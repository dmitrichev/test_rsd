<?php

return [
    'start_password' => 1650,
    'requests_per_iteration' => 50,
    'form_address' => 'http://www.rollshop.co.il/test.php',
    'use_proxy' => false,
    'proxy_list' => include PROJECT_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'proxy_list.php'
];