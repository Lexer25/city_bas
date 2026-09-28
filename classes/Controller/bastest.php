<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Bastest extends Controller {

    public function action_index()
    {
        $model = new Model_Basoop();
        $model->init('10.200.15.4');

        $result = array(
            'account_type' => $model->account_type,
            'device_model' => $model->device_model,
            'firmware'     => $model->firmware_version,
            'api_version'  => $model->api_version,
        );

        $this->response->headers('Content-Type', 'application/json');
        $this->response->body(json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}