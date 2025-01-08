<?php

namespace App\Contracts;

interface RssFeedContract
{
    public function get(string $endpoint, array $queryParams = []): array;
}
