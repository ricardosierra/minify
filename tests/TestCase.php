<?php

namespace RicardoSierra\Minify\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use RicardoSierra\Minify\MinifyServiceProvider;
use RicardoSierra\Minify\Facades\MinifyFacade;
use Illuminate\Filesystem\Filesystem;

abstract class TestCase extends OrchestraTestCase
{
    protected string $tempDir;

    protected function getPackageProviders($app): array
    {
        return [
            MinifyServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Minify' => MinifyFacade::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $this->tempDir = sys_get_temp_dir() . '/minify_test_' . uniqid();
        mkdir($this->tempDir . '/css/builds', 0777, true);
        mkdir($this->tempDir . '/js/builds', 0777, true);

        $app['config']->set('minify.config.css_build_path', '/css/builds/');
        $app['config']->set('minify.config.css_url_path', '/css/builds/');
        $app['config']->set('minify.config.js_build_path', '/js/builds/');
        $app['config']->set('minify.config.js_url_path', '/js/builds/');
        $app['config']->set('minify.config.ignore_environments', ['local']);
        $app['config']->set('minify.config.base_url', '//example.com');
        $app['config']->set('minify.config.reverse_sort', true);
        $app['config']->set('minify.config.disable_mtime', false);
        $app['config']->set('minify.config.hash_salt', '');
    }

    protected function tearDown(): void
    {
        if (isset($this->tempDir) && is_dir($this->tempDir)) {
            (new Filesystem())->deleteDirectory($this->tempDir);
        }

        parent::tearDown();
    }
}
