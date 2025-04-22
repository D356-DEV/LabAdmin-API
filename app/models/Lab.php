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

    // Update Lab´s Name
    public function updateName(int $lab_id, string $name): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET name = :name WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":name" => $name,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating name: " . $e->getMessage());
            return false;
        }
    }

    // Update Institution
    public function updateInstitution(int $lab_id, string $institution): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET institution = :institution WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ':institution' => $institution,
                ':lab_id' => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating institution: " . $e->getMessage());
            return false;
        }
    }

    // Update Campus
    public function updateCampus(int $lab_id, string $campus): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET campus = :campus WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":campus" => $campus,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating campus: " . $e->getMessage());
            return false;
        }
    }

    // Update Specialization
    public function updateSpecialization(int $lab_id, string $specialization): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET specialization = :specialization WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":specialization" => $specialization,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating specialization: " . $e->getMessage());
            return false;
        }
    }

    // Update Location
    public function updateLocation(int $lab_id, string $location): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET location = :location WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":location" => $location,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating location: " . $e->getMessage());
            return false;
        }
    }

    // Update Description
    public function updateDescription(int $lab_id, string $description): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET description = :description WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":description" => $description,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating description: " . $e->getMessage());
            return false;
        }
    }

    // Update Capacity
    public function updateCapacity(int $lab_id, int $capacity): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET capacity = :capacity WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":capacity" => $capacity,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating capacity: " . $e->getMessage());
            return false;
        }
    }

    // Update Lab´s Admin
    public function updateAdminId(int $lab_id, int $admin_id): bool
    {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE labs SET admin_id = :admin_id WHERE lab_id = :lab_id"
            );
            $stmt->execute([
                ":admin_id" => $admin_id,
                ":lab_id" => $lab_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating admin_id: " . $e->getMessage());
            return false;
        }
    }

    // Delete Lab
    public function deleteLab(int $lab_id, int $creator_id): bool
    {
        if ($lab_id < 1 || $creator_id < 1) {
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM labs WHERE lab_id = :lab_id AND creator_id = :creator_id"
            );
            $stmt->execute([
                ":lab_id" => $lab_id,
                ":creator_id" => $creator_id
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting lab: " . $e->getMessage());
            return false;
        }
    }

}