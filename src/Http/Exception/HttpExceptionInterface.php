<?php

declare(strict_types=1);

namespace Michel\Framework\Core\Http\Exception;

/**
 * @author Michel.F 
 */
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
