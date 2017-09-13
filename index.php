<?php
require_once 'define.php';

function __autoload($filename) {
    require_once lib_path . "{$filename}.php";
}

$bootstrap = new bootstrap();