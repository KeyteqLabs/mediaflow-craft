<?php
namespace Craft;

class Mediaflow_MediaFieldType extends BaseFieldType
{
    public function getName()
    {
        return Craft::t('Mediaflow item');
    }

    public function getSearchKeywords($value)
    {
        return 'mediaflow';
    }

    public function getInputHtml($name, $value)
    {
        $id = craft()->templates->namespaceInputId($name);
        $js = "angular.bootstrap(document.querySelector('#{$id}-field .mediaflow-app'), ['mediaflow']);";
        craft()->templates->includeJs($js);
        $emptyDefaults = array('id' => null);
        return craft()->templates->render('mediaflow/input', array(
            'id' => $id,
            'name'  => $name,
            'settings' => $this->getSettings(),
            'value' => $value ? $value->getAttributes() : $emptyDefaults,
            'emptyDefaults' => $emptyDefaults
        ));
    }

    public function getStaticHtml($value)
    {
        $inputHtml = $this->getInputHtml($this->model->attributes['handle'], $value);
        $inputHtml = preg_replace('/<(?:input|textarea|select|button)\s[^>]*/i', '$0 disabled', $inputHtml);

        return $inputHtml;
    }

    public function prepValue($value) {
        if (!$value) {
            return null;
        }
        $copy = array(
            'name' => 'name',
            'host' => 'host',
            'isImage' => 'isImage',
            'uploaded' => 'uploaded',
            'shareUrl' => 'shareUrl',
            'thumbnailUrl' => 'thumb',
            'thumb' => 'thumb',
            '_id' => 'id',
            'id' => 'id',
            'version' => 'version',
            'versions' => 'versions',
            'urls' => 'urls'
        );
        $data = array();
        foreach ($copy as $now => $key) {
            if (isset($value[$now])) {
                $data[$key] = $value[$now];
            }
        }
        $data['fieldtype-settings'] = $this->getSettings();
        $data['versions'] = $data['fieldtype-settings']['versions'];
        if (isset($value['file'])) {
            $file = $value['file'];
            $data['file'] = array(
                'type' => isset($file['type']) ? $file['type'] : null,
                'size' => isset($file['size']) ? $file['size'] : null,
                'width' => isset($file['width']) ? $file['width'] : null,
                'height' => isset($file['height']) ? $file['height'] : null,
                'ratio' => isset($file['ratio']) ? $file['ratio'] : null,
                'url' => isset($file['url']) ? $file['url'] : null,
                'ending' => isset($file['ending']) ? $file['ending'] : null
            );
        }
        $model = Mediaflow_MediaModel::populateModel($data);
        if (!isset($data['shareUrl'])) {
            $model->shareUrl = $model->url(array(
                'width' => 2000,
                'height' => 2000,
                'crop' => false
            ));
        }
        return $model;
    }

    public function prepValueFromPost($value)
    {
        if (is_string($value) && strlen($value) > 0) {
            $value = json_decode($value, true);
        }
        if (!is_array($value)) {
            $value = array();
        }
        return $value;
    }

    public function prepSettings($settings)
    {
        return array(
            'versions' => json_decode($settings['versions']) ?: array()
        );
    }

    public function defineSettings()
    {
        return array('versions' => AttributeType::Mixed);
    }

    /**
	 * @inheritDoc ISavableComponentType::getSettingsHtml()
	 *
	 * @return string|null
	 */
	public function getSettingsHtml()
	{
		// If they are both selected or nothing is selected, the select showBoth.
        return craft()->templates->render('mediaflow/fieldtype-settings', array(
			'settings' => $this->getSettings()
		));
	}

    public function defineContentAttribute()
    {
        return AttributeType::Mixed;
    }
}
