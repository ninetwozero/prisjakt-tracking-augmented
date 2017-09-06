<?php
    require $_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php';

    session_start();
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/functions.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/prisjakt.class.php';

    if ($_SERVER['SERVER_NAME'] === 'prisjakt.local') {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
    }
