<?php
use Aura\Router\RouterFactory;


$router_factory = new RouterFactory;
$router = $router_factory->newInstance();

/******* Main Page Routes ************/
$router->add("home", "/")->setValues(['action'=>'Bolt\Extensions\Action\Home']);
$router->add("submit", "/submit")->setValues(['action'=>'Bolt\Extensions\Action\Submit']);
$router->add("submitted", "/submitted")->setValues(['action'=>'Bolt\Extensions\Action\Submitted']);
$router->add("docs", "/docs")->setValues(['action'=>'Bolt\Extensions\Action\Docs']);
$router->add("register", "/register")->setValues(['action'=>'Bolt\Extensions\Action\Register']);

$router->add("search", "/search")->setValues(['action'=>'Bolt\Extensions\Action\Search']);


return $router;
