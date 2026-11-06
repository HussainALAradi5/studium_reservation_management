<?php

declare(strict_types=1);

namespace App\Repositories\Regional;

use App\Constants\Common\CodePrefixes;
use App\Dtos\Common\PageResult;
use App\Dtos\Regional\CityQuery;
use App\Models\Regional\City;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Pagination\Paginator;

final class CityRepository extends BaseRepository
{
    private const SORT_COLUMNS = ['name' => 'c.name', 'created' => 'c.created_at'];

    protected function table(): string
    {
        return 'cities';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::CITY;
    }

    public function findById(int $id): ?City
    {
        $row = $this->fetchOne('SELECT * FROM cities WHERE id = :id AND deleted_at IS NULL', ['id' => $id]);
        return $row ? City::fromRow($row) : null;
    }

    public function findByCode(string $code): ?City
    {
        $row = $this->fetchOne('SELECT * FROM cities WHERE code = :code AND deleted_at IS NULL', ['code' => IdentityNormalizer::code($code)]);
        return $row ? City::fromRow($row) : null;
    }

    public function paginate(CityQuery $query): PageResult
    {
        $where = 'WHERE c.deleted_at IS NULL';
        $params = [];
        if ($query->search !== '') {
            $where .= ' AND c.name LIKE :q';
            $params['q'] = '%' . $query->search . '%';
        }
        $select = 'c.*, (SELECT COUNT(*) FROM stadiums s JOIN addresses a ON a.id = s.address_id
                   WHERE a.city_id = c.id AND s.deleted_at IS NULL) AS stadium_count';
        return Paginator::paginate(
            $this->pdo,
            $select,
            "FROM cities c $where",
            $params,
            $query->page,
            self::SORT_COLUMNS,
            static fn (array $row) => City::fromRow($row)
        );
    }

    /** @return City[] */
    public function listActive(): array
    {
        $rows = $this->fetchAllRows('SELECT * FROM cities WHERE is_active = 1 AND deleted_at IS NULL ORDER BY name');
        return array_map(static fn (array $row) => City::fromRow($row), $rows);
    }

    public function existsByName(string $name, string $countryCode, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM cities WHERE name = :name AND country_code = :country AND deleted_at IS NULL';
        $params = ['name' => $name, 'country' => $countryCode];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        return $this->fetchOne($sql, $params) !== null;
    }

    public function countAddresses(int $cityId): int
    {
        return (int) $this->fetchValue(
            'SELECT COUNT(*) FROM addresses WHERE city_id = :id AND deleted_at IS NULL',
            ['id' => $cityId]
        );
    }
}
