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
            'name' => 'egerstudios.markdowndocs::lang.plugin.name',
            'description' => 'egerstudios.markdowndocs::lang.plugin.description',
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
        return [
            'egerstudios.markdowndocs.docs' => [
                'tab' => 'egerstudios.markdowndocs::lang.permissions.tab',
                'label' => 'egerstudios.markdowndocs::lang.permissions.docs'
            ],
            'egerstudios.markdowndocs.edit' => [
                'tab' => 'egerstudios.markdowndocs::lang.permissions.tab',
                'label' => 'egerstudios.markdowndocs::lang.permissions.edit'
            ],
            'egerstudios.markdowndocs.settings' => [
                'tab' => 'egerstudios.markdowndocs::lang.permissions.tab',
                'label' => 'egerstudios.markdowndocs::lang.permissions.settings'
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
                'label' => 'egerstudios.markdowndocs::lang.nav.docs',
                'url' => Backend::url('egerstudios/markdowndocs/docs'),
                'icon' => 'icon-book',
                'permissions' => ['egerstudios.markdowndocs.docs'],
                'order' => 500,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'egerstudios.markdowndocs::lang.settings.label',
                'description' => 'egerstudios.markdowndocs::lang.settings.description',
                'category' => 'egerstudios.markdowndocs::lang.settings.category',
                'icon' => 'icon-cog',
                'class' => \EgerStudios\MarkdownDocs\Models\Settings::class,
                'permissions' => ['egerstudios.markdowndocs.settings'],
            ]
        ];
    }

}
