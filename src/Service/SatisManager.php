<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Configuration\ResourceManager;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Extension\Bolt\MarketPlace\Storage\Repository;
use Bolt\Storage\EntityManager;
use Composer\Composer;
use Composer\Config;
use Composer\Config\JsonConfigSource;
use Composer\Factory;
use Composer\IO\BufferIO;
use Composer\IO\IOInterface;
use Composer\IO\NullIO;
use Composer\Json\JsonFile;
use Composer\Json\JsonValidationException;
use Composer\Package\PackageInterface;
use Composer\Satis\Builder\PackagesBuilder;
use Composer\Satis\Builder\WebBuilder;
use Composer\Satis\PackageSelection\PackageSelection;
use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\LockHandler;
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
    /** @var IOInterface */
    protected $io;
    /** @var Composer */
    protected $composer;
    /** @var array */
    protected $config;

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
     * @param string               $packageName
     * @param OutputInterface|null $output
     *
     * @return PackageInterface[]
     */
    public function build($packageName, OutputInterface $output = null)
    {
        if ($output === null) {
            $output = new NullIO();
        }

        $skipErrors = true;
        $htmlView = true;
        $lockDir = $this->resourceManager->getPath('cache/.satis/lock');

        set_time_limit(3600);
        $output->writeln('<info>Acquiring full build lock…</info>');
        $lock = new LockHandler('satis.full.build', $lockDir);
        $lock->lock(true);

        $packages = $this->buildPackages($packageName, $output, $skipErrors);

        if ($htmlView) {
            $this->dumpPackages($packages, $output, $skipErrors);
        }

        return $packages;
    }

    /**
     * @param string          $packageName
     * @param OutputInterface $output
     * @param bool            $skipErrors
     *
     * @throws \Exception
     *
     * @return PackageInterface[]
     */
    public function buildPackages($packageName, OutputInterface $output, $skipErrors = false)
    {
        $output->writeln('<info>Building packages…</info>');
        $packageSelection = new PackageSelection($output, $this->getSatisWebPath(), $this->getConfig(), $skipErrors);

        $packageEntity = $this->em->getRepository(Entity\Package::class)->findOneBy(['name' => $packageName]);
        if ($packageEntity) {
            $packageSelection->setRepositoryFilter($packageEntity->getSource());
        }

        $packages = $packageSelection->select($this->getComposer(), true);
        if ($packageSelection->hasFilterForPackages() || $packageSelection->hasRepositoryFilter()) {
            // in case of an active filter we need to load the dumped packages.json and merge the
            // updated packages in
            $oldPackages = $packageSelection->load();
            $packages += $oldPackages;
            ksort($packages);
        }

        $packagesBuilder = new PackagesBuilder($output, $this->getSatisWebPath(), $this->getConfig(), $skipErrors);
        $packagesBuilder->dump($packages);

        return $packages;
    }

    /**
     * @param OutputInterface $output
     * @param bool            $skipErrors
     *
     * @return PackageInterface[]
     */
    public function getBuiltPackages(OutputInterface $output, $skipErrors = false)
    {
        $output->writeln('<info>Fetching previously built package data…</info>');
        $packageSelection = new PackageSelection($output, $this->getSatisWebPath(), $this->getConfig(), $skipErrors);

        return $packageSelection->load();
    }

    /**
     * @param array           $packages
     * @param OutputInterface $output
     * @param bool            $skipErrors
     */
    public function dumpPackages(array $packages, OutputInterface $output, $skipErrors = false)
    {
        $output->writeln('<info>Writing out web files…</info>');
        $web = new WebBuilder($output, $this->getSatisWebPath(), $this->getConfig(), $skipErrors);
        $web->setRootPackage($this->getComposer()->getPackage());
        $web->dump($packages);
    }

    /**
     */
    public function dumpSatisJson()
    {
        $fs = new Filesystem();
        $jsonFilePath = $this->getSatisJsonFilePath();
        $fs->dumpFile($jsonFilePath, $this->getSatisJson());
    }

    /**
     * @return string
     */
    public function getSatisJsonFilePath()
    {
        return $this->resourceManager->getPath('config/satis/satis.json');
    }

    /**
     * @return string
     */
    public function getSatisWebPath()
    {
        return $this->resourceManager->getPath('web/satis');
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
                'url'  => $package->source,
            ];
        }

        $satisArray = $this->getSatisExtraRepositories($satisArray);

        return json_encode($satisArray);
    }

    /**
     * @return array
     */
    protected function getSatisJsonTempate()
    {
        return [
            'name'          => 'Bolt Extensions Repository',
            'homepage'      => 'http://extensions.bolt.cm/satis',
            'repositories'  => [],
            'output-dir'    => $this->resourceManager->getPath('web/satis'),
            'twig-template' => $this->resourceManager->getPath('theme/satis/satis-index.twig'),
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

    /**
     * @throws JsonValidationException
     * @throws ParsingException
     *
     * @return Composer
     */
    private function getComposer()
    {
        if ($this->composer !== null) {
            return $this->composer;
        }

        $repositoryUrl = null;

        // load auth.json authentication information and pass it to the io interface
        $io = $this->getIo();
        $io->loadConfiguration($this->getConfiguration());

        $file = new JsonFile($this->getSatisJsonFilePath());
        if (!$file->exists()) {
            throw new FileNotFoundException(sprintf('File not found: %s', $this->getSatisJsonFilePath()));
        }

        $this->config = $file->read();
        $this->check($this->getSatisJsonFilePath());

        // disable packagist by default
        unset(Config::$defaultRepositories['packagist']);

        return $this->composer = Factory::create($io, $this->config, false);
    }

    /**
     * @return IOInterface
     */
    public function getIo()
    {
        if ($this->io === null) {
            $this->io = new BufferIO();
        }

        return $this->io;
    }

    /**
     * @param IOInterface $io
     */
    public function setIo(IOInterface $io)
    {
        $this->io = $io;
    }

    /**
     * @return array
     */
    private function getConfig()
    {
        if ($this->composer === null) {
            $this->getComposer();
        }

        return $this->config;
    }

    /**
     * @return Config
     */
    private function getConfiguration()
    {
        $config = new Config();

        // add dir to the config
        $config->merge([
            'config' => ['home' => $this->resourceManager->getPath('cache/.composer')],
        ]);

        // load global auth file
        $file = new JsonFile($this->resourceManager->getPath('config/satis/auth.json'));
        if ($file->exists()) {
            $config->merge(['config' => $file->read()]);
        }
        $config->setAuthConfigSource(new JsonConfigSource($file, true));

        return $config;
    }

    /**
     * Validates the syntax and the schema of the current config json file
     * according to satis-schema.json rules.
     *
     * @param string $configFile The json file to use
     *
     * @throws ParsingException        if the json file has an invalid syntax
     * @throws JsonValidationException if the json file doesn't match the schema
     *
     * @return bool true on success
     */
    private function check($configFile)
    {
        $content = file_get_contents($configFile);

        $parser = new JsonParser();
        $result = $parser->lint($content);
        if (null === $result) {
            if (defined('JSON_ERROR_UTF8') && JSON_ERROR_UTF8 === json_last_error()) {
                throw new \UnexpectedValueException(sprintf('"%s" is not UTF-8, could not parse as JSON', $configFile));
            }

            $data = json_decode($content);

            $resDir = $this->resourceManager->getPath('root/vendor/composer/satis/res');
            $schemaFile = $resDir . '/satis-schema.json';
            $schema = json_decode(file_get_contents($schemaFile));
            $validator = new Validator();
            $validator->check($data, $schema);

            if (!$validator->isValid()) {
                $errors = [];
                foreach ((array) $validator->getErrors() as $error) {
                    $errors[] = ($error['property'] ? $error['property'] . ' : ' : '') . $error['message'];
                }
                throw new JsonValidationException('The json config file does not match the expected JSON schema', $errors);
            }

            return true;
        }

        throw new ParsingException(sprintf("%s does not contain valid JSON\n%s", $configFile, $result->getMessage()), $result->getDetails());
    }
}
