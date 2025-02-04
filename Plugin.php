<?php namespace EgerStudios\MarkdownDocs;

use Backend;
use System\Classes\PluginBase;

/**
 * Plugin Information File
 *
 * @link https://docs.octobercms.com/3.x/extend/system/plugins.html
 */
class Plugin extends PluginBase
{
    /**
     * pluginDetails about this plugin.
     */
    public function pluginDetails()
    {
        return [
            'name' => 'MarkdownDocs',
            'description' => 'No description provided yet...',
            'author' => 'EgerStudios',
            'icon' => 'icon-leaf'
        ];
    }

    /**
     * register method, called when the plugin is first registered.
     */
    public function register()
    {
        //
    }

    /**
     * boot method, called right before the request route.
     */
    public function boot()
    {
        //
    }

    /**
     * registerComponents used by the frontend.
     */
    public function registerComponents()
    {
        return []; // Remove this line to activate

        return [
            'EgerStudios\MarkdownDocs\Components\MyComponent' => 'myComponent',
        ];
    }

    /**
     * registerPermissions used by the backend.
     */
    public function registerPermissions()
    {
        return []; // Remove this line to activate

        return [
            'egerstudios.markdowndocs.some_permission' => [
                'tab' => 'MarkdownDocs',
                'label' => 'Some permission'
            ],
        ];
    }

    /**
     * registerNavigation used by the backend.
     */
    public function registerNavigation()
    {

        return [
            'markdowndocs' => [
                'label' => 'Docs',
                'url' => Backend::url('egerstudios/markdowndocs/docs'),
                'icon' => 'icon-book',
                'permissions' => ['egerstudios.markdowndocs.*'],
                'order' => 500,
            ],
        ];
    }
}
