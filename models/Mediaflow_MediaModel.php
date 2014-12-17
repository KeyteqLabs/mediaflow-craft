<?php
namespace Craft;

class Mediaflow_MediaModel extends BaseModel
{
	/**
	 * @access protected
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'id'    => AttributeType::String,
			'name'    => AttributeType::String,
			'host'    => AttributeType::String,
			'isImage'    => AttributeType::Bool,
			'thumb'    => AttributeType::String,
			'uploaded'    => AttributeType::Number,
			'file' => AttributeType::Mixed,
		));
	}

    public function url(array $options = array()) {
        $options += array(
            'width' => false,
            'height' => false,
            'quality' => false,
            'crop' => true,
            'ending' => $this->file['ending'],
            'host' => $this->host
        );
        if (!$options['width'] && !$options['height']) {
            throw new \Exception("width or height must be specified for media.url()");
        }

        $width = $options['width'];
        $height = $options['height'];
        $ratio = $this->file['ratio'];
        $quality = $options['quality'];
        $ending = $options['ending'];

        $height = $height ?: floor($width * $ratio);
        $width = $width ?: floor($height * $ratio);

        $size = "{$width}x{$height}";
        if ($quality) $size .= "q{$quality}";

        $url = '//' . $this->host . "/{$size}/{$this->id}{$ending}";
        if (!$options['crop']) {
            $url .= '?original=1';
        }
        return $url;
    }
}
