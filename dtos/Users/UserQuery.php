<?php

declare(strict_types=1);

namespace App\Dtos\Users;

use App\Dtos\Common\PageRequest;
use App\Enums\Users\UserRole;

final class UserQuery
{
    public function __construct(
        public readonly PageRequest $page,
        public readonly string $search = '',
        public readonly ?UserRole $role = null,
        public readonly ?bool $isActive = null
    ) {
    }

    public static function fromQuery(array $query): self
    {
        $status = $query['status'] ?? '';
        return new self(
            PageRequest::fromQuery($query, ['created', 'name', 'role'], 'created'),
            trim((string) ($query['q'] ?? '')),
            UserRole::tryFrom((string) ($query['role'] ?? '')),
            $status === 'active' ? true : ($status === 'inactive' ? false : null)
        );
    }
}
