<?php

namespace App\com_pinoox_installer\Component;

use RuntimeException;

final class InstallPlatformException extends RuntimeException
{
    /**
     * @param list<string> $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    /**
     * @return list<string>
     */
    public function errors(): array
    {
        return $this->errors;
    }
}
