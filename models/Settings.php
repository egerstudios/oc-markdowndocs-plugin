<?php namespace EgerStudios\MarkdownDocs\Models;

use Model;
use System\Models\SettingModel;

/**
 * Settings Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class Settings extends SettingModel
{
    

    public $settingsCode = 'egerstudios_markdowndocs_settings';

    public $settingsFields = 'fields.yaml';
}
