<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('alumni:migrate {--force : Force the operation to run when in production} {--pretend : Show SQL without applying it}')]
#[Description('Run only verified alumni-prefixed migrations.')]
class MigrateAlumni extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $migrations = glob(database_path('migrations/*.php')) ?: [];

        if ($migrations === []) {
            $this->error('No alumni migrations were found.');

            return self::FAILURE;
        }

        foreach ($migrations as $migration) {
            if (! str_contains(basename($migration), '_alumni_') || ! str_contains((string) file_get_contents($migration), "'alumni_")) {
                $this->error('Rejected migration outside alumni namespace: '.basename($migration));

                return self::FAILURE;
            }
        }

        return $this->call('migrate', [
            '--path' => database_path('migrations'),
            '--realpath' => true,
            '--force' => $this->option('force'),
            '--pretend' => $this->option('pretend'),
        ]);
    }
}
