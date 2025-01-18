<?php

declare(strict_types=1);

namespace Camoo\Payment\Exception;

use Throwable;

final class InvalidArgumentException extends \InvalidArgumentException
{
    private const MESSAGE = 'CAMOO API Invalid Argument:';

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = self::MESSAGE . $message;
        parent::__construct(trim($message), $code, $previous);
    }
}
