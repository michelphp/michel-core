<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Http\Exception;

/**
 * @author Michel.F 
 */
class ForbiddenException extends HttpException
{
    protected static ?string $defaultMessage = 'Access Denied';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(403, $message, $code, $previous);
    }
}
