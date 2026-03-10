<?php

namespace Michel\Framework\Core\Http;

use Psr\Http\Message\ServerRequestInterface;

class RequestContext
{
    private ?ServerRequestInterface $request = null;
    private ?object $user = null;

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }
    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
    }

    public function setUser(object $user): void
    {
        $this->user = $user;
    }
    public function getUser(): ?object
    {
        return $this->user;
    }
}
