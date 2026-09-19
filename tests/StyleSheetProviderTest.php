<?php

namespace RicardoSierra\Minify\Tests;

use RicardoSierra\Minify\Providers\StyleSheet;
use RicardoSierra\Minify\Exceptions\FileNotExistException;

class StyleSheetProviderTest extends TestCase
{
    public function test_tag_generation_without_attributes(): void
    {
        $provider = new StyleSheet($this->tempDir);
        $tag = $provider->tag('/css/app.css', []);

        $this->assertStringContainsString('<link href="/css/app.css" rel="stylesheet">', $tag);
    }

    public function test_tag_generation_with_custom_attributes(): void
    {
        $provider = new StyleSheet($this->tempDir);
        $tag = $provider->tag('/css/app.css', ['media' => 'print']);

        $this->assertStringContainsString('href="/css/app.css"', $tag);
        $this->assertStringContainsString('rel="stylesheet"', $tag);
        $this->assertStringContainsString('media="print"', $tag);
    }

    public function test_throws_exception_when_file_does_not_exist(): void
    {
        $this->expectException(FileNotExistException::class);

        $provider = new StyleSheet($this->tempDir);
        $provider->add('/css/nonexistent.css');
    }

    public function test_add_and_minify_files(): void
    {
        $file1 = $this->tempDir . '/css/file1.css';
        $file2 = $this->tempDir . '/css/file2.css';
        file_put_contents($file1, "body { color: #ffffff; margin: 0px; }");
        file_put_contents($file2, "h1 { font-size: 20px; }");

        $provider = new StyleSheet($this->tempDir);
        $provider->add(['/css/file1.css', '/css/file2.css']);
        $this->assertCount(2, $provider);

        $buildPath = '/css/builds/';
        $isMade = $provider->make($buildPath);
        $this->assertTrue($isMade);

        $output = $provider->minify();
        $this->assertFileExists($this->tempDir . '/css/builds/' . $provider->getFilename());
        $minifiedContent = file_get_contents($this->tempDir . '/css/builds/' . $provider->getFilename());
        $this->assertStringContainsString('body{color:#ffffff;margin:0px}h1{font-size:20px}', $minifiedContent);
    }
}
