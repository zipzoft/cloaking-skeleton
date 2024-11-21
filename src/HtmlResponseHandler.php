<?php

namespace App;

use App\Contracts\ResponseHandlerInterface;
use RuntimeException;

/**
 * Handles an HTML response.
 *
 * This class is responsible for handling HTML responses. It is a simple
 * implementation that reads the contents of a file and outputs it.
 *
 * @package App
 */
class HtmlResponseHandler implements ResponseHandlerInterface
{
    /**
     * Handles an HTML response.
     *
     * This method takes a template (a file path) and reads the contents
     * of the file. It then outputs the contents and exits.
     *
     * @param string $template The template to handle.
     * @throws RuntimeException If the template file cannot be read.
     *
     * @return void
     */
    public function handle(string $template): void
    {
        $content = @file_get_contents($template);
        if ($content === false) {
            throw new RuntimeException('Failed to read template file');
        }
        echo $content;
        exit;
    }
}