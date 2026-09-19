<?php

namespace RicardoSierra\Minify\Tests;

use RicardoSierra\Minify\Providers\JavaScript;
use RicardoSierra\Minify\Exceptions\FileNotExistException;

class JavaScriptProviderTest extends TestCase
{
    public function test_tag_generation_without_attributes(): void
    {
        $provider = new JavaScript($this->tempDir);
        $tag = $provider->tag('/js/app.js', []);

        $this->assertStringContainsString('<script src="/js/app.js"></script>', $tag);
    }

    public function test_tag_generation_with_custom_attributes(): void
    {
        $provider = new JavaScript($this->tempDir);
        $tag = $provider->tag('/js/app.js', ['async' => true, 'defer' => true]);

        $this->assertStringContainsString('src="/js/app.js"', $tag);
        $this->assertStringContainsString('async', $tag);
        $this->assertStringContainsString('defer', $tag);
    }

    public function test_throws_exception_when_file_does_not_exist(): void
    {
        $this->expectException(FileNotExistException::class);

        $provider = new JavaScript($this->tempDir);
        $provider->add('/js/nonexistent.js');
    }

    public function test_add_and_minify_files(): void
    {
        $file1 = $this->tempDir . '/js/file1.js';
        $file2 = $this->tempDir . '/js/file2.js';
        file_put_contents($file1, "var a = 1; \n var b = 2;");
        file_put_contents($file2, "function test() { return a + b; }");

        $provider = new JavaScript($this->tempDir);
        $provider->add(['/js/file1.js', '/js/file2.js']);
        $this->assertCount(2, $provider);

        $buildPath = '/js/builds/';
        $isMade = $provider->make($buildPath);
        $this->assertTrue($isMade);

        $output = $provider->minify();
        $this->assertFileExists($this->tempDir . '/js/builds/' . $provider->getFilename());
        $this->assertStringContainsString('var a=1;var b=2;function test(){return a+b;}', file_get_contents($this->tempDir . '/js/builds/' . $provider->getFilename()));
    }
}
