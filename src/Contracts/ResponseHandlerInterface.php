<?php

namespace App\Contracts;

interface ResponseHandlerInterface
{
    public function handle(string $template): void;
}