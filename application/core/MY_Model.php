<?php

include_once __DIR__.'/SuperModel.php';

class MY_Model extends SuperModel
{
    public function __construct()
    {
        if (isset($_GET['debug']) && $_GET['debug'] == 'true') {
            define('DEBUG', true);
        } else {
            define('DEBUG', false);
        }
        parent::__construct();
    }



}