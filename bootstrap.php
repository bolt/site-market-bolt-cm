<?php
use DI\ContainerBuilder;
$router = include(__DIR__."/config/routes.php");

$builder = new ContainerBuilder();
$builder->useAnnotations(false);
$builder->addDefinitions(__DIR__."/config/config.php");
$container = $builder->build();
$container->set("Aura\Router\Router", $router);

$app = $container->get("Bolt\Extensions\Application");

$app = (new Stack\Builder())
        ->push('Stack\Session')
        ->push('Stack\Aura\RequestRouter', $router)
        ->resolve($app);

Stack\run($app);