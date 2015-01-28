<?php
namespace Craft;
use Keyteq\Keymedia\KeymediaClient;

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
			'shareUrl'    => AttributeType::String,
			'file' => AttributeType::Mixed,
			'version' => AttributeType::Mixed,
			'urls' => AttributeType::Mixed,
		));
	}

    public function saveVersion($name, $slug, $checksum)
    {
        if (!isset($this->version[$name])) {
            return null;
        }
        $data = $this->version[$name];
        if (!is_array($data)) {
            return null;
        }
        if (!isset($data['coords'])) {
            $data = array('coords' => $data, 'width'=>100,'height'=>100);
        }
        list($x, $y, $w, $h) = $data['coords'];
        list($basename) = explode('.', $this->name);
        $hash = $this->getHash(array($x, $x+$w, $y, $y+$h));
        if ($hash === $checksum) {
            return null;
        }
        $payload = array(
            'slug' => $slug . '-' . $basename . '-' . $name,
            'width' => $data['width'],
            'height' => $data['height'],
            'coords' => array($x, $y, $x + $w, $y + $h)
        );
        $result = $this->_client()->addMediaVersion($this->id, $payload);
        $response = $result['version'];
        $response['hash'] = $hash;
        return $response;
    }

    public function getHash($coords)
    {
        return md5(implode('-', $coords));
    }

    /**
     * Example usage:
     * <img data-interchange="{{image.interchange(['default','large'])}}">
     * This only works if you have specified crops with these names, and those crops have been set
     */
    public function interchange($versions = array())
    {
        $output = array();
        foreach ($versions as $name) {
            $output[] = '[' . $this->versionUrl($name) . " ($name)]";
        }
        return implode(', ', $output);
    }

    public function versionUrl($name)
    {
        if (!isset($this->urls[$name])) {
            return null;
        }
        $data = $this->urls[$name];
        $host = craft()->plugins->getPlugin('mediaflow')->getSettings()->url;
        $path = $data['media'] . '/' . $data['slug'];
        return $host . $path . $this->file['ending'];
    }

    public function url($options = array())
    {
        if (is_string($options)) {
            return $this->versionUrl($options);
        }
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

    protected function _client() {
        $s = craft()->plugins->getPlugin('mediaflow')->getSettings();
        return new KeymediaClient($s->username, $s->url, $s->apiKey);
    }
}
