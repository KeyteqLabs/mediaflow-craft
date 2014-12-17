<?php

namespace Craft;

use Keyteq\Keymedia\KeymediaClient;
class Mediaflow_SettingsController extends BaseController {

    private $previewWidth = 150;
    private $previewHeight = 150;
    private $settings;

    public function __construct() {
        $craft = craft();
        $craft->log->removeRoute('WebLogRoute');
        $craft->log->removeRoute('ProfileLogRoute');
        $this->settings = $craft->plugins->getPlugin('mediaflow')->getSettings();
    }

    public function actionTestConnection() {
        try {
            $client = $this->_client($this->settings);
        } catch (\Exception $e) {
            return $this->returnJson(false);
        }

        $this->returnJson($client->isConnected());
    }
    public function actionListMedia() {
        $client = $this->_client($this->settings);
        $request = craft()->request;
        $search = $request->getParam('q', false);
        $results = $client->listMedia(false, $search);
        $out = array();
        foreach($results as $k => $item) {
            $out[$k] = $this->formatItem($item);
        }
        $this->returnJson($out);
    }

    public function actionUpload() {
        $client = $this->_client($this->settings);
        $file = $_FILES['file'];
        $filepath = $file['tmp_name'];
        $filename = $file['name'];
        $item = $client->postMedia($filepath, $filename, array(), array(
            'mime' => $file['type']
        ));

        $this->returnJson($this->formatItem($item));
    }

    protected function formatItem($item) {
        $result = $item->toArray();
        $result['id'] = $item->getId();
        $result['thumb'] = $item->getThumbnailUrl(
            $this->previewWidth,
            $this->previewHeight
        );
        $result['thumb'] = preg_replace('/^http(s?):/i', '', $result['thumb']);
        $result['isImage'] = $item->isImage();
        $result['uploaded'] = $item->getCreated();
        return $result;
    }

    protected function _client($settings) {
        return new KeymediaClient(
            $this->settings->username,
            $this->settings->url,
            $this->settings->apiKey
        );
    }
}
