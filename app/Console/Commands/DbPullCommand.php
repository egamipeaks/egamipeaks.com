<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class DbPullCommand extends Command
{
    protected $signature = 'db:pull';

    protected $description = 'Pull the production SQLite database down to your local environment';

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

        if (! $this->confirm('This will overwrite your local database. Continue?', false)) {
            $this->info('Aborted.');

            return self::FAILURE;
        }

        $remoteDb = "{$user}@{$host}:{$remotePath}/storage/database.sqlite";
        $localDb = database_path('database.sqlite');

        $this->info("Pulling database from {$host}...");

        $sshOpts = $this->buildSshOptions($key);
        $scpCmd = array_merge(
            ['scp'],
            $sshOpts,
            [$remoteDb, $localDb]
        );

        $process = new Process($scpCmd);
        $process->setTimeout(120);
        $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error('Failed to pull database: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        $this->comment('Database pulled successfully.');

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
}
