<?php

declare(strict_types=1);

namespace App\Repositories\Regional;

use App\Constants\Common\CodePrefixes;
use App\Repositories\Base\BaseRepository;

/** Addresses are written together with their stadium; reads go through the stadium join. */
final class AddressRepository extends BaseRepository
{
    protected function table(): string
    {
        return 'addresses';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::ADDRESS;
    }
}
