<?php
namespace App\Services;

abstract class BaseService {
    protected \PDO $pdo;

    public function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }
}