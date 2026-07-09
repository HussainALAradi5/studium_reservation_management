<?php

return [
    'users' => [
        "`id` INT AUTO_INCREMENT PRIMARY KEY", 
        "`code` VARCHAR(50) NOT NULL UNIQUE",  
        "`userName` VARCHAR(100) NOT NULL",
        "`password` VARCHAR(255) NOT NULL",
        "`email` VARCHAR(150) NOT NULL UNIQUE",
        "`phoneNumber` VARCHAR(20) NOT NULL UNIQUE",
        "`secondPhoneNumber` VARCHAR(20)",
        "`birthDay` DATE",
        "`isActive` TINYINT(1) DEFAULT 1",
        "`gender` VARCHAR(10)",
        "`userType` VARCHAR(20)",
        "`createdAt` DATETIME",
        "`updatedAt` DATETIME"
    ],
    'groups' => [
        "`id` INT AUTO_INCREMENT PRIMARY KEY",
        "`name` VARCHAR(50) NOT NULL UNIQUE",
        "`description` VARCHAR(255)"
    ],
    'permissions' => [
        "`id` INT AUTO_INCREMENT PRIMARY KEY",
        "`key_name` VARCHAR(100) NOT NULL UNIQUE",
        "`description` VARCHAR(255)"
    ],
    'group_permissions' => [
        "`group_id` INT",
        "`permission_id` INT",
        "PRIMARY KEY (`group_id`, `permission_id`)",
        "FOREIGN KEY (`group_id`) REFERENCES `groups`(`id`) ON DELETE CASCADE",
        "FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE"
    ]
];