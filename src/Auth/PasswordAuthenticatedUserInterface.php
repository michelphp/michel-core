<?php

namespace Michel\Framework\Core\Auth;

interface PasswordAuthenticatedUserInterface
{
    public function getPassword(): string;

    public function setPassword(?string $password);
}
