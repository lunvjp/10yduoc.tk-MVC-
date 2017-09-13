<?php
class bootstrap {
    private $url;
    private $controller;

    public function __construct() {
        $this->setURL();

        if (!isset($this->url['controller'])) {
            $this->loadDefaultController();
            exit();
        }
        $this->loadExistsController();
        $this->callControllerMethod();
    }

    public function loadDefaultController() {
        require_once controller_path . 'index.php';
        $this->controller = new index();
        $this->controller->index();
    }

    public function loadExistsController() {
        $file = controller_path . $this->url['controller'] . '.php';
        if (file_exists($file)) {
            require_once $file;
            $this->controller = new $this->url['controller']();
        } else {
            $this->error();
        }
    }

    public function callControllerMethod() {
        $url = isset($this->url['action']) ? $this->url['action'] : 'index';
        if (method_exists($this->controller,$url)) {
            if ($url == 'loadListTest' || $url == 'doMoreQuestion') {
                $subject_id = isset($_GET['subject_id']) ? $_GET['subject_id'] : 1;
                $this->controller->$url($subject_id);
            } else $this->controller->$url();
        } else {
            $this->error();
        }
    }

    public function error() {
        require_once controller_path . 'error.php';
        $error = new error();
        $error->index();
    }

    public function setURL() {
        $this->url = isset($_GET) ? $_GET : null;
    }
}