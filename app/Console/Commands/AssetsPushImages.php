<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class AssetsPushImages extends Command
{
    protected $signature = 'assets:push-images
        {--dry-run : Run the remote import in dry-run mode}';

    protected $description = 'Export local image asset records, scp to production, and update prod Asset rows';

    public function handle(): int
    {
        $host = config('services.prod_ssh.host');
        $user = config('services.prod_ssh.user');
        $key = config('services.prod_ssh.key');
        $remotePath = config('services.prod_ssh.path');

        if (! $host || ! $user || ! $remotePath) {
            $this->error('Missing SSH config. Set PROD_SSH_HOST, PROD_SSH_USER, and PROD_SSH_PATH in .env');

            return self::FAILURE;
        }

        $this->info('Step 1/3: Exporting local image asset records...');

        if ($this->call('assets:export-images') !== self::SUCCESS) {
            $this->error('Export failed.');

            return self::FAILURE;
        }

        $localFile = storage_path('app/private/exports/optimized-assets.json');
        $remoteFile = "{$remotePath}/storage/app/private/exports/optimized-assets.json";

        $this->info('Step 2/3: Uploading export file to production...');

        if (! $this->runProcess(array_merge(['scp'], $this->buildSshOptions($key), [$localFile, "{$user}@{$host}:{$remoteFile}"]), 'scp upload')) {
            return self::FAILURE;
        }

        $this->info('Step 3/3: Running assets:import-image-records on production...');

        $importCmd = "php artisan assets:import-image-records {$remoteFile}";

        if ($this->option('dry-run')) {
            $importCmd .= ' --dry-run';
        }

        $remoteShell = "cd {$remotePath}/current && {$importCmd} && rm -f {$remoteFile}";

        if (! $this->runProcess(array_merge(['ssh'], $this->buildSshOptions($key), ["{$user}@{$host}", $remoteShell]), 'remote import')) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->comment('Image asset records pushed to production.');

        return self::SUCCESS;
    }

    /** @return array<int, string> */
    private function buildSshOptions(?string $key): array
    {
        $opts = ['-o', 'StrictHostKeyChecking=no'];

        if ($key) {
            $opts[] = '-i';
            $opts[] = $key;
        }

        return $opts;
    }

    /** @param array<int, string> $cmd */
    private function runProcess(array $cmd, string $label): bool
    {
        $process = new Process($cmd);
        $process->setTimeout(120);
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error("Failed during {$label}: ".$process->getErrorOutput());

            return false;
        }

        return true;
    }
}
