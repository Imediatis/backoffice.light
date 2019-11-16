<?php

use Digitalis\Core\Handlers\CustomErrorHandler;
use Digitalis\Core\Handlers\CustomNotAllowedHandler;
use Digitalis\Core\Handlers\CustomNotFoundHandler;
use Digitalis\Core\Handlers\CustomPhpErrorHandler;
use Digitalis\Core\Middlewares\ActionLoggerMiddleware;
use Digitalis\Core\Middlewares\InjectorMiddleware;
use Digitalis\Core\Middlewares\ResellerMiddleware;
use Digitalis\Core\Middlewares\RouteCaseInsensitiveMiddleware;

$c = $app->getContainer();


$c['notAllowedHandler'] = function ($c) {
    return new CustomNotAllowedHandler($c);
};

$app->add(new InjectorMiddleware($c));

$app->add(new ResellerMiddleware($c));

$app->add(new ActionLoggerMiddleware($c));

unset($app->getContainer()['notFoundHandler']);
$app->getContainer()['notFoundHandler'] = function ($c) {
    return new CustomNotFoundHandler($c);
};

unset($app->getContainer()['errorHandler']);
$app->getContainer()['errorHandler'] = function ($c) {
    return new CustomErrorHandler($c);
};

unset($app->getContainer()['phpErrorHandler']);
$app->getContainer()['phpErrorHandler'] = function ($c) {
    return new CustomPhpErrorHandler($c);
};

$app->add(new RouteCaseInsensitiveMiddleware($c));
