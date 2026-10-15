<?php namespace EgerStudios\MarkdownDocs\Classes;

use Backend;
use Str;

/**
 * Renders a page's markdown to safe HTML and post-processes it:
 * heading anchors + table of contents, relative link/image rewriting,
 * and server-side code highlighting (when scrivo/highlight.php is installed).
 */
class Renderer
{
    /** Bump to invalidate cached HTML after changing this class. */
    const VERSION = 1;

    const IMAGE_EXTS = ['png', 'jpg', 'jpeg', 'gif', 'webp', 'svg'];

    protected $pageId;
    protected $toc = [];

    public function __construct(string $pageId)
    {
        $this->pageId = $pageId;
    }

    /**
     * @return array{html:string,toc:array}
     */
    public function render(string $markdown): array
    {
        $parser = class_exists(\ParsedownExtra::class) ? new \ParsedownExtra() : new \Parsedown();
        $parser->setSafeMode(true); // strips raw HTML/JS, unsafe URL schemes

        $html = $parser->text($markdown);
        $html = $this->headings($html);
        $html = $this->links($html);
        $html = $this->highlight($html);

        return ['html' => $html, 'toc' => $this->toc];
    }

    protected function headings(string $html): string
    {
        $used = [];
        return preg_replace_callback('~<h([2-4])(\s[^>]*)?>(.*?)</h\1>~s', function ($m) use (&$used) {
            $text = trim(html_entity_decode(strip_tags($m[3]), ENT_QUOTES));
            $base = Str::slug($text) ?: 'section';
            $id = $base;
            for ($n = 2; isset($used[$id]); $n++) {
                $id = $base . '-' . $n;
            }
            $used[$id] = true;
            $this->toc[] = ['level' => (int) $m[1], 'id' => $id, 'text' => $text];
            return '<h' . $m[1] . ' id="' . e($id) . '">' . $m[3] . '</h' . $m[1] . '>';
        }, $html);
    }

    protected function links(string $html): string
    {
        return preg_replace_callback('~(<(?:a|img)\s[^>]*?\b(?:href|src)=")([^"]*)(")~', function ($m) {
            $url = $this->rewrite(html_entity_decode($m[2], ENT_QUOTES));
            return $m[1] . e($url) . $m[3];
        }, $html);
    }

    /**
     * Turns page-relative links to .md files and images into backend URLs.
     */
    protected function rewrite(string $url): string
    {
        if ($url === '' || $url[0] === '#' || $url[0] === '/' || preg_match('/^[a-z][a-z0-9+.\-]*:/i', $url)) {
            return $url;
        }

        $fragment = '';
        if (($pos = strpos($url, '#')) !== false) {
            $fragment = substr($url, $pos);
            $url = substr($url, 0, $pos);
        }

        $target = $this->normalize(dirname($this->pageId) . '/' . $url);
        if ($target === null) {
            return '#';
        }

        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));
        $base = Backend::url('egerstudios/markdowndocs/docs');
        if (in_array($ext, ['md', 'markdown'], true)) {
            return $base . '?file=' . rawurlencode($target) . $fragment;
        }
        if (in_array($ext, self::IMAGE_EXTS, true)) {
            return $base . '/asset?file=' . rawurlencode($target);
        }
        return '#';
    }

    /**
     * Collapses ./ and ../ segments; null if it climbs out of the docs root.
     */
    protected function normalize(string $path): ?string
    {
        $out = [];
        foreach (explode('/', str_replace('\\', '/', $path)) as $seg) {
            if ($seg === '' || $seg === '.') {
                continue;
            }
            if ($seg === '..') {
                if (!$out) {
                    return null;
                }
                array_pop($out);
                continue;
            }
            $out[] = $seg;
        }
        return $out ? implode('/', $out) : null;
    }

    protected function highlight(string $html): string
    {
        if (!class_exists(\Highlight\Highlighter::class)) {
            return $html;
        }
        $highlighter = new \Highlight\Highlighter();

        return preg_replace_callback('~<pre><code class="language-([\w+#\-]+)">(.*?)</code></pre>~s', function ($m) use ($highlighter) {
            try {
                $result = $highlighter->highlight($m[1], html_entity_decode($m[2], ENT_QUOTES));
                return '<pre><code class="hljs language-' . e($m[1]) . '">' . $result->value . '</code></pre>';
            } catch (\Throwable $e) {
                return $m[0]; // unknown language: leave plain
            }
        }, $html);
    }
}
