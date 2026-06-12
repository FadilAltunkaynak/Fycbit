<?php

namespace App\Services\ParallelService;

use Closure;
use App\Services\ParallelService\ParallelService;

interface ParallelServiceContract
{
    public function add(string $returnValueKey, Closure $callback): ParallelService;
    public function addClassMethod(string $returnValueKey, string $class, string $method, ...$params): ParallelService;
    public function fireCommand(): mixed;
    public function async(Closure $callback): mixed;
    public function asyncFire(): mixed;
    public function wait(?int $processId = null): array;
    public function fire(): int|array;
}