<?php
class Lab
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new lab
    public function createLab($name, $location, $capacity, $description, $institution, $campus, $specialization, $manager_id, $creator_id, $lab_image): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO labs (lab_name, lab_description, lab_location) 
                VALUES (?, ?, ?)"
            );
            $stmt->execute([$lab_name, $lab_description, $lab_location]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    // Get all labs
    public function getAllLabs(): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM labs");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Get lab by ID
    public function getLabById($lab_id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM labs WHERE lab_id = ?");
        $stmt->execute([$lab_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}