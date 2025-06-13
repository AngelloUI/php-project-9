<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../templates', [
    'cache' => false,
    'debug' => true]
);
$app->add(TwigMiddleware::create($app, $twig));

$app->addErrorMiddleware(true, true, true);

$app->get('/welcome', function ($request, $response) use ($twig) {

    return $twig->render($response, 'welcome.html.twig');
})->setName('welcome');

$app->get('/', function ($request, $response) use ($twig) {

    return $twig->render($response, 'main.html.twig');
})->setName('main');

$app->run();