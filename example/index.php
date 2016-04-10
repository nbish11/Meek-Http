<?php

// application bootstrapping
error_reporting(-1);
date_default_timezone_set('Australia/Brisbane');

// include external libraries
require_once '../vendor/autoload.php';
require_once 'FictionalRouter.php';

// use statements
use Meek\Http\Request;
use Meek\Http\Session\PdoStorageDriver;
use Meek\Http\Session;
use Meek\Http\Response;
use Meek\Http\RedirectedResponse;
use Meek\Http\Exception as HttpException;

// get the current request
$request = Request::createFromGlobals();

// manipulate the current URI
$uri = $request->getUri();
$uri->setPath(substr($uri->getPath(), strlen('/meek-http')));

// initialize a session
$dbh = new PDO('mysql:host=localhost;dbname=test', 'root', '', [
    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
]);
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$handler = new PdoStorageDriver($dbh);
$session = new Session($handler);

$request->setSession($session);
$request->session->start();

// setup and initalize our application's routes
$router = new FictionalRouter();

// basic usage
$router->map('GET', '/', function ($request) {
    $username = $request->session->get('username', 'World');

    return new Response(sprintf('Hello, %s!', $username));
});

// working with headers and redirections
$router->map('GET', '/login', function ($request) {
    $username = $request->server->get('PHP_AUTH_USER');
    $password = $request->server->get('PHP_AUTH_PW');

    if ($username === 'admin' && $password === 'password') {
        $request->session->set('username', $username);
        return new RedirectedResponse('/meek-http/account');
    }

    return new Response('Please sign in.', 401, [
        'WWW-Authenticate' => sprintf('Basic realm=%s', 'site_login')
    ]);
});

// working with JSON responses
$router->map('GET', '/api/v1', function () {
    $user = new stdClass();
    $user->name = 'Nathan';
    $user->dob = '24-02-1991';

    return new Meek\Http\JsonResponse($user);
});

// save session to store and dispatch request
$request->session->save();
$router->dispatch($request);
