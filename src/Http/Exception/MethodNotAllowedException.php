<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * HTTP 405 Method Not Allowed exception
 */

namespace Michel\Framework\Core\Http\Exception;

class MethodNotAllowedException extends HttpException
{
    protected static ?string $defaultMessage = 'Method Not Allowed';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(405, $message, $code, $previous);
    }
}
