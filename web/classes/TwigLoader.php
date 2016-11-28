<?php
require_once __DIR__ . '/../vendor/autoload.php';

class TwigLoader {

    private $twig;

    private $context = array();

    public function __construct($indev) {
        Twig_Autoloader::register();

        $loader = new Twig_Loader_Filesystem('templates');
        $this->twig = new Twig_Environment($loader, array(
            'cache' => 'compilation_cache',
            'auto_reload' => $indev, // TODO this is for dev only
            'debug' => $indev
        ));
        if ($indev) {
            $this->twig->addExtension(new Twig_Extension_Debug());
        }

        $this->addGlobalContext('branches', ['1.9/dev', '1.10/dev', '1.11/dev']); // FIXME
    }

    public function show($template, $context = []) {
        $this->twig->display($template, array_merge($this->context, $context));
    }

    public function addGlobalContext() {
        if (func_num_args() == 1 && is_array(func_get_arg(0))) {
            $this->context = array_merge($this->context, func_get_arg(0));
        } else if (func_num_args() == 2) {
            $this->context[func_get_arg(0)] = func_get_arg(1);
        } else {
            throw new BadFunctionCallException("This function requires an array, or a Key/Value pair.", 1);
        }
    }
}
