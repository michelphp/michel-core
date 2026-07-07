<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * HTTP 400 Bad Request exception
 */

namespace Michel\Framework\Core\Http\Exception;

class BadRequestException extends HttpException
{
    protected static ?string $defaultMessage = 'Bad Request';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(400, $message, $code, $previous);
    }
}
