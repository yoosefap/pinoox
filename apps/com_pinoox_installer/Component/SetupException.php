<?php

namespace App\com_pinoox_installer\Component;

use RuntimeException;
use Throwable;

final class SetupException extends RuntimeException
{
    public function __construct(
        private readonly string $messageKey,
        string $detail = '',
        ?Throwable $previous = null,
    ) {
        $message = $detail !== '' ? $messageKey . ': ' . $detail : $messageKey;

        parent::__construct($message, 0, $previous);
    }

    public function messageKey(): string
    {
        return $this->messageKey;
    }
}
