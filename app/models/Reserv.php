<?php
class Reserv
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createReserv(
        string $reserv_date,
        string $start_time,
        string $end_time,
        int $lab_id,
        int $user_id,
        string $description
    ): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO reservs (reserv_date, start_time, end_time, lab_id, user_id, description)
                 VALUES (:reserv_date, :start_time, :end_time, :lab_id, :user_id, :description)
                "
            );
            $stmt->execute([
                ":reserv_date" => $reserv_date,
                ":start_time" => $start_time,
                ":end_time" => $end_time,
                ":lab_id" => $lab_id,
                ":user_id" => $user_id,
                ":description" => $description
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error inserting reserv: " . $e->getMessage());
            return false;
        }
    }

    public function acceptReserv(int $reserv_id, int $admin_id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE reservs 
             SET status = 'accepted', admin_id = :admin_id 
             WHERE id = :reserv_id"
            );
            $stmt->execute([
                ':admin_id' => $admin_id,
                ':reserv_id' => $reserv_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error accepting reserv: " . $e->getMessage());
            return false;
        }
    }

    public function rejectReserv(int $reserv_id, int $admin_id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE reservs 
             SET status = 'rejected', admin_id = :admin_id 
             WHERE id = :reserv_id"
            );
            $stmt->execute([
                ':admin_id' => $admin_id,
                ':reserv_id' => $reserv_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error rejecting reserv: " . $e->getMessage());
            return false;
        }
    }

    public function getAllReservs(): array
    {
        try {
            $stmt = $this->pdo->query("SELECT * FROM reservs ORDER BY reserv_date DESC, start_time ASC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching all reservs: " . $e->getMessage());
            return [];
        }
    }

    public function getReservById(int $reserv_id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM reservs WHERE id = :reserv_id");
            $stmt->execute([':reserv_id' => $reserv_id]);
            $reserv = $stmt->fetch(PDO::FETCH_ASSOC);

            return $reserv ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching reserv by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getReservsByLabId(int $lab_id): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM reservs WHERE lab_id = :lab_id ORDER BY reserv_date DESC, start_time ASC");
            $stmt->execute([':lab_id' => $lab_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reservs by lab_id: " . $e->getMessage());
            return [];
        }
    }

    public function getReservsByUserId(int $user_id): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM reservs WHERE user_id = :user_id ORDER BY reserv_date DESC, start_time ASC");
            $stmt->execute([':user_id' => $user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reservs by user_id: " . $e->getMessage());
            return [];
        }
    }

    public function getReservsByLabIdAndStatus(int $lab_id, string $status): array
    {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT * FROM reservs 
             WHERE lab_id = :lab_id AND status = :status 
             ORDER BY reserv_date DESC, start_time ASC"
            );
            $stmt->execute([
                ':lab_id' => $lab_id,
                ':status' => $status
            ]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching reservs by lab_id and status: " . $e->getMessage());
            return [];
        }
    }

}