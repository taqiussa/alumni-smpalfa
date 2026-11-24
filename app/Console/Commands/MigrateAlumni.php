<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

#[Signature('alumni:migrate {--pretend : Show SQL without applying it}')]
#[Description('Run only verified alumni-prefixed migrations.')]
class MigrateAlumni extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach (glob(database_path('migrations/alumni/*.php')) ?: [] as $migration) {
            if (! str_contains((string) file_get_contents($migration), "'alumni_")) {
                $this->error('Rejected migration outside alumni namespace: '.basename($migration));

                return self::FAILURE;
            }
        }

        return Artisan::call('migrate', ['--path' => 'database/migrations/alumni', '--force' => false, '--pretend' => $this->option('pretend')]);
    }
}
