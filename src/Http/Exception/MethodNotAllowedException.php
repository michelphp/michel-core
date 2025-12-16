<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Http\Exception;

/**
 * @author Michel.F 
 */
class MethodNotAllowedException extends HttpException
{
    protected static ?string $defaultMessage = 'Method Not Allowed';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(405, $message, $code, $previous);
    }
}
