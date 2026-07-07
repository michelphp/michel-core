<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Interface for HTTP exceptions
 */

namespace Michel\Framework\Core\Http\Exception;

interface HttpExceptionInterface extends \Throwable
{
    /**
     * Returns the status code.
     *
     * @return int An HTTP response status code
     */
    public function getStatusCode(): int;

    /**
     * Returns the default message status.
     *
     * @return string
     */
    public function getDefaultMessage(): string;


    public function getContentType(): ?string;
}
