<?php
class view {
    public $content = array();
    
    public function render($name) {
        require_once "views/$name.php";
    }
}