<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Http\Exception;

/**
 * @author Michel.F 
 */
class UnauthorizedException extends HttpException
{
    protected static ?string $defaultMessage = 'Unauthorized';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
