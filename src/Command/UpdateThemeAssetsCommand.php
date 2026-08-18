<?php
declare(strict_types=1);

namespace BootstrapTools\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Plugin;
use Cake\Http\Client;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

/**
 * UpdateThemeAssetsCommand
 *
 * Downloads and updates a plugin's assets based on 'extra.theme-config'.
 * Supports Source Code (Tags/Branches) AND Compiled Release Assets.
 */
class UpdateThemeAssetsCommand extends Command
{
    /**
     * Build the command option parser.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Updates theme assets from GitHub.')
            ->addArgument('plugin', [
                'help' => 'Name of the plugin (Optional if --path is used)',
                'required' => false,
            ])
            ->addOption('path', [
                'help' => 'Absolute path to the plugin root (For Standalone mode)',
                'short' => 'p',
            ]);

        return $parser;
    }

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args
     * @param \Cake\Console\ConsoleIo $io
     * @return int
     */
    public function execute(Arguments $args, ConsoleIo $io): int
    {
        // --- STEP 1: Determine Plugin Path ---
        $manualPath = $args->getOption('path');
        $pluginName = $args->getArgument('plugin');
        $pluginPath = '';

        if ($manualPath) {
            $pluginPath = rtrim($manualPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $pluginName = basename($pluginPath);
            $io->info("Standalone mode detected. Path: {$pluginPath}");
        } elseif ($pluginName) {
            try {
                $pluginPath = Plugin::path($pluginName);
            } catch (Exception $e) {
                $io->error("Plugin '{$pluginName}' not found.");

                return self::CODE_ERROR;
            }
        } else {
            $io->error('Error: Specify plugin name or use --path.');

            return self::CODE_ERROR;
        }

        // --- STEP 2: Read Configuration ---
        $composerFile = $pluginPath . 'composer.json';
        if (!file_exists($composerFile)) {
            $io->error('composer.json not found.');

            return self::CODE_ERROR;
        }

        $composerData = json_decode(file_get_contents($composerFile), true);
        $config = $composerData['extra']['theme-config'] ?? null;

        if (!$config) {
            $io->error("Missing 'extra.theme-config' in composer.json");

            return self::CODE_ERROR;
        }

        // --- STEP 3: Validate Parameters ---
        $rawRepo = $config['github_repo'] ?? null;
        $tag = $config['tag'] ?? null;
        $branch = $config['branch'] ?? null;
        $assetName = $config['release_asset'] ?? null; // <--- NUEVO CAMPO

        $sourceDir = $config['source_dir'] ?? 'dist';
        $relativeDestDir = $config['dest_dir'] ?? 'webroot';
        $relativeDestDir = trim($relativeDestDir, '/\\');
        $finalDestPath = $pluginPath . $relativeDestDir;

        if (!$rawRepo) {
            $io->error("Missing 'github_repo' in configuration.");

            return self::CODE_ERROR;
        }

        $repo = $this->parseRepoFromUrl($rawRepo);
        $downloadUrl = '';
        $versionLog = '';

        // --- LÓGICA DE URL ACTUALIZADA ---
        if (!empty($assetName)) {
            // Caso 1: Descargar un ASSET adjunto a un Release (ej: template.zip)
            if (empty($tag)) {
                $io->error("To use 'release_asset', you MUST specify a 'tag'.");

                return self::CODE_ERROR;
            }
            // URL Formato: https://github.com/user/repo/releases/download/v1.0.0/file.zip
            $downloadUrl = "https://github.com/{$repo}/releases/download/{$tag}/{$assetName}";
            $versionLog = "release asset '{$assetName}' for tag '{$tag}'";
        } elseif (!empty($branch)) {
            // Caso 2: Código fuente de una Rama
            $downloadUrl = "https://github.com/{$repo}/archive/refs/heads/{$branch}.zip";
            $versionLog = "branch '{$branch}' source code";
        } elseif (!empty($tag)) {
            // Caso 3: Código fuente de un Tag
            $downloadUrl = "https://github.com/{$repo}/archive/refs/tags/{$tag}.zip";
            $versionLog = "tag '{$tag}' source code";
        } else {
            $io->error("You must define 'tag', 'branch' or 'release_asset' in composer.json.");

            return self::CODE_ERROR;
        }

        // --- STEP 4: Download ---
        $io->out("Repo: {$repo}");
        $io->out("Downloading {$versionLog}...");

        $tmpDir = sys_get_temp_dir();
        $tmpZip = $tmpDir . DIRECTORY_SEPARATOR . 'theme_' . uniqid() . '.zip';
        $extractPath = $tmpDir . DIRECTORY_SEPARATOR . 'extract_' . uniqid() . DIRECTORY_SEPARATOR;

        try {
            $http = $this->createHttpClient();

            $response = $http->get($downloadUrl);

            if (!$response->isOk()) {
                throw new Exception("Download failed (HTTP {$response->getStatusCode()}). URL: {$downloadUrl}");
            }

            $body = $response->getStringBody();
            if (empty($body)) {
                throw new Exception('Empty response from GitHub.');
            }

            file_put_contents($tmpZip, $body);

            if (filesize($tmpZip) === 0) {
                throw new Exception('Downloaded file is empty.');
            }

            // --- STEP 5: Install ---
            $io->out("Extracting to: {$relativeDestDir}...");
            $this->processZipAndInstall($tmpZip, $extractPath, $sourceDir, $finalDestPath);
            $io->success("Assets for {$pluginName} successfully updated!");
        } catch (Exception $e) {
            $io->error('FAILED: ' . $e->getMessage());
            $this->cleanup($tmpZip, $extractPath);

            return self::CODE_ERROR;
        }

        $this->cleanup($tmpZip, $extractPath);

        return static::CODE_SUCCESS;
    }

    /**
     * Build the HTTP client used to download the theme archive.
     *
     * @return \Cake\Http\Client
     */
    protected function createHttpClient(): Client
    {
        return new Client([
            'headers' => ['User-Agent' => 'CakePHP-Updater/1.0'],
            'redirect' => true,
            'timeout' => 120, // Releases can be large
        ]);
    }

    /**
     * Extract the "owner/repo" pair from a GitHub URL or short form.
     *
     * @param string $input
     * @return string
     */
    private function parseRepoFromUrl(string $input): string
    {
        $clean = rtrim(trim($input), '/');
        if (str_contains($clean, 'github.com')) {
            $path = parse_url($clean, PHP_URL_PATH);
            $clean = trim($path, '/');
        }

        return preg_replace('/\.git$/', '', $clean);
    }

    /**
     * Extract, resolve the source directory and copy files into the destination.
     *
     * @param string $zipFile
     * @param string $extractPath
     * @param string $sourceDir
     * @param string $destPath
     * @return void
     */
    private function processZipAndInstall(string $zipFile, string $extractPath, string $sourceDir, string $destPath): void
    {
        $zip = new ZipArchive();
        if ($zip->open($zipFile) !== true) {
            throw new Exception('Could not open ZIP.');
        }
        $zip->extractTo($extractPath);
        $zip->close();

        // Detectar si la carpeta fuente está en la raíz o dentro de un subdirectorio
        $rootItems = scandir($extractPath);
        $candidateRoot = null;

        // 1. Buscamos primero si la carpeta deseada (ej: dist) ya existe en la raiz extraída
        // Esto pasa a menudo en release assets que no tienen carpeta contenedora
        if (is_dir($extractPath . $sourceDir)) {
            $sourceFull = $extractPath . $sourceDir;
        } else {
            // 2. Si no, buscamos una carpeta contenedora única (comportamiento estándar de GitHub Source Zips)
            foreach ($rootItems as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                if (is_dir($extractPath . $item)) {
                    $candidateRoot = $item;
                    break;
                }
            }
            if ($candidateRoot && is_dir($extractPath . $candidateRoot . DIRECTORY_SEPARATOR . $sourceDir)) {
                $sourceFull = $extractPath . $candidateRoot . DIRECTORY_SEPARATOR . $sourceDir;
            } else {
                 // Último intento: Quizás el usuario puso "." como source_dir y es un zip plano
                if ($sourceDir === '.' || $sourceDir === './') {
                    $sourceFull = $extractPath; // Copiar todo el contenido del zip
                } else {
                    throw new Exception("Source directory '{$sourceDir}' not found in ZIP.");
                }
            }
        }

        $this->copyRecursive($sourceFull, $destPath);
    }

    /**
     * Recursively copy files from a source directory into a destination.
     *
     * @param string $source
     * @param string $dest
     * @return void
     */
    private function copyRecursive(string $source, string $dest): void
    {
        $source = rtrim($source, DIRECTORY_SEPARATOR);
        if (!is_dir($dest)) {
            if (!mkdir($dest, 0755, true) && !is_dir($dest)) {
                 throw new Exception(sprintf('Directory "%s" was not created', $dest));
            }
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
        );

        /** @var \SplFileInfo $item */
        foreach ($iterator as $item) {
            $relativePath = substr($item->getPathname(), strlen($source));
            $destPath = $dest . $relativePath;

            if ($item->isDir()) {
                if (!is_dir($destPath)) {
                    mkdir($destPath, 0755, true);
                }
            } else {
                copy($item->getPathname(), $destPath);
            }
        }
    }

    /**
     * Remove temporary files and extracted folders.
     *
     * @param string $file
     * @param string $folder
     * @return void
     */
    private function cleanup(string $file, string $folder): void
    {
        if (file_exists($file)) {
            unlink($file);
        }
        if (is_dir($folder)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folder, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );
            foreach ($files as $fileinfo) {
                $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
                @$todo($fileinfo->getRealPath());
            }
            rmdir($folder);
        }
    }
}
