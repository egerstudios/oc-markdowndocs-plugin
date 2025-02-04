<?php namespace EgerStudios\MarkdownDocs\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Parsedown;
use File;
use Yaml;


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
    

    public function __construct()
    {
        parent::__construct();
        
        $this->docsPath = plugins_path('egerstudios/markdowndocs/docs/');
        

        BackendMenu::setContext('EgerStudios.MarkdownDocs', 'markdowndocs', 'docs');
        
    }

    public function index()
    {
        $this->pageTitle = 'Documentation';

        // Get list of Markdown files with metadata
        $files = $this->getMarkdownFilesWithMeta();
        $this->vars['files'] = $files;

        // Load the default file (first one) or the selected file
        $selectedFile = input('file', array_key_first($files));
        
        $parsed = $this->parseMarkdownFile($selectedFile);
        
        $this->vars['content'] = $parsed['content'];
        $this->vars['meta'] = $parsed['meta'];
        $this->vars['selectedFile'] = $selectedFile;
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

    private function parseFileContent($content)
    {
        // Updated pattern to better match YAML front matter
        $pattern = '/^\s*---([\s\S]*?)---\s*(.*)$/m';
        
        if (preg_match($pattern, $content, $matches)) {
            try {
                // Parse the YAML section
                $yamlString = trim($matches[1]);
                $meta = Yaml::parse($yamlString);
                $content = trim($matches[2]);
            } catch (\Exception $e) {
                \Log::warning('YAML parsing failed: ' . $e->getMessage());
                $meta = [];
                $content = $content;
            }
        } else {
            // No front matter found, treat the whole thing as content
            $meta = [];
            $content = $content;
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
            $files[$filename] = $parsed['meta'];
        }

        // Sort files based on position if specified in meta
        uasort($files, function ($a, $b) {
            $posA = $a['position'] ?? PHP_INT_MAX;
            $posB = $b['position'] ?? PHP_INT_MAX;
            return $posA <=> $posB;
        });

        return $files;
    }

    private function getMarkdownFiles()
    {
        return array_map('basename', File::files($this->docsPath));
    }


    

    
}
