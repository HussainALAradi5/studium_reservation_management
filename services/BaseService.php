<?php

namespace App\Services;

// In BaseService.php
namespace App\Services;

abstract class BaseService
{
    protected \PDO $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    protected function setAlert(string $message, \App\Enums\UI\AlertMessageType $type, string $title = ''): void
    {
        $_SESSION['flash_alert'] = [
            'message' => $message,
            'type'    => $type->value,
            'title'   => $title
        ];
    }
    protected function setDialog(string $title, string $content, bool $isAlertDialog = false): void
    {
    $_SESSION['flash_dialog'] = [
            'title'   => $title,
            'content' => $content,
            'isAlertDialog' => $isAlertDialog
        ];
    }
}
