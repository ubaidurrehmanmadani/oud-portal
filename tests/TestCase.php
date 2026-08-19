<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->withoutMiddleware(VerifyCsrfToken::class);

        config([
            'cache.default' => 'array',
            'mail.default' => 'array',
            'queue.default' => 'sync',
            'session.driver' => 'array',
        ]);
    }
}
