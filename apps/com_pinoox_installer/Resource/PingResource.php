<?php

namespace App\com_pinoox_installer\Resource;

use Pinoox\Component\Http\Api\ApiResource;
use Pinoox\Component\Http\Request;

final class PingResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'ok' => true,
            'routing' => true,
            'timestamp' => (int) ($this->resource['timestamp'] ?? time()),
        ];
    }
}
