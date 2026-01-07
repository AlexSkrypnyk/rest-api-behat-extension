<?php

declare(strict_types=1);

use atoum\atoum\asserter\generator;

use atoum\atoum\asserter;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * phpcs:disable Generic.Files.LineLength.TooLong
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * @see https://github.com/Behat/WebApiExtension/blob/master/features/bootstrap/FeatureContext.php
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    private ?string $phpBin = null;

    private ?Process $process = null;

    private ?string $workingDir = null;

    private readonly generator $generator;

    public function __construct()
    {
        $this->generator = new generator();
    }

    /**
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders(): void
    {
        $dir = self::workingDir();

        if (is_dir($dir)) {
            self::clearDirectory($dir);
        }
    }

    /**
     * @BeforeScenario
     */
    public function prepareScenario(): void
    {
        $dir = self::workingDir() . DIRECTORY_SEPARATOR . (md5((string)(microtime(true) * random_int(0, 10000))));
        mkdir($dir . '/features/bootstrap', 0777, true);

        $phpFinder = new PhpExecutableFinder();

        if (false === $php = $phpFinder->find()) {
            throw new \RuntimeException('Unable to find the PHP executable.');
        }

        $this->workingDir = $dir;
        $this->phpBin = $php;
    }

    /**
     * @Given /^a file named "(?P<filename>[^"]*)" with:$/
     */
    public function aFileNamedWith(string $filename, PyStringNode $pyStringNode): void
    {
        $content = strtr((string) $pyStringNode, ["'''" => '"""']);
        $this->createFile($this->workingDir . '/' . $filename, $content);
    }

    /**
     * @When /^I run behat "(?P<arguments>[^"]*)"$/
     */
    public function iRunBehat($arguments): void
    {
        $argumentsString = strtr($arguments, ["'" => '"']);

        $commandLine = sprintf(
            '%s %s %s %s',
            $this->phpBin,
            escapeshellarg(BEHAT_BIN_PATH),
            $argumentsString,
            '--no-colors'
        );

        $this->process = Process::fromShellCommandline($commandLine);
        $this->process->setWorkingDirectory($this->workingDir);
        $this->process->start();
        $this->process->wait();
    }

    /**
     * @Then /^it should (fail|pass) with:$/
     */
    public function itShouldTerminateWithStatusAndContent($exitStatus, PyStringNode $pyStringNode): void
    {
        if ('fail' === $exitStatus) {
            $this->generator->integer($this->getExitCode())->isEqualTo(1);
        } elseif ('pass' === $exitStatus) {
            $this->generator->integer($this->getExitCode())->isEqualTo(0);
        } else {
            throw new \LogicException('Accepts only "fail" or "pass"');
        }

        $stringAsserterFunc = class_exists('mageekguy\\atoum\\asserters\\phpString') ? 'phpString' : 'string';
        $this->generator->$stringAsserterFunc($this->getOutput())->contains((string) $pyStringNode);
    }

    private function getExitCode(): ?int
    {
        return $this->process->getExitCode();
    }

    private function getOutput(): string
    {
        $output = $this->process->getErrorOutput() . $this->process->getOutput();

        // Normalize the line endings in the output
        if ("\n" !== PHP_EOL) {
            $output = str_replace(PHP_EOL, "\n", $output);
        }

        return trim((string) preg_replace("/ +$/m", '', $output));
    }

    private function createFile(string $filename, string $content): void
    {
        $path = dirname($filename);

        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }

        file_put_contents($filename, $content);
    }

    public static function workingDir(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'json-api-behat';
    }

    private static function clearDirectory(string $path): void
    {
        $files = scandir($path);
        array_shift($files);
        array_shift($files);

        foreach ($files as $file) {
            $file = $path . DIRECTORY_SEPARATOR . $file;
            if (is_dir($file)) {
                self::clearDirectory($file);
            } else {
                unlink($file);
            }
        }

        rmdir($path);
    }
}
