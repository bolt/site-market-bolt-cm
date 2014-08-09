<?php

use Doctrine\DBAL\Migrations\Configuration\Configuration as MigrateConfig;
use Doctrine\DBAL\Driver\Connection as DB;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\DBAL\DriverManager;


use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;


use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormFactory;

use Aura\Router\Router;

use Bolt\Extensions\Application;


Symfony\Component\Debug\Debug::enable();

return [

    Application::class => DI\object(),
    
    
    'db'=> [
        'driver'     => 'pdo_pgsql',
        'dbname'     => 'bolt_extensions',
        'host'       => '127.0.0.1',
        'user'       => 'bolt_extensions',
        'password'   => 'bolt30080',
    ],


    DB::class => DI\factory(function($c) {
        return DriverManager::getConnection($c->get('db'));
    }),
    
    EntityManager::class => DI\factory(function($c){
        $driver = new StaticPHPDriver(dirname(__DIR__) . '/src/Entity');
        $config = Setup::createConfiguration(true);
        $config->setMetadataDriverImpl($driver);
        $em = EntityManager::create($c->get(DB::class), $config);
        return $em;
    }),
    
    "migrations" => DI\factory(function($c){
        $m = new MigrateConfig($c->get(DB::class));
        $m->setMigrationsDirectory(dirname(__DIR__)."/src/Migrations");
        $m->setMigrationsNamespace("Bolt\Extensions");
        $m->registerMigrationsFromDirectory(dirname(__DIR__)."/src/Migrations");
        return $m;
    }),

 
    
    
    Twig_Environment::class => DI\factory(function ($c) {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../src/Templates');
        $twig = new Twig_Environment($loader);
        $formEngine = new TwigRendererEngine(["forms.html"]);
        $formEngine->setEnvironment($twig);
        $twig->addExtension(new FormExtension(new TwigRenderer($formEngine)));
        $twig->addExtension(new Bolt\Extensions\Helper\Url($c->get(Router::class)));
        return $twig;
    }),
    
    FormFactory::class => DI\Factory(function($c){
        return Forms::createFormFactoryBuilder()
            ->addType(new Bolt\Extensions\Form\PackageForm)
            ->addType(new Bolt\Extensions\Form\AccountForm)
            ->getFormFactory();
    }),
    
    
    'console.commands' => DI\factory(function($c){
        return [
            $c->get(Bolt\Extensions\Command\Satis::class),
            $c->get(Bolt\Extensions\Command\Builder::class),
            $c->get(Bolt\Extensions\Command\UpdatePackage::class)
        ];
    }),

    
    


];
