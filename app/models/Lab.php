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
        $stmt = $this->pdo->prepare("SELECT * FROM labs WHERE lab_id = ?");
        $stmt->execute([$lab_id]);
        return $stmt->fetch();
    }
}