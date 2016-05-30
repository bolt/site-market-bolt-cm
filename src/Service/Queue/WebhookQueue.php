<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service\Queue;

use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\LockHandler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Webhook processing queue.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class WebhookQueue extends AbstractQueue
{
    const CACHE_DIR_WEBHOOK_PENDING = 'cache/.satis/queue/webhook/pending';
    const CACHE_DIR_WEBHOOK_PROCESSED = 'cache/.satis/queue/webhook/processed';

    /** @var PackageQueue */
    protected $packageQueue;

    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function queue(Request $request)
    {
        $queuePendingDir = $this->getCachePath(self::CACHE_DIR_WEBHOOK_PENDING);
        $queueProcessedDir = $this->getCachePath(self::CACHE_DIR_WEBHOOK_PROCESSED);
        $event = $request->headers->get('X-GitHub-Event', null);
        $delivery = $request->headers->get('X-GitHub-Delivery', null);
        $signature = $request->headers->get('X-Hub-Signature', null);
        $token = $request->query->get('token');

        if ($event === null || $delivery === null || $token === null) {
            return new JsonResponse(['status' => 'error', 'response' => 'Invalid request'], Response::HTTP_BAD_REQUEST);
        }

        $fs = new Filesystem();
        if (!$fs->exists($queuePendingDir)) {
            $fs->mkdir($queuePendingDir);
        }
        if (!$fs->exists($queueProcessedDir)) {
            $fs->mkdir($queueProcessedDir);
        }

        $payload = json_decode($request->getContent(), true);
        $payload['source'] = $request->server->get('REMOTE_HOST') ?: gethostbyaddr($request->server->get('REMOTE_ADDR'));
        $payload['ip'] = $request->server->get('REMOTE_ADDR');
        $payload['signature'] = $signature;

        $webhookQueueFile = sprintf('%s/%s#%s', $queuePendingDir, $token, time());
        $fs->dumpFile($webhookQueueFile, json_encode($payload));

        $response = new JsonResponse(['status' => 'OK']);

        return $response;
    }

    /**
     * @param OutputInterface $output
     */
    public function process(OutputInterface $output)
    {
        $lockDir = $this->getCachePath(QueueManager::CACHE_DIR_LOCK);
        $queuePendingDir = $this->getCachePath(self::CACHE_DIR_WEBHOOK_PENDING);
        $queueProcessedDir = $this->getCachePath(self::CACHE_DIR_WEBHOOK_PROCESSED);

        $fs = new Filesystem();

        $finder = new Finder();
        $files = $finder
            ->files()
            ->ignoreDotFiles(true)
            ->in($queuePendingDir)
            ->depth(0)
            ->ignoreUnreadableDirs()
        ;

        /** @var SplFileInfo $file */
        foreach ($files as $file) {
            $lock = new LockHandler($file->getFilename(), $lockDir);
            if (!$lock->lock()) {
                $output->writeln(sprintf('<error>[Q] Unable to get lock on %s</error>', $file->getFilename()));
                continue;
            }

            $payload = json_decode($file->getContents());
            $fileName = $file->getFilename();
            $parts = explode('#', $fileName);
            $token = $parts[0];

            /** @var Repository\Package $packageRepo */
            $packageRepo = $this->em->getRepository(Entity\Package::class);

            /** @var Entity\Package $package */
            $package = $packageRepo->findOneBy(['token' => $token]);
            if ($package) {
                // @TODO
                //if ($payload->security) {
                //    $this->validateGitHubSignature($package->getTokenSecret(), $payload->security, $file->getContents());
                //}

                $this->getPackgeQueue()->queue($package);

                /** @var Repository\Stat $statRepo */
                $statRepo = $this->em->getRepository(Entity\Stat::class);
                $stat = new Entity\Stat([
                    'package_id' => $package->getId(),
                    'type'       => 'webhook',
                    'source'     => $payload->source,
                    'ip'         => $payload->ip,
                    'recorded'   => new \DateTime(),
                ]);
                $statRepo->save($stat);

                $output->writeln(sprintf('<info>[Q] Queued package update for %s</info>', $package->getName()));
            } else {
                $output->writeln(sprintf('<error>[Q] Package not found for %s</error>', $file->getFilename()));
            }

            $fs->copy($file->getPathname(), sprintf('%s/%s', $queueProcessedDir, $file->getFilename()));
            $fs->remove($file->getPathname());

            $lock->release();
        }
    }

    /**
     * @return PackageQueue
     */
    protected function getPackgeQueue()
    {
        if ($this->packageQueue === null) {
            $this->packageQueue = new PackageQueue($this->em, $this->resourceManager);
        }

        return $this->packageQueue;
    }

    /**
     * @param string $secret
     * @param string $signatureHeader
     * @param string $payload
     *
     * @return bool
     */
    private function validateGitHubSignature($secret, $signatureHeader, $payload)
    {
        list($algorithm, $gitHubSignature) = explode('=', $signatureHeader);
        $payloadHash = hash_hmac($algorithm, $payload, $secret);

        return $payloadHash === $gitHubSignature;
    }
}
