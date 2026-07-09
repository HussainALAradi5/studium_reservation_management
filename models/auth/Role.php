<?php

namespace App\Models\Auth;

class Role
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $description,
    ) {}

    public static function fromDatabase(array $data): self
    {
        return new self(
            $data['id'] ?? null,
            $data['name'],
            $data['description']
        );
    }
}
