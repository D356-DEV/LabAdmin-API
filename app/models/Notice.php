<?php

class Notice
{
    private $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createNotice($admin_id, $lab_id, $title, $message): bool
    {
        try {
            $stmt = $this->pdo->prepare("INSERT INTO notices (admin_id, lab_id, title, message) VALUES (:admin_id, :lab_id, :title, :message)");
            $stmt->execute([
                ':admin_id' => $admin_id,
                ':lab_id' => $lab_id,
                ':title' => $title,
                ':message' => $message
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error creating notice: " . $e->getMessage());
            return false;
        }
    }

    public function getByLabId($lab_id): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM notices WHERE lab_id = :lab_id ORDER BY creation_date DESC");
            $stmt->execute([':lab_id' => $lab_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching notices: " . $e->getMessage());
            return [];
        }
    }

    public function getByAdminId($admin_id): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM notices WHERE admin_id = :admin_id ORDER BY creation_date DESC");
            $stmt->execute([':admin_id' => $admin_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching notices: " . $e->getMessage());
            return [];
        }
    }

    public function deleteNotice($admin_id, $notice_id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM notices WHERE admin_id = :admin_id AND id = :notice_id");
            $stmt->execute([
                ':admin_id' => $admin_id,
                ':notice_id' => $notice_id
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting notice: " . $e->getMessage());
            return false;
        }
    }

}