<?php
declare(strict_types=1);

namespace BootstrapTools\Test\TestCase\Command;

use BootstrapTools\Command\UpdateThemeAssetsCommand;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\TestSuite\StubConsoleOutput;
use Cake\TestSuite\TestCase;
use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionMethod;
use ZipArchive;

/**
 * UpdateThemeAssetsCommandTest
 */
class UpdateThemeAssetsCommandTest extends TestCase
{
    private UpdateThemeAssetsCommand $command;

    private StubConsoleOutput $output;

    private ConsoleIo $io;

    private string $workDir;

    public function setUp(): void
    {
        parent::setUp();
        $this->command = new UpdateThemeAssetsCommand();
        $this->output = new StubConsoleOutput();
        $this->io = new ConsoleIo($this->output, $this->output);
        $this->workDir = sys_get_temp_dir() . '/bst_theme_' . uniqid();
        mkdir($this->workDir, 0777, true);
    }

    public function tearDown(): void
    {
        $this->removeDir($this->workDir);
        parent::tearDown();
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($it as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($dir);
    }

    private function args(array $args = [], array $options = []): Arguments
    {
        return new Arguments($args, $options, ['plugin']);
    }

    private function writeComposer(array $extra): void
    {
        file_put_contents($this->workDir . '/composer.json', json_encode([
            'name' => 'vendor/theme',
            'extra' => ['theme-config' => $extra],
        ]));
    }

    private function buildZip(string ...$entries): string
    {
        $tmp = $this->workDir . '/zip.zip';
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($entries as $entry) {
            $zip->addFromString($entry, 'content-of-' . $entry);
        }
        $zip->close();

        return (string)file_get_contents($tmp);
    }

    public function testExecuteWithoutPluginNorPathErrors(): void
    {
        $result = $this->command->execute($this->args(), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString('Specify plugin name or use --path', $this->output->output());
    }

    public function testExecuteWithUnknownPluginErrors(): void
    {
        $result = $this->command->execute($this->args(['UnknownPlugin']), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString("Plugin 'UnknownPlugin' not found", $this->output->output());
    }

    public function testExecutePathWithoutComposerJsonErrors(): void
    {
        $result = $this->command->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString('composer.json not found', $this->output->output());
    }

    public function testExecuteWithoutThemeConfigErrors(): void
    {
        file_put_contents($this->workDir . '/composer.json', '{"name":"vendor/theme"}');
        $result = $this->command->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString("Missing 'extra.theme-config'", $this->output->output());
    }

    public function testExecuteWithoutGithubRepoErrors(): void
    {
        $this->writeComposer(['tag' => 'v1.0.0']);
        $result = $this->command->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString("Missing 'github_repo'", $this->output->output());
    }

    public function testExecuteReleaseAssetWithoutTagErrors(): void
    {
        $this->writeComposer(['github_repo' => 'user/repo', 'release_asset' => 'theme.zip']);
        $result = $this->command->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString("you MUST specify a 'tag'", $this->output->output());
    }

    public function testExecuteWithoutTagBranchOrAssetErrors(): void
    {
        $this->writeComposer(['github_repo' => 'user/repo']);
        $result = $this->command->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_ERROR, $result);
        $this->assertStringContainsString("define 'tag', 'branch' or 'release_asset'", $this->output->output());
    }

    public function testParseRepoFromUrl(): void
    {
        $method = new ReflectionMethod(UpdateThemeAssetsCommand::class, 'parseRepoFromUrl');

        $this->assertSame('user/repo', $method->invoke($this->command, 'https://github.com/user/repo'));
        $this->assertSame('user/repo', $method->invoke($this->command, 'https://github.com/user/repo.git'));
        $this->assertSame('user/repo', $method->invoke($this->command, 'https://github.com/user/repo/'));
        $this->assertSame('user/repo', $method->invoke($this->command, 'user/repo'));
    }

    public function testCopyRecursiveCreatesDestAndFiles(): void
    {
        $source = $this->workDir . '/src';
        $dest = $this->workDir . '/out';
        mkdir($source . '/sub', 0777, true);
        file_put_contents($source . '/a.txt', 'a');
        file_put_contents($source . '/sub/b.txt', 'b');

        $method = new ReflectionMethod(UpdateThemeAssetsCommand::class, 'copyRecursive');
        $method->invoke($this->command, $source, $dest);

        $this->assertFileExists($dest . '/a.txt');
        $this->assertFileExists($dest . '/sub/b.txt');
        $this->assertSame('b', file_get_contents($dest . '/sub/b.txt'));
    }

    public function testProcessZipAndInstallWithSourceDir(): void
    {
        $zipFile = $this->workDir . '/src.zip';
        file_put_contents($zipFile, $this->buildZip('dist/style.css'));

        $dest = $this->workDir . '/webroot';
        $method = new ReflectionMethod(UpdateThemeAssetsCommand::class, 'processZipAndInstall');
        $method->invoke(
            $this->command,
            $zipFile,
            $this->workDir . '/extract' . DIRECTORY_SEPARATOR,
            'dist',
            $dest,
        );

        $this->assertFileExists($dest . '/style.css');
    }

    public function testProcessZipAndInstallWithContainerFolder(): void
    {
        $zipFile = $this->workDir . '/src.zip';
        file_put_contents($zipFile, $this->buildZip('theme-main/dist/style.css'));

        $dest = $this->workDir . '/webroot';
        $method = new ReflectionMethod(UpdateThemeAssetsCommand::class, 'processZipAndInstall');
        $method->invoke(
            $this->command,
            $zipFile,
            $this->workDir . '/extract' . DIRECTORY_SEPARATOR,
            'dist',
            $dest,
        );

        $this->assertFileExists($dest . '/style.css');
    }

    public function testProcessZipAndInstallMissingSourceDirThrows(): void
    {
        $zipFile = $this->workDir . '/src.zip';
        file_put_contents($zipFile, $this->buildZip('theme-main/other/file.css'));

        $dest = $this->workDir . '/webroot';
        $method = new ReflectionMethod(UpdateThemeAssetsCommand::class, 'processZipAndInstall');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Source directory 'dist' not found");
        $method->invoke(
            $this->command,
            $zipFile,
            $this->workDir . '/extract' . DIRECTORY_SEPARATOR,
            'dist',
            $dest,
        );
    }

    public function testExecuteHappyPathWithBranch(): void
    {
        $this->writeComposer([
            'github_repo' => 'user/repo',
            'branch' => 'main',
            'source_dir' => 'dist',
            'dest_dir' => 'webroot',
        ]);

        $fake = new FakeThemeAssetsCommand();
        $fake->zipContent = $this->buildZip('main/dist/theme.css');

        $result = $fake->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_SUCCESS, $result);
        $this->assertFileExists($this->workDir . '/webroot/theme.css');
    }

    public function testExecuteHappyPathWithReleaseAsset(): void
    {
        $this->writeComposer([
            'github_repo' => 'user/repo',
            'tag' => 'v1.0.0',
            'release_asset' => 'theme.zip',
            'source_dir' => '',
            'dest_dir' => 'webroot',
        ]);

        $fake = new FakeThemeAssetsCommand();
        // release assets suelen traer la carpeta contenedora con source '.'.
        $fake->zipContent = $this->buildZip('theme/asset/style.css');

        $result = $fake->execute($this->args([], ['path' => $this->workDir]), $this->io);

        $this->assertSame(Command::CODE_SUCCESS, $result);
    }
}
