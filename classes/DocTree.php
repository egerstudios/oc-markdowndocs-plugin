<?php namespace EgerStudios\MarkdownDocs\Classes;

use BackendAuth;
use Cache;
use File;
use Log;
use Yaml;

/**
 * DocTree builds the navigation tree for a docs folder.
 *
 * Hierarchy comes from (in priority order):
 *  1. `parent: other/file.md` in the front matter
 *  2. the folder structure: `guide/index.md` is the parent of `guide/*.md`;
 *     a folder without an index file becomes a non-clickable group.
 *
 * Access is controlled per file with `permissions:` in the front matter
 * (a single code or a list; the user must hold all of them). A hidden
 * parent hides its whole subtree. `hidden: true` removes a page from the
 * sidebar but keeps it reachable by direct link.
 */
class DocTree
{
    protected $root;

    /** @var array<string,array> flat nodes keyed by id (relative path) */
    protected $nodes = [];

    public function __construct(string $root)
    {
        $this->root = rtrim($root, '/\\');
    }

    /**
     * Builds the tree, already filtered by what the current user may see.
     */
    public function tree(): array
    {
        $this->nodes = $this->scan();
        return $this->sort($this->filter($this->link()));
    }

    /**
     * Resolves an id to an absolute path, or null when it is outside the
     * docs folder, not markdown, or missing. Blocks path traversal.
     */
    public function resolve(?string $id, array $exts = ['md', 'markdown']): ?string
    {
        if (!$id || !preg_match('/\.(' . implode('|', $exts) . ')$/i', $id)) {
            return null;
        }
        $base = realpath($this->root);
        $path = realpath($this->root . '/' . ltrim($id, '/\\'));
        if (!$base || !$path || !is_file($path)) {
            return null;
        }
        return strpos($path, $base . DIRECTORY_SEPARATOR) === 0 ? $path : null;
    }

    /**
     * Splits a file into front matter and markdown body.
     */
    public static function parse(string $raw): array
    {
        if (preg_match('/^---\R(.*?)\R---\R?(.*)/s', $raw, $m)) {
            try {
                $meta = Yaml::parse(trim($m[1]));
                return ['meta' => is_array($meta) ? $meta : [], 'content' => trim($m[2])];
            } catch (\Exception $e) {
                Log::warning('MarkdownDocs: YAML parsing failed: ' . $e->getMessage());
            }
        }
        return ['meta' => [], 'content' => trim($raw)];
    }

    public static function canAccess(array $meta): bool
    {
        $perms = (array) ($meta['permissions'] ?? $meta['permission'] ?? []);
        if (!$perms) {
            return true;
        }
        $user = BackendAuth::getUser();
        return $user && $user->hasAccess($perms, true);
    }

    /**
     * First visible, linkable node (depth first), for the default page.
     */
    public static function firstLink(array $tree): ?string
    {
        foreach ($tree as $node) {
            if ($node['link']) {
                return $node['id'];
            }
            if ($found = static::firstLink($node['children'])) {
                return $found;
            }
        }
        return null;
    }

    /**
     * Ids of every ancestor of $id, so the sidebar can expand them.
     */
    public static function ancestors(array $tree, string $id, array $trail = []): ?array
    {
        foreach ($tree as $node) {
            if ($node['id'] === $id) {
                return $trail;
            }
            if (($r = static::ancestors($node['children'], $id, array_merge($trail, [$node['id']]))) !== null) {
                return $r;
            }
        }
        return null;
    }

    /**
     * Reads every page's front matter and searchable text. The result is
     * cached against the folder's file list, sizes and mtimes, so edits
     * invalidate it automatically.
     */
    protected function scan(): array
    {
        if (!is_dir($this->root)) {
            return [];
        }

        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($this->root, \FilesystemIterator::SKIP_DOTS)
        );
        $prefix = strlen($this->root) + 1;
        $files = [];
        foreach ($it as $file) {
            if ($file->isFile() && preg_match('/\.(md|markdown)$/i', $file->getFilename())) {
                $files[str_replace('\\', '/', substr($file->getPathname(), $prefix))] = $file;
            }
        }

        $sig = md5(json_encode(array_map(function ($f) {
            return [$f->getPathname(), $f->getMTime(), $f->getSize()];
        }, $files)));

        return Cache::remember('egerstudios.markdowndocs.scan.' . $sig, 86400, function () use ($files) {
            $nodes = [];
            foreach ($files as $id => $file) {
                $parsed = static::parse(File::get($file->getPathname()));
                $meta = $parsed['meta'];
                $nodes[$id] = [
                    'id' => $id,
                    'meta' => $meta,
                    'title' => $meta['title'] ?? pathinfo($id, PATHINFO_FILENAME),
                    'icon' => $meta['icon'] ?? null,
                    'position' => isset($meta['position']) ? (int) $meta['position'] : PHP_INT_MAX,
                    'hidden' => !empty($meta['hidden']),
                    'link' => true,
                    'children' => [],
                    'parent' => $this->parentId($id, $meta),
                    'text' => trim(preg_replace('/\s+/', ' ', preg_replace('/[#*`>_\[\]|]/', ' ', $parsed['content']))),
                ];
            }
            return $nodes;
        });
    }

    /**
     * Full-text search over pages the current user can see.
     * All words must match (title or body); title hits rank higher.
     *
     * @return array<int,array{id:string,title:string,icon:?string,snippet:string}>
     */
    public function search(string $query, int $limit = 20): array
    {
        $terms = array_values(array_filter(preg_split('/\s+/u', mb_strtolower(trim($query)))));
        if (!$terms) {
            return [];
        }

        $this->nodes = $this->scan();
        $visible = [];
        $this->collectIds($this->filter($this->link()), $visible);

        $hits = [];
        foreach ($this->nodes as $id => $node) {
            if (!isset($visible[$id]) || !$node['link']) {
                continue;
            }
            $title = mb_strtolower($node['title']);
            $text = mb_strtolower($node['text']);
            $score = 0;
            foreach ($terms as $t) {
                $inTitle = mb_strpos($title, $t) !== false;
                $count = mb_substr_count($text, $t);
                if (!$inTitle && !$count) {
                    continue 2;
                }
                $score += ($inTitle ? 10 : 0) + min($count, 5);
            }
            $hits[] = [
                'id' => $id, 'title' => $node['title'], 'icon' => $node['icon'],
                'snippet' => $this->snippet($node['text'], $terms), 'score' => $score,
            ];
        }

        usort($hits, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        return array_slice($hits, 0, $limit);
    }

    protected function collectIds(array $nodes, array &$ids): void
    {
        foreach ($nodes as $node) {
            $ids[$node['id']] = true;
            $this->collectIds($node['children'], $ids);
        }
    }

    /**
     * Escaped excerpt around the first match, with matches in <mark>.
     */
    protected function snippet(string $text, array $terms): string
    {
        $pos = null;
        foreach ($terms as $t) {
            $p = mb_stripos($text, $t);
            if ($p !== false && ($pos === null || $p < $pos)) {
                $pos = $p;
            }
        }
        $start = max(0, ($pos ?? 0) - 50);
        $raw = ($start > 0 ? '…' : '') . mb_substr($text, $start, 140) . (mb_strlen($text) > $start + 140 ? '…' : '');
        $pattern = '/(' . implode('|', array_map(function ($t) {
            return preg_quote($t, '/');
        }, $terms)) . ')/iu';
        $out = '';
        foreach (preg_split($pattern, $raw, -1, PREG_SPLIT_DELIM_CAPTURE) as $i => $piece) {
            $out .= $i % 2 ? '<mark>' . e($piece) . '</mark>' : e($piece);
        }
        return $out;
    }

    protected function parentId(string $id, array $meta): ?string
    {
        if (!empty($meta['parent'])) {
            return ltrim(str_replace('\\', '/', $meta['parent']), '/');
        }
        $dir = dirname($id);
        if ($dir === '.') {
            return null;
        }
        // An index file sits beside its folder, not inside it, so it
        // doesn't become its own parent.
        if (strtolower(pathinfo($id, PATHINFO_FILENAME)) === 'index') {
            return $this->dirParent(dirname($dir));
        }
        return $this->dirParent($dir);
    }

    /**
     * Node id representing a folder: its index.md, else a virtual group.
     */
    protected function dirParent(string $dir): ?string
    {
        if ($dir === '.' || $dir === '') {
            return null;
        }
        return is_file($this->root . '/' . $dir . '/index.md') ? $dir . '/index.md' : 'dir:' . $dir;
    }

    /**
     * Wires parent/child links, creating virtual group nodes for folders.
     */
    protected function link(): array
    {
        $roots = [];
        foreach ($this->nodes as $id => &$node) {
            $parent = $node['parent'];
            while ($parent && strpos($parent, 'dir:') === 0 && !isset($this->nodes[$parent])) {
                $dir = substr($parent, 4);
                $up = $this->dirParent(dirname($dir));
                $this->nodes[$parent] = [
                    'id' => $parent, 'meta' => [], 'title' => ucfirst(str_replace(['-', '_'], ' ', basename($dir))),
                    'icon' => 'icon-folder', 'position' => PHP_INT_MAX, 'hidden' => false,
                    'link' => false, 'children' => [], 'parent' => $up,
                ];
                $parent = $up;
            }
        }
        unset($node);

        // Break cycles / dangling parents so nothing silently vanishes.
        foreach ($this->nodes as $id => $node) {
            $seen = [$id];
            $p = $node['parent'];
            while ($p && isset($this->nodes[$p]) && !in_array($p, $seen, true)) {
                $seen[] = $p;
                $p = $this->nodes[$p]['parent'];
            }
            if ($p && (!isset($this->nodes[$p]) || in_array($p, $seen, true))) {
                $this->nodes[$id]['parent'] = null;
            }
        }

        foreach ($this->nodes as $id => &$node) {
            if ($node['parent']) {
                $this->nodes[$node['parent']]['children'][] = &$node;
            } else {
                $roots[] = &$node;
            }
        }
        unset($node);
        return $roots;
    }

    protected function filter(array $nodes): array
    {
        $out = [];
        foreach ($nodes as $node) {
            if ($node['link'] && !static::canAccess($node['meta'])) {
                continue;
            }
            $node['children'] = $this->filter($node['children']);
            if (!$node['link'] && !$node['children']) {
                continue; // empty group
            }
            $out[] = $node;
        }
        return $out;
    }

    protected function sort(array $nodes): array
    {
        usort($nodes, function ($a, $b) {
            return [$a['position'], strtolower($a['title'])] <=> [$b['position'], strtolower($b['title'])];
        });
        foreach ($nodes as &$node) {
            $node['children'] = $this->sort($node['children']);
        }
        return $nodes;
    }
}
