<?php

declare(strict_types=1);

namespace Asseco\JsonAuthorization\App\Console\Commands;

use Asseco\JsonAuthorization\App\AuthorizableModel;
use Illuminate\Console\Command;

class SyncAuthorizableModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asseco:sync-authorizable-models';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync authorizable models in DB by comparing current DB state with models having Authorizable trait.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        AuthorizableModel::reCache();

        $this->info('Sync completed.');
    }
}
