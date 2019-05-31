<?php

require_once 'vendor/autoload.php';
require_once 'libs/Grabber.php';
require_once 'src/Pampik.php';

//use Exception;
use src\Pampik;

try {
    $_SERVER['DOCUMENT_ROOT'] = dirname(__FILE__);
    $pampik = new Pampik();
    $pampik->setCatagories(['https://pampik.com/category/dla-malcikov']);
    $result = $pampik->purse();


} catch (Exception $e) {
    echo '<pre>';
    print_r($e);
}




