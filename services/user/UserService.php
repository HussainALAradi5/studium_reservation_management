<?php

namespace App\Services\User;

use App\Models\User\User;
use App\Services\BaseService;
use App\Utilities\Validation;
use App\Enums\UI\AlertMessageType;

class UserService extends BaseService
{
    public function createUser(User $user): bool
{
    $fields = ['email', 'userName', 'phoneNumber'];
    foreach ($fields as $field) {
        if (Validation::isDuplicate($this->pdo, $field, $user->$field)) {
            $this->setAlert(ucfirst($field) . " is already in use.", AlertMessageType::ERROR, "Registration Failed");
            throw new \Exception("Duplicate entry detected.");
        }
    }

    $sql = "INSERT INTO users (userName, password, email, phoneNumber, birthDay, isActive, gender, userType, createdAt) 
            VALUES (:userName, :password, :email, :phoneNumber, :birthDay, :isActive, :gender, :userType, NOW())";

    $result = $this->pdo->prepare($sql)->execute($user->toDatabaseParams());
    
    if ($result) {
        $this->setAlert("Account created successfully. You can now log in.", AlertMessageType::SUCCESS, "Welcome!");
    }

    return $result;
}
    public function updateUser(int $code, User $user): bool
    {
        $sql = "UPDATE users SET userName = :userName, email = :email, phoneNumber = :phoneNumber, 
                secondPhoneNumber = :secondPhoneNumber, birthDay = :birthDay, gender = :gender, 
                userType = :userType, updatedAt = NOW() WHERE code = :code";

        $params = $user->toDatabaseParams(false);
        $params['code'] = $code;

        $result = $this->pdo->prepare($sql)->execute($params);

        if ($result) {
            $this->setAlert("User updated successfully.", AlertMessageType::SUCCESS, "Update Successful");
        } else {
            $this->setAlert("Failed to update user.", AlertMessageType::ERROR, "System Error");
        }

        return $result;
    }

    public function deleteUser(int $code): bool
    {
        $stmt = $this->pdo->prepare("SELECT isActive FROM users WHERE code = ?");
        $stmt->execute([$code]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->setAlert("User account not found.", AlertMessageType::ERROR, "System Error");
            throw new \Exception("User not found.");
        }

        return $this->pdo->prepare("UPDATE users SET isActive = 0, updatedAt = NOW() WHERE code = ?")
            ->execute([$code]);
    }
}