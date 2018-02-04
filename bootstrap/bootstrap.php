<?php

define('PROJECT_ROOT', dirname(__DIR__));

spl_autoload_register(function ($class)
{
    $path = PROJECT_ROOT . DIRECTORY_SEPARATOR . $class . '.php';

    if (file_exists($path))
    {
        require_once $path;
    }
});