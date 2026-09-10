<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('oud:sync-design-assets', function () {
    $source = base_path('OUD_project');
    if (! is_file($source.'/styles.css') || ! is_dir($source.'/assets')) {
        $this->error('OUD_project/styles.css and OUD_project/assets are required.');

        return 1;
    }
    File::ensureDirectoryExists(public_path('oud'));
    File::copy($source.'/styles.css', public_path('oud/styles.css'));
    File::copyDirectory($source.'/assets', public_path('oud/assets'));
    $this->info('OUD design assets synchronized. Review Blade templates and application.css for structural changes.');
})->purpose('Copy supplied OUD styles and assets without importing demo JavaScript');
