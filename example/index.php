<?php

// application bootstrapping
error_reporting(-1);
date_default_timezone_set('Australia/Brisbane');

// include external libraries
require_once '../vendor/autoload.php';
require_once 'functions.php';

// dispatch a response based on the current request
send_response('<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
    </head>
    <body>
        <video src="video.mp4" controls="controls"></video>
    </body>
</html>');
