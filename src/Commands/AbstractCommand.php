<?php

namespace OneMustCode\LaravelDDD\Commands;

use Illuminate\Console\Command;

abstract class AbstractCommand extends Command
{
    /**
     * @return string
     */
    protected function getNamespace(): string
    {
        return config('ddd.namespace');
    }

    /**
     * @return string
     */
    protected function getSource(): string
    {
        return config('ddd.source');
    }

    /**
     * Creates the given path
     *
     * @param string $path
     */
    protected function createDirectory($path)
    {
        $segments = explode(DIRECTORY_SEPARATOR, $path);
        $segments = array_filter($segments);

        $directory = [];

        foreach ($segments as $segment) {
            $directory[] = $segment;
            $path = implode(DIRECTORY_SEPARATOR, $directory);
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    /**
     * Creates file from given path and contents
     *
     * @param string $path
     * @param string $contents
     */
    protected function createFile($path, $contents)
    {
        $this->createDirectory(pathinfo($path, PATHINFO_DIRNAME));

        file_put_contents($path, $contents);
    }

    /**
     * Parses the given stub and writes it to the given filename
     *
     * @param $stub
     * @param $filename
     * @param array $variables
     */
    protected function parseStub($stub, $filename, array $variables = [])
    {
        if (is_file($filename)) {
            return;
        }

        $variables = array_merge(config('ddd', []), $variables);

        $contents = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR . $stub);

        foreach ($variables as $name => $value) {
            $name = strtoupper($name);
            $contents = preg_replace('#{{ '. $name .' }}#', $value, $contents);
        }

        $this->createFile($filename, $contents);
    }
}