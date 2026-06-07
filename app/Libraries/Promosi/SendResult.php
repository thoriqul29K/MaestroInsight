<?php

namespace App\Libraries\Promosi;

class SendResult
{
    public function __construct(
        public bool $success,
        public ?string $errorMessage = null,
    ) {
    }
}
