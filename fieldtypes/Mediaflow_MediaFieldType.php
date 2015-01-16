<?php
namespace Craft;

class Mediaflow_MediaFieldType extends BaseFieldType
{
    public function getName()
    {
        return Craft::t('Mediaflow item');
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
            'value' => $value ? $value->getAttributes() : $emptyDefaults,
            'emptyDefaults' => $emptyDefaults
        ));
    }

    public function prepValue($value) {
        if (!$value) {
            return null;
        }
        $data = array();
        $copy = array(
            'name' => 'name',
            'host' => 'host',
            'isImage' => 'isImage',
            'uploaded' => 'uploaded',
            'shareUrl' => 'shareUrl',
            'thumbnailUrl' => 'thumb',
            'thumb' => 'thumb',
            '_id' => 'id',
            'id' => 'id'
        );
        foreach ($copy as $now => $key) {
            if (isset($value[$now])) {
                $data[$key] = $value[$now];
            }
        }
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

    public function defineContentAttribute()
    {
        return AttributeType::Mixed;
    }
}
