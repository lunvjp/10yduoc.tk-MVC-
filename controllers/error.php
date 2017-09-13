<?php
class error extends controller {
    public function __construct() {
        parent::__construct();
        // Controller này không thao tác với database nên chúng ta không khởi tạo đối tượng cho thuộc tính model
    }

    public function index() {
        $this->view->render('error/index');
    }
}