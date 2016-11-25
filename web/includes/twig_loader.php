<?php

require_once 'vendor/autoload.php';

Twig_Autoloader::register();

$loader = new Twig_Loader_Filesystem('templates');
$twig = new Twig_Environment($loader, array(
    'cache' => 'compilation_cache',
    'auto_reload' => true // TODO this is for dev only
));

?>
