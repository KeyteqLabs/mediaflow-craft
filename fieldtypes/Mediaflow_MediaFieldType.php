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
        $id = craft()->templates->formatInputId($name);
        $class = get_class($this->element);
        $inMatrix = $class === 'Craft\Mediaflow_MediaFieldType';
        return craft()->templates->render('mediaflow/input', array(
            'inMatrix' => $inMatrix,
            'id' => $id,
            'name'  => $name,
            'value' => $value ? $value->getAttributes() : array()
        ));
    }

    public function prepValue($value) {
        if (!$value) {
            return null;
        }
        $data = array();
        $copy = array(
            'name' => 'name',
            'isImage' => 'isImage',
            'thumbnailUrl' => 'thumb',
            'thumb' => 'thumb',
            '_id' => 'id'
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
        if (isset($value['host'])) {
            $model->setHost($value['host']);
        }
        return $model;
    }

    public function prepValueFromPost($value)
    {
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
