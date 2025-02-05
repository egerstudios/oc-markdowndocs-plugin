<?php namespace EgerStudios\MarkdownDocs\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use EgerStudios\MarkdownDocs\Models\Settings;
use Parsedown;
use File;
use Storage;
use Yaml;
use Log;


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


    private $docsPath;
    private $settings;
    

    public function __construct()
    {
        parent::__construct();

        $this->settings = Settings::instance();
        $this->docsPath = $this->settings->storage_path ? Storage::path($this->settings->storage_path) : plugins_path('egerstudios/markdowndocs/docs/');
        
        $this->addCss('/plugins/egerstudios/markdowndocs/assets/css/docs.css');
        BackendMenu::setContext('EgerStudios.MarkdownDocs', 'markdowndocs', 'docs');
        
    }

    public function index()
    {
        $this->pageTitle = $this->settings->title ? $this->settings->title : 'Documentation';

        // Get list of Markdown files with metadata
        $files = $this->getMarkdownFilesWithMeta();
        $this->vars['files'] = $files;

        // Load the default file (first one) or the selected file
        $selectedFile = input('file', array_key_first($files));
        
        $parsed = $this->parseMarkdownFile($selectedFile);
        
        $this->vars['content'] = $parsed['content'];
        $this->vars['meta'] = $parsed['meta'];
        $this->vars['selectedFile'] = $selectedFile;
        $this->pageTitle = $this->vars['meta']['title'] . ' - ' . $this->pageTitle;
        $this->bodyClass = 'compact-container';

    }

    


    private function parseMarkdownFile($filename)
    {
        $filePath = $this->docsPath . $filename;
        if (!File::exists($filePath)) {
            return ['content' => '<p><strong>File not found.</strong></p>', 'meta' => []];
        }

        $content = File::get($filePath);
        
        // Parse meta data and content
        $parsed = $this->parseFileContent($content);
        
        $parsedown = new Parsedown();
        return [
            'content' => $parsedown->text($parsed['content']),
            'meta' => $parsed['meta']
        ];
    }

    private function parseFileContent($content) {
        $pattern = '/^---[\r\n|\r|\n](.*?)[\r\n|\r|\n]---[\r\n|\r|\n](.*)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            try {
                $yamlString = trim($matches[1]);
                $meta = Yaml::parse($yamlString);
                $content = trim($matches[2]);
            } catch (\Exception $e) {
                Log::warning('YAML parsing failed: ' . $e->getMessage());
                $meta = [];
                $content = $content;
            }
        } else {
            $meta = [];
            $content = trim($content);
        }

        return [
            'meta' => $meta ?? [],
            'content' => $content
        ];
    }

    private function getMarkdownFilesWithMeta()
    {
        $files = [];
        foreach (File::files($this->docsPath) as $path) {
            $filename = basename($path);
            $parsed = $this->parseMarkdownFile($filename);
            $files[$filename] = [
                'meta' => $parsed['meta'],
                'content' => $parsed['content']
            ];
        }

        // Sort files based on position from meta data
        uasort($files, function ($a, $b) {
            $posA = isset($a['meta']['position']) ? (int)$a['meta']['position'] : PHP_INT_MAX;
            $posB = isset($b['meta']['position']) ? (int)$b['meta']['position'] : PHP_INT_MAX;
            return $posA <=> $posB;
        });

        return $files;
    }

    private function getMarkdownFiles()
    {
        return array_map('basename', File::files($this->docsPath));
    }

    public function onSelectFile()
    {
        $filename = post('file');
        $parsed = $this->parseMarkdownFile($filename);
        
        return [
            '#layout-body' => $this->makePartial('content', [
                'content' => $parsed['content']
            ])
        ];
    }


    

    
}
