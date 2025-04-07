<?php

class Admin 
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Is User an Admin
    public function isUserAdmin(int $user_id): bool
    {
        if ($user_id < 1) return false;
    
        $stmt = $this->pdo->prepare("SELECT 1 FROM admins WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([
            ":user_id" => $user_id
        ]);
    
        return $stmt->fetch() !== false;
    }
    
    // Get Admin info from user_id
    public function getAdminByUser(int $user_id): ?array
    {
        if ($user_id < 1) return null;
    
        $stmt = $this->pdo->prepare("SELECT * FROM admins WHERE user_id = :user_id LIMIT 1");
        $stmt->execute([
            ":user_id" => $user_id
        ]);
    
        return $stmt->fetch() ?: null;
    }
    
}