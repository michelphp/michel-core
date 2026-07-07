<?php

declare(strict_types=1);

/**
 * Michel PHP Framework
 *
 * @package    MichelFramework
 * @author     Michel.F
 * @license    Mozilla Public License v2.0 (MPL-2.0)
 *
 * Base HTTP exception class
 */

namespace Michel\Framework\Core\Http\Exception;

class HttpException extends \Exception implements HttpExceptionInterface
{
    protected static ?string $defaultMessage = 'An error occurred . Please try again later.';
    private int $statusCode;

    private ?string $contentType = null;

    public function __construct(int $statusCode, ?string $message = null, int $code = 0, \Throwable $previous = null)
    {
        if ($message === null) {
            $message = static::$defaultMessage;
        }

        $this->statusCode = $statusCode;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return int HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getDefaultMessage(): string
    {
        return static::$defaultMessage;
    }

    public function setContentType(string  $contentType): self
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}
