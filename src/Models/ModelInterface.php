<?php

declare(strict_types=1);

namespace Camoo\Payment\Models;

interface ModelInterface
{
    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self;

    /** @return array<string, mixed> $data */
    public function toArray(): array;
}
