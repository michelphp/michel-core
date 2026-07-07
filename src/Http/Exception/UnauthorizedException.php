<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * HTTP 401 Unauthorized exception
 */

namespace Michel\Framework\Core\Http\Exception;

class UnauthorizedException extends HttpException
{
    protected static ?string $defaultMessage = 'Unauthorized';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(401, $message, $code, $previous);
    }
}
