<?php

namespace App;

use App\Contracts\ResponseHandlerInterface;

class HtmlResponseHandler implements ResponseHandlerInterface
{
    public function handle(string $template): void
    {
        $content = file_get_contents($template);
        echo $content;
        exit;
    }
}