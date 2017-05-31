<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Bolt\Configuration\PathResolver;
use Bolt\Extension\Bolt\MarketPlace\Location;
use Bolt\Extension\Bolt\MarketPlace\Output\BufferedOutputArray;
use Bolt\Extension\Bolt\MarketPlace\Storage\Entity;
use Bolt\Storage\EntityManager;
use Composer\Composer;
use Composer\Config;
use Composer\Config\JsonConfigSource;
use Composer\Factory;
use Composer\IO\BufferIO;
use Composer\Json\JsonFile;
use Composer\Json\JsonValidationException;
use Composer\Package\PackageInterface;
use Composer\Satis\Builder\PackagesBuilder;
use Composer\Satis\Builder\WebBuilder;
use Composer\Satis\PackageSelection\PackageSelection;
use JsonSchema\Validator;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;
use Symfony\Component\Console\Helper\Table;
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
    /** @var PathResolver */
    protected $pathResolver;
    /** @var BufferIO */
    protected $composerOutput;
    /** @var OutputInterface */
    protected $consoleOutput;
    /** @var OutputInterface */
    protected $consoleOutputBuffer;
    /** @var Composer */
    protected $composer;
    /** @var array */
    protected $satisJsonData;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param PathResolver  $pathResolver
     */
    public function __construct(EntityManager $em, PathResolver $pathResolver)
    {
        $this->em = $em;
        $this->pathResolver = $pathResolver;
        $this->consoleOutputBuffer = new BufferedOutputArray();
        $this->composerOutput = new BufferIO();
    }

    /**
     * @param string $packageName
     *
     * @return PackageInterface[]
     */
    public function build($packageName)
    {
        $skipErrors = true;
        $htmlView = true;
        $lock = $this->getLock();
        $packageSelection = $this->getSatisPackageSelection($packageName, $skipErrors);

        $this->consoleOutput->writeln('<info>Searching repositories for valid versions …</info>');
        $packages = $packageSelection->select($this->getComposer(), true);

        // In case of an active filter we need to load the dumped packages.json
        // and merge the updated packages in
        if ($packageSelection->hasFilterForPackages() || $packageSelection->hasRepositoryFilter()) {
            $oldPackages = $packageSelection->load();
            $packages += $oldPackages;
            ksort($packages);
        }
        $this->renderPackageSelection($packages);

        // Write out JSON
        $this->consoleOutput->writeln(sprintf('<info>Outputting Satis JSON data to %s </info>', $this->getSatisWebPath()));
        $packagesBuilder = new PackagesBuilder($this->consoleOutputBuffer, $this->getSatisWebPath(), $this->getSatisJsonData(), $skipErrors);
        $packagesBuilder->dump($packages);

        if ($htmlView) {
            $this->dumpPackages($packages, $skipErrors);
        }

        $lock->release();

        return $packages;
    }

    /**
     * @param string|null $packageName
     * @param bool        $skipErrors
     *
     * @return PackageSelection
     */
    private function getSatisPackageSelection($packageName, $skipErrors)
    {
        $packageSelection = new PackageSelection($this->consoleOutputBuffer, $this->getSatisWebPath(), $this->getSatisJsonData(), $skipErrors);
        if ($packageName === null) {
            return $packageSelection;
        }
        $packageEntity = $this->em->getRepository(Entity\Package::class)->findOneBy(['name' => $packageName]);
        if ($packageEntity) {
            $packageSelection->setRepositoryFilter($packageEntity->getSource());
        }

        return $packageSelection;
    }

    /**
     * @param \Composer\Package\CompletePackage[] $packages
     */
    private function renderPackageSelection(array $packages)
    {
        $result = [];
        foreach ($packages as $package) {
            $name = $package->getPrettyName();
            $result[$name][] = $package->getPrettyVersion();
        }
        $table = new Table($this->consoleOutput);
        foreach ($result as $name => $versions) {
            $chunks = array_chunk($versions, 8);
            foreach ($chunks as $chunk) {
                $table->addRow([$name, implode(', ', $chunk)]);
            }
        }
        $table->render();
    }

    /**
     * @param bool $skipErrors
     *
     * @return PackageInterface[]
     */
    public function getBuiltPackages($skipErrors = false)
    {
        $this->consoleOutput->writeln('<info>Fetching previously built package data…</info>');
        $packageSelection = new PackageSelection($this->consoleOutputBuffer, $this->getSatisWebPath(), $this->getSatisJsonData(), $skipErrors);

        return $packageSelection->load();
    }

    /**
     * @param array $packages
     * @param bool  $skipErrors
     */
    public function dumpPackages(array $packages, $skipErrors = false)
    {
        $this->consoleOutput->writeln('<info>Writing out web files…</info>');
        $web = new WebBuilder($this->consoleOutputBuffer, $this->getSatisWebPath(), $this->getSatisJsonData(), $skipErrors);
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
        return $this->pathResolver->resolve('%config%/satis/satis.json');
    }

    /**
     * @return string
     */
    public function getSatisWebPath()
    {
        return $this->pathResolver->resolve('%web%/satis');
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
            'output-dir'    => $this->pathResolver->resolve('%web%/satis'),
            'twig-template' => $this->pathResolver->resolve('%theme%/satis/satis-index.twig'),
        ];
    }

    /**
     * @param array $satisArray
     *
     * @return array
     */
    protected function getSatisExtraRepositories(array $satisArray)
    {
        $repoFile = $this->pathResolver->resolve('%config%/satis/repos.yml');
        $repoConfig = Yaml::parse(file_get_contents($repoFile));
        foreach (array_keys($repoConfig) as $type) {
            foreach ($repoConfig[$type] as $url) {
                $satisArray['repositories'][] = ['type' => $type, 'url' => $url];
            }
        }

        return $satisArray;
    }

    /**
     * Get an instance of the Composer API.
     *
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

        // load auth.json authentication information and pass it to the io interface
        $this->composerOutput->loadConfiguration($this->getConfiguration());

        $file = new JsonFile($this->getSatisJsonFilePath());
        if (!$file->exists()) {
            throw new FileNotFoundException(sprintf('File not found: %s', $this->getSatisJsonFilePath()));
        }

        $this->satisJsonData = $file->read();
        $this->check($this->getSatisJsonFilePath());

        // Disable packagist.org by default
        unset(Config::$defaultRepositories['packagist.org']);

        return $this->composer = Factory::create($this->composerOutput, $this->satisJsonData, false);
    }

    /**
     * @return OutputInterface
     */
    public function getConsoleOutput()
    {
        if ($this->consoleOutput === null) {
            throw new \RuntimeException(sprintf('Output interface not set!'));
        }

        return $this->consoleOutput;
    }

    /**
     * @param OutputInterface $output
     */
    public function setConsoleOutput(OutputInterface $output)
    {
        $this->consoleOutput = $output;
    }

    /**
     * @return array
     */
    private function getSatisJsonData()
    {
        if ($this->composer === null) {
            $this->getComposer();
        }

        return $this->satisJsonData;
    }

    /**
     * @return Config
     */
    private function getConfiguration()
    {
        $config = new Config();

        // add dir to the config
        $config->merge([
            'config' => ['home' => $this->pathResolver->resolve('composer')],
        ]);

        // load global auth file
        $file = new JsonFile($this->pathResolver->resolve('%config%/satis/auth.json'));
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

            $resDir = $this->pathResolver->resolve('%root%/vendor/composer/satis/res');
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

    /**
     * Get write lock on Satis directory.
     *
     * @return LockHandler
     */
    private function getLock()
    {
        set_time_limit(3600);
        $lockDir = $this->pathResolver->resolve(Location::SATIS_LOCK);
        $lock = new LockHandler('satis.full.build', $lockDir);

        $this->consoleOutput->writeln(sprintf('<info>Acquiring lock on build directory: %s </info>', $lockDir));
        if ($lock->lock(true)) {
            return $lock;
        }

        throw new \RuntimeException('Unable to aquire build directory lock.');
    }
}
