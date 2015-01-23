<?php
namespace Craft;

$autoloaderPath = __DIR__ . '/vendor/autoload.php';
file_exists($autoloaderPath) && require $autoloaderPath;

class MediaflowPlugin extends BasePlugin {

    public function init()
    {
        craft()->on('entries.onBeforeSaveEntry', function($event)
        {
            $entry = $event->params['entry'];
            if (isset($entry->image)) {
                $image = $entry->image;
                $urls = $image->urls ?: array();
                foreach ($image->version as $name => $version) {
                    $hash = null;
                    if (isset($urls[$name]) && isset($urls[$name]['hash'])) {
                        $hash = $urls[$name]['hash'];
                    }
                    $urls[$name] = $image->saveVersion($name, $entry->slug, $hash);
                }
                $image->urls = $urls;
                $entry->getContent()->setAttributes(compact('image'));
            }
        });
    }

    public function getName()
    {
        return Craft::t('Mediaflow');
    }

    public function getVersion()
    {
        return '1.0.0-rc1';
    }

    public function getDeveloper()
    {
        return 'Keyteq Labs';
    }

    public function getDeveloperUrl()
    {
        return 'http://keyteq.no';
    }


    protected function defineSettings()
    {
        $string = AttributeType::String;
        return array(
            'url' => array($string, 'required' => true, 'label' => 'URL', 'default' => Craft::t('Mediaflow URL')),
            'username' => array($string, 'required' => true, 'label' => 'Username', 'default' => Craft::t('Username')),
            'apiKey' => array($string, 'required' => true, 'label' => 'API Key', 'default' => Craft::t('API key'))
        );
    }

    public function getSettingsHtml()
    {
        return craft()->templates->render('mediaflow/settings', array(
            'settings' => $this->getSettings()
        ));
    }


    public function prepSettings($settings)
    {
        return $settings;
    }

    public function hasCpSection()
    {
        return true;
    }

    public function registerCpRoutes()
    {
        return array(
            'mediaflow/check' => array('action' => 'mediaflow/settings/testConnection'),
            'mediaflow/media' => array('action' => 'mediaflow/settings/listMedia'),
            'mediaflow/upload' => array('action' => 'mediaflow/settings/upload'),
        );
}
} 
