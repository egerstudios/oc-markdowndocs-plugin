<?php namespace EgerStudios\MarkdownDocs\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use EgerStudios\MarkdownDocs\Classes\DocTree;
use EgerStudios\MarkdownDocs\Models\Settings;
use ApplicationException;
use Backend;
use BackendAuth;
use Cache;
use EgerStudios\MarkdownDocs\Classes\Renderer;
use Event;
use File;
use Flash;
use Storage;

/**
 * Docs Backend Controller
 *
 * @link https://docs.octobercms.com/3.x/extend/system/controllers.html
 */
class Docs extends Controller
{
    public $implement = [];

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['egerstudios.markdowndocs.docs'];

    /**
     * @var string Path to the documentation files
     */
    private $docsPath;

    /**
     * @var Settings Instance of the Settings model
     */
    private $settings;

    public function __construct()
    {
        parent::__construct();

        $this->settings = Settings::instance();

        // usage: in your plugin boot method
        // Event::listen("markdowndocs.docspath", fn() => plugins_path('acme/blog/docs/'));
        $externalDocsPath = Event::fire('markdowndocs.docspath', []);

        if ($externalDocsPath && is_array($externalDocsPath) && !empty($externalDocsPath[0])) {
            $this->docsPath = $externalDocsPath[0];
        } else {
            $this->docsPath = $this->settings->storage_path
                ? Storage::path($this->settings->storage_path)
                : plugins_path('egerstudios/markdowndocs/docs/');
        }

        $this->addCss('/plugins/egerstudios/markdowndocs/assets/css/docs.css');
        $this->addCss('/plugins/egerstudios/markdowndocs/assets/css/docs-nav.css');
        BackendMenu::setContext('EgerStudios.MarkdownDocs', 'markdowndocs', 'docs');
    }

    /**
     * Displays the sidebar tree and the selected page.
     */
    public function index()
    {
        $title = $this->settings->title ?: __('egerstudios.markdowndocs::lang.default_title');

        $docs = new DocTree($this->docsPath);
        $tree = $docs->tree();

        $selected = input('file') ?: DocTree::firstLink($tree);
        $page = $this->loadPage($docs, $selected);

        $this->vars['tree'] = $tree;
        $this->vars['selectedFile'] = $selected;
        $this->vars['openIds'] = $selected ? (DocTree::ancestors($tree, $selected) ?: []) : [];
        $this->vars['content'] = $page['content'];
        $this->vars['toc'] = $page['toc'];
        $this->vars['meta'] = $page['meta'];
        $this->vars['canEdit'] = $page['found'] && $this->canEdit($page['meta']);
        $this->vars['editing'] = $this->vars['canEdit'] && input('edit');
        $this->vars['source'] = $this->vars['editing'] ? File::get($docs->resolve($selected)) : '';
        $this->pageTitle = ($page['meta']['title'] ?? null) ? $page['meta']['title'] . ' - ' . $title : $title;
        $this->bodyClass = 'compact-container';
    }

    /**
     * Reads, permission-checks and renders one page (HTML is cached per
     * file version).
     */
    private function loadPage(DocTree $docs, ?string $id): array
    {
        $path = $docs->resolve($id);
        if (!$path) {
            return $this->notice(__('egerstudios.markdowndocs::lang.page.not_found'));
        }

        $parsed = DocTree::parse(File::get($path));

        // Direct links must respect the same permissions as the sidebar.
        if (!DocTree::canAccess($parsed['meta'])) {
            return $this->notice(__('egerstudios.markdowndocs::lang.page.no_access'));
        }

        $key = 'egerstudios.markdowndocs.page.' . md5(Renderer::VERSION . $id . $path . filemtime($path) . filesize($path));
        $rendered = Cache::remember($key, 86400, function () use ($id, $parsed) {
            return (new Renderer($id))->render($parsed['content']);
        });

        return ['content' => $rendered['html'], 'toc' => $rendered['toc'], 'meta' => $parsed['meta'], 'found' => true];
    }

    private function notice(string $message): array
    {
        return ['content' => '<p><strong>' . e($message) . '</strong></p>', 'toc' => [], 'meta' => [], 'found' => false];
    }

    /**
     * Editing needs the setting switched on, the edit permission, and
     * access to the page itself.
     */
    private function canEdit(array $meta): bool
    {
        $user = BackendAuth::getUser();
        return $this->settings->allow_editing
            && $user && $user->hasAccess('egerstudios.markdowndocs.edit')
            && DocTree::canAccess($meta);
    }

    /**
     * AJAX: live search in the sidebar.
     */
    public function onSearch()
    {
        $docs = new DocTree($this->docsPath);
        $query = trim((string) post('q'));

        return [
            '#docs-search-results' => $this->makePartial('search_results', [
                'query' => $query,
                'results' => mb_strlen($query) >= 2 ? $docs->search($query) : [],
            ]),
        ];
    }

    /**
     * AJAX: save an edited page, then return to the rendered view.
     */
    public function onSave()
    {
        $docs = new DocTree($this->docsPath);
        $id = post('file');
        $path = $docs->resolve($id);
        if (!$path) {
            throw new ApplicationException(__('egerstudios.markdowndocs::lang.page.not_found'));
        }

        // Authorize against the page as it exists on disk, not the new text.
        if (!$this->canEdit(DocTree::parse(File::get($path))['meta'])) {
            throw new ApplicationException(__('egerstudios.markdowndocs::lang.page.edit_forbidden'));
        }

        $source = str_replace("\r\n", "\n", (string) post('source'));
        if (strlen($source) > 1024 * 1024) {
            throw new ApplicationException(__('egerstudios.markdowndocs::lang.page.too_large'));
        }
        if (preg_match('/^---\R(.*?)\R---/s', $source, $m)) {
            try {
                \Yaml::parse(trim($m[1]));
            } catch (\Exception $e) {
                throw new ApplicationException(__('egerstudios.markdowndocs::lang.page.invalid_front_matter', ['message' => $e->getMessage()]));
            }
        }

        File::put($path, $source);
        Flash::success(__('egerstudios.markdowndocs::lang.page.saved'));

        return Backend::redirect('egerstudios/markdowndocs/docs?file=' . rawurlencode($id));
    }

    /**
     * Serves images referenced from markdown files (inside the docs folder only).
     */
    public function asset()
    {
        $path = (new DocTree($this->docsPath))->resolve(input('file'), Renderer::IMAGE_EXTS);
        if (!$path) {
            abort(404);
        }

        return response()->file($path, [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; style-src 'unsafe-inline'; sandbox",
        ]);
    }
}
