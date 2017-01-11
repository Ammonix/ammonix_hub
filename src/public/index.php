<?php
use Slim\Http\Request as Request;
use Slim\Http\Response as Response;

require '../vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);

$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig('../templates', [
        'cache' => false,
        'autoreload' => true,
        'debug' => true
    ]);
    // Instantiate and add Slim specific extension
    $basePath = "https://ammonix.ch";
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));
    return $view;
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response->withStatus(404), 'HTTPStatus/404.html.twig');
    };
};

$app->get('/', function (Request $request, Response $response, array $args) {
    return $this->view->render($response, 'home/main.html.twig');
})->setName('home');

$app->run();