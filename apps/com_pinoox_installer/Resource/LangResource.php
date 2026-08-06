<?php

namespace App\com_pinoox_installer\Resource;

use Pinoox\Component\Http\Api\ApiResource;
use Pinoox\Component\Http\Request;

final class LangResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        $payload = is_array($this->resource) ? $this->resource : [];

        return [
            'direction' => (string) ($payload['direction'] ?? 'ltr'),
            'lang' => is_array($payload['lang'] ?? null) ? $payload['lang'] : [],
        ];
    }
}
