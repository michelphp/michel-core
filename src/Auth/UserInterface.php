<?php

namespace Michel\Framework\Core\Auth;

interface UserInterface
{
    public function getUserIdentifier(): string;
}
