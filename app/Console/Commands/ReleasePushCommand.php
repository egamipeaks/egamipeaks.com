<?php

namespace App\Console\Commands;

use App\Models\Release;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ReleasePushCommand extends Command
{
    protected $signature = 'release:push {release : Release ID or slug}';

    protected $description = 'Export a release locally, push to production, and import it there';

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

        $identifier = $this->argument('release');

        $release = Release::query()
            ->where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->first();

        if (! $release) {
            $this->error("Release not found: {$identifier}");

            return self::FAILURE;
        }

        $this->info('Step 1/3: Exporting release locally...');

        $exportResult = $this->call('release:export', ['release' => $release->slug]);

        if ($exportResult !== self::SUCCESS) {
            $this->error('Export failed.');

            return self::FAILURE;
        }

        $localFile = storage_path("app/private/exports/release-{$release->slug}.json");
        $remoteFile = "{$remotePath}/storage/app/private/exports/release-{$release->slug}.json";

        $this->info('Step 2/3: Uploading export file to production...');

        $sshOpts = $this->buildSshOptions($key);
        $scpCmd = array_merge(
            ['scp'],
            $sshOpts,
            [$localFile, "{$user}@{$host}:{$remoteFile}"]
        );

        if (! $this->runProcess($scpCmd, 'scp upload')) {
            return self::FAILURE;
        }

        $this->info('Step 3/3: Running release:import on production...');

        $sshCmd = array_merge(
            ['ssh'],
            $this->buildSshOptions($key),
            [
                "{$user}@{$host}",
                "cd {$remotePath}/current && php artisan release:import {$remoteFile} && rm -f {$remoteFile}",
            ]
        );

        if (! $this->runProcess($sshCmd, 'remote import')) {
            return self::FAILURE;
        }

        $this->newLine();
        $this->comment("Release \"{$release->slug}\" successfully pushed to production.");

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
