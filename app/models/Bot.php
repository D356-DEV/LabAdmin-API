<?php
class Bot
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Get bot dataset
    public function getBotDataset(): ?array
    {
        try {
            $sql = "SELECT 
                    labs.name AS lab_name, 
                    CONCAT(users.first_name, ' ', users.last_name) AS admin_name, 
                    '09:00' AS shift_start, 
                    '17:00' AS shift_end 
                FROM admins 
                JOIN users ON admins.user_id = users.user_id 
                JOIN labs ON admins.lab_id = labs.lab_id";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage()); // Log error
            return null;
        }
    }

}