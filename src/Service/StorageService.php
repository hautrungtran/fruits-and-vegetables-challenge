<?php

namespace App\Service;

class StorageService
{
    public function __construct(protected string $request)
    {
    }

    public function getRequest(): string
    {
        return $this->request;
    }
}
