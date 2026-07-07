<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * HTTP 404 Not Found exception
 */

namespace Michel\Framework\Core\Http\Exception;

class NotFoundException extends HttpException
{
    protected static ?string $defaultMessage = 'Not Found';

    public function __construct(?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        parent::__construct(404, $message, $code, $previous);
    }
}
