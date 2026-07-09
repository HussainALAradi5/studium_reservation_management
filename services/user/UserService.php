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
        $this->pdo->beginTransaction();

        try {
            // 1. Calculate the next sequence number safely
            $stmt = $this->pdo->query("SELECT MAX(CAST(SUBSTRING_INDEX(code, '/', -1) AS UNSIGNED)) as last_seq FROM users");
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $nextSequence = ($row['last_seq'] ?? 0) + 1;

            // 2. Assign the code to the existing $user object
            // This avoids the argument error by setting the property directly
            $user->code = User::generateCode($nextSequence);

            // 3. Insert the user[cite: 5]
            $sql = "INSERT INTO users (code, userName, password, email, phoneNumber, birthDay, isActive, gender, userType, createdAt) 
                VALUES (:code, :userName, :password, :email, :phoneNumber, :birthDay, :isActive, :gender, :userType, NOW())";

            $result = $this->pdo->prepare($sql)->execute($user->toDatabaseParams());

            $this->pdo->commit();

            if ($result) {
                $this->setAlert("Account created successfully.", AlertMessageType::SUCCESS, "Welcome!");
            }

            return $result;
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Retrieves all users for the Admin Panel data table.
     */
    public function getAllUsers(): array
    {
        // Omitting passwords and sensitive data for the view
        $sql = "SELECT code, userName, email, phoneNumber, isActive, userType, createdAt 
                FROM users 
                ORDER BY createdAt DESC";
        return $this->pdo->query($sql)->fetchAll(\PDO::FETCH_ASSOC);
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