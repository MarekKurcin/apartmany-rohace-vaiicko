<?php

namespace App\Auth;

use Framework\Core\IIdentity;

/**
 * Database-based identity implementation
 */
class DbIdentity implements IIdentity
{
    private int $id;
    private string $email;
    private string $name;

    public function __construct(int $id, string $email, string $name)
    {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
