<?php namespace Diveramkt\Getinstagram;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    public function pluginDetails()
    {
        return [
            'name' => 'Getinstagram',
            'description' => 'Provides feed from a Instagram #hashtag',
            'author' => 'John Svensson',
            'icon' => 'icon-instagram'
        ];
    }

    public function registerComponents()
    {
        return [
            '\Diveramkt\Getinstagram\Components\Instagramfeed' => 'instagramFeed'
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Getinstagram',
                'icon'        => 'icon-instagram',
                'description' => '',
                'class'       => '\Diveramkt\Getinstagram\Models\Settings',
                'order'       => 250
            ]
        ];
    }
}