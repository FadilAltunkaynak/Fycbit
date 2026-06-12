<?php

namespace App\Dtos;

class EvmResponseDto
{
    public function __construct(
        public bool $success,
        public string $message = '',
        public $data = null,
    ) {}
}
