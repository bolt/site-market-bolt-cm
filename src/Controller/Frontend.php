<?php

namespace Bolt\Extension\Bolt\MarketPlace\Controller;

use Bolt\Extension\Bolt\MarketPlace\Action\ActionInterface;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Ramsey\Uuid\Uuid;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Frontend controller.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Frontend implements ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];

        $ctr->match('/browse', [$this, 'browse'])
            ->bind('browse')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/edit/{package}', [$this, 'edit'])
            ->bind('edit')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/', [$this, 'home'])
            ->bind('home')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/feed.xml', [$this, 'feed'])
            ->bind('feed')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/hook/create', [$this, 'hookCreator'])
            ->bind('hookCreator')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/hook', [$this, 'hookListener'])
            ->bind('hookListener')
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/info.json', [$this, 'infoJson'])
            ->bind('infoJson')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/profile', [$this, 'profile'])
            ->bind('profilePackages')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/profile/starred', [$this, 'profileStarred'])
            ->bind('profileStarred')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/list.json', [$this, 'listJson'])
            ->bind('listJson')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/list/downloaded.json', [$this, 'listDownloadedJson'])
            ->bind('listDownloadedJson')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/login', [$this, 'login'])
            ->bind('loginRedirect')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/logout', [$this, 'logout'])
            ->bind('logoutRedirect')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/ping', [$this, 'ping'])
            ->bind('ping')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/register', [$this, 'register'])
            ->bind('registerRedirect')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/reset', [$this, 'reset'])
            ->bind('resetRedirect')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/search', [$this, 'search'])
            ->bind('search')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/search.json', [$this, 'searchJson'])
            ->bind('searchJson')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/star/{package}', [$this, 'star'])
            ->bind('star')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/stat/install/{author}/{package}/{version}', [$this, 'statCollectInstall'])
            ->bind('statCollectInstall')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/stats/api/{package}', [$this, 'statsApi'])
            ->bind('statsApi')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/stats/{package}', [$this, 'stats'])
            ->bind('stats')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/status/', [$this, 'status'])
            ->bind('status')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/submit', [$this, 'submit'])
            ->bind('submit')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/test/{package}/{version}', [$this, 'testExtension'])
            ->bind('testExtension')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET . '|' . Request::METHOD_POST)
        ;

        $ctr->match('/test/{package}', [$this, 'testListing'])
            ->bind('testListing')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/update/{package}', [$this, 'update'])
            ->bind('updatePackage')
            ->before([$this, 'auth'])
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/view/{package}', [$this, 'view'])
            ->bind('view')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/view/releases/{packageId}', [$this, 'viewPackageReleases'])
            ->bind('viewPackageReleases')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/view/{packageAuthor}', [$this, 'viewPackagesByAuthor'])
            ->bind('viewPackagesByAuthor')
            ->method(Request::METHOD_GET)
        ;

        $ctr->match('/view/{packageAuthor}/{packageName}', [$this, 'viewPackage'])
            ->bind('viewPackage')
            ->method(Request::METHOD_GET)
        ;

        $ctr->before([$this, 'before']);
        $ctr->after([$this, 'after']);

        return $ctr;
    }

    /**
     * Middleware to modify the Response before the controller is executed.
     *
     * @param Request     $request
     * @param Application $app
     *
     * @return null|RedirectResponse
     */
    public function auth(Request $request, Application $app)
    {
        if ($app['auth.session']->hasAuthorisation()) {
            return null;
        }

        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $app['url_generator'];
        $route = $urlGen->generate('authenticationLogin');

        return new RedirectResponse($route);
    }

    /**
     * Middleware to modify the Response before the controller is executed.
     *
     * @param Request     $request
     * @param Application $app
     */
    public function before(Request $request, Application $app)
    {
    }

    /**
     * Middleware to modify the Response before it is sent to the client.
     *
     * @param Request     $request
     * @param Response    $response
     * @param Application $app
     */
    public function after(Request $request, Response $response, Application $app)
    {
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function browse(Application $app, Request $request)
    {
        $params = [
            'type'    => 'browse',
            'version' => $request->query->get('version'),
        ];

        return $this->getAction($app, 'search')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function search(Application $app, Request $request)
    {
        $params = [
            'type' => 'search',
        ];

        return $this->getAction($app, 'search')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function searchJson(Application $app, Request $request)
    {
        $params = [
            'type' => 'search',
        ];

        return $this->getAction($app, 'search_json')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function edit(Application $app, Request $request, $package)
    {
        $params = [
            'user'    => $app['auth.session']->getAuthorisation(),
            'package' => $package,
        ];

        return $this->getAction($app, 'package_edit')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function home(Application $app, Request $request)
    {
        return $this->getAction($app, 'home')->execute($request, []);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function feed(Application $app, Request $request)
    {
        $params = [];

        return $this->getAction($app, 'feed')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function hookCreator(Application $app, Request $request)
    {
        $params = [];

        return $this->getAction($app, 'webhook_creator')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function hookListener(Application $app, Request $request)
    {
        $params = [];

        return $this->getAction($app, 'webhook_listener')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function infoJson(Application $app, Request $request)
    {
        $params = [];

        return $this->getAction($app, 'package_info')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function profile(Application $app, Request $request)
    {
        $params = [
            'user' => $app['auth.session']->getAuthorisation(),
        ];

        return $this->getAction($app, 'account_profile')->execute($request, $params);
    }

    public function profileStarred(Application $app, Request $request)
    {
        $params = [
            'user' => $app['auth.session']->getAuthorisation(),
        ];

        return $this->getAction($app, 'account_starred')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function listJson(Application $app, Request $request)
    {
        $params = [
        ];

        return $this->getAction($app, 'list_packages')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function listDownloadedJson(Application $app, Request $request)
    {
        $params = [
            'sort' => 'downloaded',
            'type' => $request->query->get('type', 'bolt-extension'),
        ];

        return $this->getAction($app, 'list_packages')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function login(Application $app, Request $request)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $app['url_generator'];
        $route = $urlGen->generate('authenticationLogin');

        return new RedirectResponse($route);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function logout(Application $app, Request $request)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $app['url_generator'];
        $route = $urlGen->generate('authenticationLogout');

        return new RedirectResponse($route);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function ping(Application $app, Request $request)
    {
        $params = [];

        return $this->getAction($app, 'ping')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function reset(Application $app, Request $request)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $app['url_generator'];
        $route = $urlGen->generate('authenticationPasswordReset');

        return new RedirectResponse($route);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function register(Application $app, Request $request)
    {
        /** @var UrlGeneratorInterface $urlGen */
        $urlGen = $app['url_generator'];
        $route = $urlGen->generate('authProfileRegister');

        return new RedirectResponse($route);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function star(Application $app, Request $request, $package)
    {
        $params = [
            'package' => $package,
        ];

        return $this->getAction($app, 'package_star')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $author
     * @param string      $package
     * @param string      $version
     *
     * @return Response
     */
    public function statCollectInstall(Application $app, Request $request, $author, $package, $version)
    {
        $params = [
            'id'      => 'install',
            'package' => sprintf('%s/%s', $author, $package),
            'version' => $version,
        ];

        return $this->getAction($app, 'stat')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function stats(Application $app, Request $request, $package)
    {
        $params = [
            'user'    => $app['auth.session']->getAuthorisation(),
            'package' => $package,
        ];

        return $this->getAction($app, 'package_stats')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function statsApi(Application $app, Request $request, $package)
    {
        $params = [
            'user'    => $app['auth.session']->getAuthorisation(),
            'package' => $package,
        ];

        return $this->getAction($app, 'package_stats_api')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function status(Application $app, Request $request)
    {
        $params = [
            'user' => $app['auth.session']->getAuthorisation(),
        ];

        return $this->getAction($app, 'status')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function submit(Application $app, Request $request)
    {
        $params = [
            'user' => $app['auth.session']->getAuthorisation(),
        ];

        return $this->getAction($app, 'submit')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     * @param string      $version
     *
     * @return Response
     */
    public function testExtension(Application $app, Request $request, $package, $version)
    {
        $params = [
            'package' => $package,
            'version' => $version,
        ];

        return $this->getAction($app, 'test_extension')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function testListing(Application $app, Request $request, $package)
    {
        $params = [
            'package' => $package,
        ];

        return $this->getAction($app, 'test_listing')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function update(Application $app, Request $request, $package)
    {
        $params = [
            'package' => $package,
        ];

        return $this->getAction($app, 'package_update')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $package
     *
     * @return Response
     */
    public function view(Application $app, Request $request, $package)
    {
        if (Uuid::isValid($package)) {
            $repo = $app['storage']->getRepository(Entity\Package::class);
            $packageEntity = $repo->findOneBy(['id' => $package]);
            if ($packageEntity === false) {
                $html = $app['twig']->render('not-found.twig', ['reason' => 'Package does not exist.']);

                return new Response($html, Response::HTTP_NOT_FOUND);
            }

            $parts = explode('/', $packageEntity->getName());
            /** @var UrlGeneratorInterface $urlGen */
            $urlGen = $app['url_generator'];
            $route = $urlGen->generate('viewPackage', [
                'packageAuthor' => $parts[0],
                'packageName'   => $parts[1],
            ]);

            return new RedirectResponse($route);
        }

        $params = [
            'package' => $package,
        ];

        return $this->getAction($app, 'package_view')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $packageAuthor
     * @param string      $packageName
     *
     * @return Response
     */
    public function viewPackage(Application $app, Request $request, $packageAuthor, $packageName)
    {
        $params = [
            'package_author' => $packageAuthor,
            'package_name'   => $packageName,
        ];

        return $this->getAction($app, 'package_view')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $packageId
     *
     * @return Response
     */
    public function viewPackageReleases(Application $app, Request $request, $packageId)
    {
        $params = [
            'package' => $packageId,
        ];

        return $this->getAction($app, 'package_releases')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param Request     $request
     * @param string      $packageAuthor
     *
     * @return Response
     */
    public function viewPackagesByAuthor(Application $app, Request $request, $packageAuthor)
    {
        $params = [
            'package_author' => $packageAuthor,
        ];

        return $this->getAction($app, 'packages_author')->execute($request, $params);
    }

    /**
     * @param Application $app
     * @param string      $name
     *
     * @return ActionInterface
     */
    private function getAction(Application $app, $name)
    {
        if (!isset($app['marketplace.actions'][$name])) {
            throw new \BadMethodCallException(sprintf('Action "%s" does not exist', $name));
        }

        return $app['marketplace.actions'][$name];
    }
}
