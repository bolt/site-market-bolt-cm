<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Satis JSON management class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class SatisManager
{
    /** @var EntityManager */
    protected $em;
    /** @var ResourceManager */
    protected $resourceManager;

    /**
     * Constructor.
     *
     * @param EntityManager   $em
     * @param ResourceManager $resourceManager
     */
    public function __construct(EntityManager $em, ResourceManager $resourceManager)
    {
        $this->em = $em;
        $this->resourceManager = $resourceManager;
    }

    /**
     */
    public function dump()
    {
        $fs = new Filesystem();
        $jsonFilePath = $this->getPath();
        $fs->dumpFile($jsonFilePath, $this->getSatisJson());
    }

    public function getPath()
    {
        return $this->resourceManager->getPath('root'). '/satis.json';
    }

    /**
     * @return string
     */
    protected function getSatisJson()
    {
        $packages = $this->em
            ->getRepository(Entity\Package::class)
            ->findBy(['approved' => true])
        ;

        $satisArray = $this->getSatisJsonTempate();
        foreach ($packages as $package) {
            $satisArray['repositories'][] = [
                'type' => 'vcs',
                'url'  => $package->source
            ];
        }

        $this->getSatisExtraRepositories($satisArray);

        return json_encode($satisArray);
    }

    /**
     * @return array
     */
    protected function getSatisJsonTempate()
    {
        return [
            'name'         => 'Bolt Extensions Repository',
            'homepage'     => 'http://extensions.bolt.cm/satis',
            'repositories' => [],
            'output-dir'   => $this->resourceManager->getPath('web/satis'),
        ];
    }

    /**
     * @param array $satisArray
     *
     * @return array
     */
    protected function getSatisExtraRepositories(array $satisArray)
    {
        $repoFile = $this->resourceManager->getPath('config/satis/repos.yml');
        $repoConfig = Yaml::parse(file_get_contents($repoFile));
        foreach (array_keys($repoConfig) as $type) {
            foreach ($repoConfig[$type] as $url) {
                $satisArray['repositories'][] = ['type' => $type, 'url' => $url];
            }
        }

        return $satisArray;
    }
}
