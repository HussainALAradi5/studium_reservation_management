<?php

declare(strict_types=1);

namespace App\Repositories\Stadiums;

use App\Constants\Common\CodePrefixes;
use App\Dtos\Common\AccessScope;
use App\Dtos\Common\PageResult;
use App\Dtos\Stadiums\StadiumQuery;
use App\Models\Stadiums\Stadium;
use App\Repositories\Base\BaseRepository;
use App\Utilities\Formatting\IdentityNormalizer;
use App\Utilities\Pagination\Paginator;

final class StadiumRepository extends BaseRepository
{
    private const SELECT = 's.*, a.code AS address_code, a.city_id AS address_city_id, a.street AS address_street,
        a.building AS address_building, a.block AS address_block, a.area AS address_area,
        a.latitude AS address_latitude, a.longitude AS address_longitude,
        c.name AS city_name, c.code AS city_code, m.full_name AS manager_name, m.code AS manager_code';

    private const FROM = 'FROM stadiums s
        JOIN addresses a ON a.id = s.address_id
        JOIN cities c ON c.id = a.city_id
        LEFT JOIN users m ON m.id = s.manager_id';

    private const SORT_COLUMNS = ['name' => 's.name', 'price' => 's.price_per_hour', 'created' => 's.created_at'];

    protected function table(): string
    {
        return 'stadiums';
    }

    protected function codePrefix(): string
    {
        return CodePrefixes::STADIUM;
    }

    public function findById(int $id): ?Stadium
    {
        return $this->findOneWhere('s.id = :id', ['id' => $id]);
    }

    public function findByCode(string $code): ?Stadium
    {
        return $this->findOneWhere('s.code = :code', ['code' => IdentityNormalizer::code($code)]);
    }

    /** Locks the stadium row until the transaction ends; serializes bookings of one stadium. */
    public function lockById(int $id): void
    {
        $this->fetchOne('SELECT id FROM stadiums WHERE id = :id FOR UPDATE', ['id' => $id]);
    }

    /**
     * @param bool $publicOnly only active stadiums in active cities (customer browsing)
     */
    public function paginate(StadiumQuery $query, AccessScope $scope, bool $publicOnly): PageResult
    {
        [$scopeSql, $params] = $scope->toSql('s', null);
        $where = ['s.deleted_at IS NULL', $scopeSql];
        if ($publicOnly) {
            $where[] = 's.is_active = 1 AND c.is_active = 1';
        } elseif ($query->isActive !== null) {
            $where[] = 's.is_active = :is_active';
            $params['is_active'] = (int) $query->isActive;
        }
        if ($query->search !== '') {
            $where[] = '(s.name LIKE :q1 OR a.area LIKE :q2 OR a.street LIKE :q3)';
            $like = '%' . $query->search . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
        }
        if ($query->cityCode !== '') {
            $where[] = 'c.code = :city_code';
            $params['city_code'] = $query->cityCode;
        }
        if ($query->sportType !== null) {
            $where[] = 's.sport_type = :sport_type';
            $params['sport_type'] = $query->sportType->value;
        }
        return Paginator::paginate(
            $this->pdo,
            self::SELECT,
            self::FROM . ' WHERE ' . implode(' AND ', $where),
            $params,
            $query->page,
            self::SORT_COLUMNS,
            static fn (array $row) => Stadium::fromRow($row)
        );
    }

    /** @return Stadium[] stadiums within scope, for select boxes and reports */
    public function listInScope(AccessScope $scope): array
    {
        [$scopeSql, $params] = $scope->toSql('s', null);
        $rows = $this->fetchAllRows(
            'SELECT ' . self::SELECT . ' ' . self::FROM . " WHERE s.deleted_at IS NULL AND $scopeSql ORDER BY s.name",
            $params
        );
        return array_map(static fn (array $row) => Stadium::fromRow($row), $rows);
    }

    public function countActive(): int
    {
        return (int) $this->fetchValue('SELECT COUNT(*) FROM stadiums WHERE is_active = 1 AND deleted_at IS NULL');
    }

    private function findOneWhere(string $condition, array $params): ?Stadium
    {
        $row = $this->fetchOne('SELECT ' . self::SELECT . ' ' . self::FROM . " WHERE $condition AND s.deleted_at IS NULL", $params);
        return $row ? Stadium::fromRow($row) : null;
    }
}
