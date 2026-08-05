<?php

namespace App\com_pinoox_installer\Resource;

use Pinoox\Component\Http\Api\ApiResource;
use Pinoox\Component\Http\Request;

final class AgreementResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'text' => (string) $this->resource,
        ];
    }
}
