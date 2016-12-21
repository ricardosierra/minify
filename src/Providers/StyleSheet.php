<?php

namespace Devfactory\Minify\Providers;

use Devfactory\Minify\Contracts\MinifyInterface;
use CssMinifier;

class StyleSheet extends BaseProvider implements MinifyInterface
{
    /**
     *  The extension of the outputted file.
     */
    const EXTENSION = '.css';

    /**
     * @return string
     */
    public function minify()
    {
        $minified = new CssMinifier($this->appended);

        return $this->put($minified->getMinified());
    }

    /**
     * @param $file
     * @param array $attributes
     * @return string
     */
    public function tag($file, array $attributes = array())
    {
        $attributes = array('href' => $file, 'rel' => 'stylesheet') + $attributes;

        return "<link {$this->attributes($attributes)}>".PHP_EOL;
    }

    /**
     * Override appendFiles to solve css url path issue
     * 
     * @throws \Devfactory\Minify\Exceptions\FileNotExistException
     */
    protected function appendFiles()
    {
        foreach ($this->files as $file) {
            if ($this->checkExternalFile($file)) {
                if (strpos($file, '//') === 0) $file = 'http:'.$file;

                $headers = $this->headers;
                foreach ($headers as $key => $value) {
                    $headers[$key] = $key.': '.$value;
                }
                $context = stream_context_create(array('http' => array(
                        'ignore_errors' => true,
                        'header' => implode("\r\n", $headers),
                )));

                $http_response_header = array(false);


                if (strpos($http_response_header[0], '200') === false) {
                    throw new FileNotExistException("File '{$file}' does not exist");
                }
            }
            $contents = $this->urlCorrection($file);
            $this->appended .= $contents."\n";
        }
    }

    /**
     * Css url path correction
     * 
     * @param string $file
     * @return string
     */
    public function urlCorrection($file)
    {
        $folder = str_replace(public_path(), '', $file);
        $folder = str_replace(basename($folder), '', $folder);
        return str_replace('url(\'', 'url(\''.$folder, file_get_contents($file));
    }
}
