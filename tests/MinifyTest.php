<?php

namespace RicardoSierra\Minify\Tests;

use RicardoSierra\Minify\Minify;
use RicardoSierra\Minify\Facades\MinifyFacade;
use RicardoSierra\Minify\Exceptions\InvalidArgumentException;

class MinifyTest extends TestCase
{
    protected array $validConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->usePublicPath($this->tempDir);

        $this->validConfig = [
            'css_build_path' => '/css/builds/',
            'css_url_path' => '/css/builds/',
            'js_build_path' => '/js/builds/',
            'js_url_path' => '/js/builds/',
            'ignore_environments' => ['local'],
            'base_url' => '//example.com',
            'reverse_sort' => true,
            'disable_mtime' => false,
            'hash_salt' => '',
        ];
    }

    public function test_throws_exception_when_config_is_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Minify(['invalid' => 'config'], 'production');
    }

    public function test_javascript_minification(): void
    {
        $file = $this->tempDir . '/js/test.js';
        file_put_contents($file, "var x = 10; \n var y = 20;");

        $minify = new Minify($this->validConfig, 'production');
        $result = (string) $minify->javascript('/js/test.js');

        $this->assertStringContainsString('<script src="/js/builds/', $result);
        $this->assertStringContainsString('.js"></script>', $result);
    }

    public function test_stylesheet_minification(): void
    {
        $file = $this->tempDir . '/css/test.css';
        file_put_contents($file, "div { background-color: #000000; }");

        $minify = new Minify($this->validConfig, 'production');
        $result = (string) $minify->stylesheet('/css/test.css');

        $this->assertStringContainsString('<link href="/css/builds/', $result);
        $this->assertStringContainsString('.css" rel="stylesheet">', $result);
    }

    public function test_only_url_option(): void
    {
        $file = $this->tempDir . '/js/test.js';
        file_put_contents($file, "var z = 30;");

        $minify = new Minify($this->validConfig, 'production');
        $url = (string) $minify->javascript('/js/test.js')->onlyUrl();

        $this->assertStringStartsWith('/js/builds/', $url);
        $this->assertStringEndsWith('.js', $url);
        $this->assertStringNotContainsString('<script', $url);
    }

    public function test_with_full_url_option(): void
    {
        $file = $this->tempDir . '/css/test.css';
        file_put_contents($file, "span { color: red; }");

        $minify = new Minify($this->validConfig, 'production');
        $url = (string) $minify->stylesheet('/css/test.css')->onlyUrl()->withFullUrl();

        $this->assertStringStartsWith('//example.com/css/builds/', $url);
    }

    public function test_ignore_environment_returns_unminified_tag(): void
    {
        $file = $this->tempDir . '/js/dev.js';
        file_put_contents($file, "console.log('dev');");

        $minify = new Minify($this->validConfig, 'local');
        $result = (string) $minify->javascript('/js/dev.js');

        $this->assertStringContainsString('<script src="/js/dev.js"></script>', $result);
    }

    public function test_facade_resolves_and_runs(): void
    {
        $file = $this->tempDir . '/js/facade.js';
        file_put_contents($file, "var a = 1;");

        $tag = (string) MinifyFacade::javascript('/js/facade.js');
        $this->assertStringContainsString('<script src="/js/builds/', $tag);
    }
}
