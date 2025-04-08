<?php
class Lab
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new lab
    public function createLab(
        string $name,
        string $location,
        int $capacity,
        string $description,
        string $institution,
        string $campus,
        string $specialization,
        int $creator_id
    ): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO labs 
            (name, location, capacity, description, institution, campus, specialization, creator_id) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([
                $name,
                $location,
                $capacity,
                $description,
                $institution,
                $campus,
                $specialization,
                $creator_id
            ]);

            return true;
        } catch (PDOException $e) {
            error_log("Error creating lab: " . $e->getMessage());
            return false;
        }
    }

    // Get all labs
    public function getAllLabs(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM labs");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Get lab by ID
    public function getLabById($lab_id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM labs WHERE lab_id = :lab_id");
        $stmt->bindParam(':lab_id', $lab_id);
        $stmt->execute();
        $lab = $stmt->fetch();
        return $lab ?: null;
    }

    // Get labs created by a specific admin
    public function getCreatorLabs(int $admin_id): ?array
    {
        if ($admin_id < 0)
            return null;

        $check_id = $this->pdo->prepare("SELECT 1 FROM admins WHERE admin_id = :admin_id LIMIT 1");
        $check_id->execute([":admin_id" => $admin_id]);

        if ($check_id->fetch()) {
            $stmt = $this->pdo->prepare("SELECT * FROM labs WHERE creator_id = :admin_id");
            $stmt->execute([":admin_id" => $admin_id]);
            return $stmt->fetchAll();
        }

        return null;
    }

}