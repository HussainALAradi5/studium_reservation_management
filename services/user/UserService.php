<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Services\BaseService;
use App\Utilities\Validation;

class UserService extends BaseService
{
    public function createUser(User $user): bool
    {
        $fields = ['email', 'userName', 'phoneNumber'];
        foreach ($fields as $field) {
            if (Validation::isDuplicate($this->pdo, $field, $user->$field)) {
                throw new \Exception(ucfirst($field) . " already exists.");
            }
        }

        $sql = "INSERT INTO users (userName, password, email, phoneNumber, secondPhoneNumber, birthDay, isActive, gender, userType, createdAt) 
                VALUES (:userName, :password, :email, :phoneNumber, :secondPhoneNumber, :birthDay, :isActive, :gender, :userType, NOW())";

        return $this->pdo->prepare($sql)->execute($user->toDatabaseParams());
    }

    public function updateUser(int $code, User $user): bool
    {
        $sql = "UPDATE users SET userName = :userName, email = :email, phoneNumber = :phoneNumber, 
                secondPhoneNumber = :secondPhoneNumber, birthDay = :birthDay, gender = :gender, 
                userType = :userType, updatedAt = NOW() WHERE code = :code";

        $params = $user->toDatabaseParams(false);
        $params['code'] = $code;

        return $this->pdo->prepare($sql)->execute($params);
    }

    public function deleteUser(int $code): bool
    {
        return $this->pdo->prepare("UPDATE users SET isActive = 0 WHERE code = ?")->execute([$code]);
    }
}
