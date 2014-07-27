<?php
use Aura\Router\RouterFactory;


$router_factory = new RouterFactory;
$router = $router_factory->newInstance();

/******* Main Page Routes ************/
$router->add("home", "/")->setValues(['action'=>'Bolt\Extensions\Action\Home']);
$router->add("submit", "/submit")->setValues(['action'=>'Bolt\Extensions\Action\Submit']);
$router->add("submitted", "/submitted")->setValues(['action'=>'Bolt\Extensions\Action\Submitted']);

$router->add("register", "/register")->setValues(['action'=>'Bolt\Extensions\Action\Register']);
$router->add("admin", "/admin")->setValues(['action'=>'Bolt\Extensions\Action\Admin']);
$router->add("login", "/login")->setValues(['action'=>'Bolt\Extensions\Action\Login']);
$router->add("logout", "/logout")->setValues(['action'=>'Bolt\Extensions\Action\Logout']);


$router->add("search", "/search")->setValues(['action'=>'Bolt\Extensions\Action\Search']);
$router->add("list", "/list.json")->setValues(['action'=>'Bolt\Extensions\Action\ListPackages']);

$router->add("docs", "/docs")->setValues(['action'=>'Bolt\Extensions\Action\Docs']);
$router->add("doc.page", "/docs/{page}")->setValues(['action'=>'Bolt\Extensions\Action\Docs']);



return $router;
