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
use Symfony\Component\HttpFoundation\Request;

use Aura\Router\Router;

use Composer\Config as Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Config\JsonConfigSource;

use Bolt\Extensions\Application;
use Bolt\Extensions\Firewall;
use Bolt\Extensions\Action;


Symfony\Component\Debug\Debug::enable();
@include_once 'env.php';
return [

    'debug' => false, 

    Application::class => DI\object(),

    Firewall::class => DI\object()
        ->constructorParameter('restrict', DI\link('userfirewall'))
        ->lazy(),
    
    
    'db' => DI\factory(function($c){
        return [
            'driver'     => 'pdo_mysql',
            'dbname'     => 'bolt_extensions',
            'host'       => '127.0.0.1',
            'user'       => 'bolt_extensions',
            'password'   => getenv('APP_DB_PASSWORD')
        ];
    }),


    DB::class => DI\factory(function($c) {
        return DriverManager::getConnection($c->get('db'));
    }),
    
    EntityManager::class => DI\factory(function($c){
        $driver = new StaticPHPDriver(dirname(__DIR__) . '/src/Entity');
        $config = Setup::createConfiguration(true);
        $config->setMetadataDriverImpl($driver);
        $config->setAutoGenerateProxyClasses($c->get('debug'));
        $em = EntityManager::create($c->get(DB::class), $config);
        return $em;
    }),
    
    'userfirewall' => [
        Action\Submit::class,
        Action\EditPackage::class,
        Action\TestExtension::class,
        Action\Profile::class  
    ],
    
    "migrations" => DI\factory(function($c){
        $m = new MigrateConfig($c->get(DB::class));
        $m->setMigrationsDirectory(dirname(__DIR__)."/src/Migrations");
        $m->setMigrationsNamespace("Bolt\Extensions");
        $m->registerMigrationsFromDirectory(dirname(__DIR__)."/src/Migrations");
        return $m;
    }),

 
    Composer::class => DI\factory(function($c){
        // Beware changing or removing this can have bad effects, since the server may run as the home user
        // If this is the case, the composer library will output an .htaccess that will deny access to the server!
        putenv("COMPOSER_HOME=".sys_get_temp_dir());
        $io = new NullIO();
        $config = Factory::createConfig($io);
        $file = new JsonFile(__DIR__.'/github.json');
        if ($file->exists()) {
            $config->merge(array('config' => $file->read()));
        }
        $config->setAuthConfigSource(new JsonConfigSource($file, true));
        return $config;
    }),
    
    Twig_Environment::class => DI\factory(function ($c) {
        $loader = new Twig_Loader_Filesystem(__DIR__ . '/../src/Templates');
        $twig = new Twig_Environment($loader);
        $formEngine = new TwigRendererEngine(["forms.html"]);
        $formEngine->setEnvironment($twig);
        $twig->addExtension(new FormExtension(new TwigRenderer($formEngine)));
        $twig->addExtension(new Bolt\Extensions\Helper\Url($c->get(Router::class)));
        $twig->addExtension(new Bolt\Extensions\Helper\Bolt());
        $twig->addGlobal('request', $c->get(Request::class));
        $twig->addGlobal('session', $c->get(Request::class)->getSession());
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
            $c->get(Bolt\Extensions\Command\UpdatePackage::class),
            $c->get(Bolt\Extensions\Command\ExtensionTestRunner::class)
        ];
    }),

    
    


];
