<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Extension\Bolt\Members\AccessControl\Session as MembersSession;
use Github\Client as GithubClient;
use Silex\Application;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Webhook manager class.
 *
 * @see https://developer.github.com/v3/repos/hooks/#create-a-hook
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class WebhookManager
{
    /** @var Session */
    protected $session;
    /** @var Application */
    private $app;

    /**
     * Constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->session = $app['session'];
    }

    /**
     * @param string $username
     * @param string $repository
     *
     * @return array
     */
    public function getRateLimit($username, $repository)
    {
        if (!$client = $this->getGitHubClient()) {
            return [];
        }

        /** @var \Github\Api\RateLimit $apiRateLimit */
        $apiRateLimit = $client->api('rate_limit');
        $rateLimits = $apiRateLimit->getRateLimits();

        return $rateLimits;
    }

    /**
     *
     *
     * @param string $username
     * @param string $repository
     * @param string $callbackToken
     *
     * @return bool
     */
    public function createWebhook($username, $repository, $callbackToken)
    {
        if (!$client = $this->getGitHubClient()) {
            return false;
        }

        /** @var \Github\Api\Repo $apiRepo */
        $apiRepo = $client->api('repo');

        try {
            $result = $apiRepo->hooks()->create($username, $repository, $this->getWebhookParameters($callbackToken));
        } catch (\Exception $e) {
            $this->session->getFlashBag()->add('error', sprintf('Exception type: %s', get_class($e)));
            $this->session->getFlashBag()->add('error', sprintf('Exception occurred creating webhook: %s', $e->getMessage()));

            return false;
        }

        if (!$this->isHookValid((array) $result, $callbackToken)) {
            $this->session->getFlashBag()->add('error', sprintf('Response invalid creating webhook: %s', json_encode($result)));

            return false;
        }

        $this->session->getFlashBag()->add('success', 'Successfully created webhook!');

        return true;
    }

    /**
     * Ping our webhook to see if it's working.
     *
     * @param string $username
     * @param string $repository
     * @param string $id
     *
     * @return bool
     */
    public function pingWebhook($username, $repository, $id)
    {
        if (!$client = $this->getGitHubClient()) {
            return false;
        }

        /** @var \Github\Api\Repo $apiRepo */
        $apiRepo = $client->api('repo');

        try {
            $result = $apiRepo->hooks()->ping($username, $repository, $id);

        } catch (\Exception $e) {
            $this->session->getFlashBag()->add('error', sprintf('Exception type: %s', get_class($e)));
            $this->session->getFlashBag()->add('error', sprintf('Exception occurred pinging webhook: %s', $e->getMessage()));

            return false;
        }
        $this->session->getFlashBag()->add('success', 'Successfully pinged webhook!');

        return true;
    }

    /**
     * Remove our webhook.
     * 
     * @param string $username
     * @param string $repository
     * @param string $id
     *
     * @return bool
     */
    public function removeWebhook($username, $repository, $id)
    {
        if (!$client = $this->getGitHubClient()) {
            return false;
        }

        /** @var \Github\Api\Repo $apiRepo */
        $apiRepo = $client->api('repo');

        try {
            $result = $apiRepo->hooks()->remove($username, $repository, $id);

        } catch (\Exception $e) {
            $this->session->getFlashBag()->add('error', sprintf('Exception type: %s', get_class($e)));
            $this->session->getFlashBag()->add('error', sprintf('Exception occurred removing webhook: %s', $e->getMessage()));

            return false;
        }

        return true;
    }

    /**
     * Return an authorised GitHub client object.
     *
     * @return GithubClient|null
     */
    protected function getGitHubClient()
    {
        if (!$accessToken = $this->getAccessToken()) {
            return null;
        }

        /** @var GithubClient $client */
        $client = $this->app['github.api']['client'];
        $client->authenticate($accessToken, $accessToken, GithubClient::AUTH_HTTP_TOKEN);

        return $client;
    }

    /**
     * Return the in-use access token from the Members session.
     *
     * @return \League\OAuth2\Client\Token\AccessToken
     */
    protected function getAccessToken()
    {
        /** @var MembersSession $membersSession */
        $membersSession = $this->app['members.session'];

        if ($membersSession->hasAuthorisation() === false) {
            return null;
        }

        return $membersSession->getAuthorisation()->getAccessToken('github');
    }

    /**
     * Checks a hook array for validity.
     *
     * @param array  $hook
     * @param string $token
     *
     * @return bool
     */
    protected function isHookValid(array $hook, $token)
    {
        if (!isset($hook['type']) || $hook['type'] !== 'Repository') {
            return false;
        }
        if (!isset($hook['name']) || $hook['name'] !== 'web') {
            return false;
        }
        if (!isset($hook['active']) || !$hook['active']) {
            return false;
        }
        if (!isset($hook['config']['content_type']) || $hook['config']['content_type'] !== 'json') {
            return false;
        }

        if (!isset($hook['config']['insecure_ssl']) || $hook['config']['insecure_ssl']) {
            return false;
        }

        if (!isset($hook['config']['url']) || $hook['config']['url'] !== $this->getCallbackUrl($token)) {
            return false;
        }
        if (!isset($hook['events']) || array_diff($hook['events'], $this->getHookEvents()) !== []) {
            return false;
        }

        return true;
    }

    /**
     * Retrun an array of parameters.
     *
     * @param string $token
     *
     * @return array
     */
    protected function getWebhookParameters($token)
    {
        return [
            'name'   => 'web',
            'active' => true,
            'events' => $this->getHookEvents(),
            'config' => [
                'content_type' => 'json',
                'insecure_ssl' => 0,
                'url'          => $this->getCallbackUrl($token),
            ],
        ];
    }

    /**
     * Return a valid callback URL.
     *
     * @param string $callbackToken
     *
     * @return string
     */
    protected function getCallbackUrl($callbackToken)
    {
        return sprintf('%s/hook?token=%s', $this->app['resources']->getUrl('hostname'), $callbackToken);
    }

    /**
     * The events our hooks wants to trigger on.
     *
     * @return array
     */
    protected function getHookEvents()
    {
        return [
            'create',
            'delete',
            'push',
            'release',
        ];
    }
}
