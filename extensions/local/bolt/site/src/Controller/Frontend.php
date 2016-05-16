<?php

namespace Bolt\Extension\Bolt\ExtensionSite\Controller;

use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
            ->method('GET')
        ;

        $ctr->match('/edit', [$this, 'edit'])
            ->bind('edit')
            ->method('GET|POST')
        ;

        $ctr->match('/', [$this, 'home'])
            ->bind('home')
            ->method('GET|POST')
        ;

        $ctr->match('/profile', [$this, 'profile'])
            ->bind('profile')
            ->method('GET|POST')
        ;

        $ctr->match('/login', [$this, 'login'])
            ->bind('login')
            ->method('GET|POST')
        ;

        $ctr->match('/logout', [$this, 'logout'])
            ->bind('logout')
            ->method('GET|POST')
        ;

        $ctr->match('/reset', [$this, 'reset'])
            ->bind('reset')
            ->method('GET|POST')
        ;

        $ctr->match('/star', [$this, 'star'])
            ->bind('star')
            ->method('GET|POST')
        ;

        $ctr->match('/stats', [$this, 'stats'])
            ->bind('stats')
            ->method('GET')
        ;

        $ctr->match('/submit', [$this, 'submit'])
            ->bind('submit')
            ->method('GET|POST')
        ;

        $ctr->match('/tests', [$this, 'tests'])
            ->bind('tests')
            ->method('GET|POST')
        ;

        $ctr->match('/update', [$this, 'update'])
            ->bind('update')
            ->method('GET|POST')
        ;

        $ctr->match('/view', [$this, 'view'])
            ->bind('view')
            ->method('GET')
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
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function edit(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function home(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function profile(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function login(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function logout(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function reset(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function star(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function stats(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function submit(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function tests(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function update(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }

    /**
     * @param Application $app
     * @param Request     $request
     *
     * @return Response
     */
    public function view(Application $app, Request $request)
    {
        return new Response(sprintf('Not yet implemented: %s::%s', __CLASS__, __FUNCTION__));
    }
}
