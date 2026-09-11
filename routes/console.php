<?php

use Database\Seeders\ReferenceWorkspaceSeeder;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('oud:import-reference-content', function () {
    $this->call('db:seed', ['--class' => ReferenceWorkspaceSeeder::class, '--force' => true]);
    $this->info('Reference properties, 60 monthly reports, listings and landlord assignments imported. Existing records and decisions retained.');
})->purpose('Import the complete supplied landlord reference content');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
