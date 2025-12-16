<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Http\Exception;

/**
 * @author Michel.F 
 */
class BadRequestException extends HttpException
{
    protected static ?string $defaultMessage = 'Bad Request';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}
