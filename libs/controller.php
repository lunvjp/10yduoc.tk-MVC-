<?php
class controller {
    public $view;
    public $model;
    
    public function __construct() {
        $this->view = new view();
    }

    protected function loadModel($name) {
        require_once model_path . $name . '.php';
        $this->model = new $name();
    }
}