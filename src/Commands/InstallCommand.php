<?php

namespace Ijpatricio\DockerForForge\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class InstallCommand extends Command
{
    protected array $data;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dockerEnv:install {appName?} {appDomain?} {vendorName?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Installs scaffolding.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $confirm = $this->askWithCompletion('Please save your changes before proceeding. Are you sure you want to continue?', ['yes', 'no'], 'yes');
        if ($confirm !== 'yes') {
            $this->info('Stopped.');
            return 0;
        }

        $this->data = [
            'webapp_name' => $this->argument('appName') ?? $this->ask('App name?'),
            'webapp_domain' => $this->argument('appDomain') ?? $this->ask('App Domain?'),
            'webapp_vendor' => $this->argument('vendorName') ?? $this->ask('Vendor name?'),
        ];

        $this->_processDockerEnvFiles();
        $this->_dockerComposeFile();

        return 0;
    }

    protected function _dockerComposeFile()
    {
        $originalContent = File::get(__DIR__ . '/../../stubs/production-single-container/docker-compose.prod.yml');

        $newContent = Blade::render($originalContent, $this->data);

        File::put(base_path('docker-compose.prod.yml'), $newContent);
        $this->info('Processed docker-compose file.');
    }

    protected function _processDockerEnvFiles()
    {
        $relativePath = 'stubs/production-single-container/docker';
        $destinationBasePath = '/docker/production';

        $dockerEnvFiles = File::allFiles(__DIR__ . '/../../' . $relativePath);
        foreach ($dockerEnvFiles as $dockerEnvFile) {
            $this->_renderFileToFolder($dockerEnvFile, $destinationBasePath);
        }
    }

    protected function _renderFileToFolder(string|SplFileInfo $file, $folder)
    {
        $filename = $file->getFilename();

        $destination = base_path($folder);
        File::ensureDirectoryExists($destination);
        $originalContent = File::get($file->getRealPath());
        $newContent = Blade::render($originalContent, $this->data);

        $append = str($filename)->start('/');
        File::put($destination . $append, $newContent);
        $this->info('Processed ' . $filename . '.');
    }


}
