<?php

class Schedule
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function createSchedule(
        int $lab_id,
        bool $active_monday,
        string $start_time_monday,
        string $end_time_monday,
        bool $active_tuesday,
        string $start_time_tuesday,
        string $end_time_tuesday,
        bool $active_wednesday,
        string $start_time_wednesday,
        string $end_time_wednesday,
        bool $active_thursday,
        string $start_time_thursday,
        string $end_time_thursday,
        bool $active_friday,
        string $start_time_friday,
        string $end_time_friday,
        bool $active_saturday,
        string $start_time_saturday,
        string $end_time_saturday,
        bool $active_sunday,
        string $start_time_sunday,
        string $end_time_sunday
    ): bool {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO schedules (
                    lab_id,
                    active_monday, start_time_monday, end_time_monday,
                    active_tuesday, start_time_tuesday, end_time_tuesday,
                    active_wednesday, start_time_wednesday, end_time_wednesday,
                    active_thursday, start_time_thursday, end_time_thursday,
                    active_friday, start_time_friday, end_time_friday,
                    active_saturday, start_time_saturday, end_time_saturday,
                    active_sunday, start_time_sunday, end_time_sunday
                ) VALUES (
                    :lab_id,
                    :active_monday, :start_time_monday, :end_time_monday,
                    :active_tuesday, :start_time_tuesday, :end_time_tuesday,
                    :active_wednesday, :start_time_wednesday, :end_time_wednesday,
                    :active_thursday, :start_time_thursday, :end_time_thursday,
                    :active_friday, :start_time_friday, :end_time_friday,
                    :active_saturday, :start_time_saturday, :end_time_saturday,
                    :active_sunday, :start_time_sunday, :end_time_sunday
                )"
            );

            $stmt->execute([
                ':lab_id' => $lab_id,
                ':active_monday' => (int) $active_monday,
                ':start_time_monday' => $start_time_monday,
                ':end_time_monday' => $end_time_monday,
                ':active_tuesday' => (int) $active_tuesday,
                ':start_time_tuesday' => $start_time_tuesday,
                ':end_time_tuesday' => $end_time_tuesday,
                ':active_wednesday' => (int) $active_wednesday,
                ':start_time_wednesday' => $start_time_wednesday,
                ':end_time_wednesday' => $end_time_wednesday,
                ':active_thursday' => (int) $active_thursday,
                ':start_time_thursday' => $start_time_thursday,
                ':end_time_thursday' => $end_time_thursday,
                ':active_friday' => (int) $active_friday,
                ':start_time_friday' => $start_time_friday,
                ':end_time_friday' => $end_time_friday,
                ':active_saturday' => (int) $active_saturday,
                ':start_time_saturday' => $start_time_saturday,
                ':end_time_saturday' => $end_time_saturday,
                ':active_sunday' => (int) $active_sunday,
                ':start_time_sunday' => $start_time_sunday,
                ':end_time_sunday' => $end_time_sunday
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error creating schedule: " . $e->getMessage());
            return false;
        }
    }

    public function updateSchedule(
        int $schedule_id,
        int $lab_id,
        bool $active_monday,
        string $start_time_monday,
        string $end_time_monday,
        bool $active_tuesday,
        string $start_time_tuesday,
        string $end_time_tuesday,
        bool $active_wednesday,
        string $start_time_wednesday,
        string $end_time_wednesday,
        bool $active_thursday,
        string $start_time_thursday,
        string $end_time_thursday,
        bool $active_friday,
        string $start_time_friday,
        string $end_time_friday,
        bool $active_saturday,
        string $start_time_saturday,
        string $end_time_saturday,
        bool $active_sunday,
        string $start_time_sunday,
        string $end_time_sunday
    ): bool {
        try {
            $stmt = $this->pdo->prepare(
                "UPDATE schedules SET
                    lab_id = :lab_id,
                    active_monday = :active_monday,
                    start_time_monday = :start_time_monday,
                    end_time_monday = :end_time_monday,
                    active_tuesday = :active_tuesday,
                    start_time_tuesday = :start_time_tuesday,
                    end_time_tuesday = :end_time_tuesday,
                    active_wednesday = :active_wednesday,
                    start_time_wednesday = :start_time_wednesday,
                    end_time_wednesday = :end_time_wednesday,
                    active_thursday = :active_thursday,
                    start_time_thursday = :start_time_thursday,
                    end_time_thursday = :end_time_thursday,
                    active_friday = :active_friday,
                    start_time_friday = :start_time_friday,
                    end_time_friday = :end_time_friday,
                    active_saturday = :active_saturday,
                    start_time_saturday = :start_time_saturday,
                    end_time_saturday = :end_time_saturday,
                    active_sunday = :active_sunday,
                    start_time_sunday = :start_time_sunday,
                    end_time_sunday = :end_time_sunday
                WHERE schedule_id = :schedule_id"
            );

            $stmt->execute([
                ':schedule_id' => $schedule_id,
                ':lab_id' => $lab_id,
                ':active_monday' => (int) $active_monday,
                ':start_time_monday' => $start_time_monday,
                ':end_time_monday' => $end_time_monday,
                ':active_tuesday' => (int) $active_tuesday,
                ':start_time_tuesday' => $start_time_tuesday,
                ':end_time_tuesday' => $end_time_tuesday,
                ':active_wednesday' => (int) $active_wednesday,
                ':start_time_wednesday' => $start_time_wednesday,
                ':end_time_wednesday' => $end_time_wednesday,
                ':active_thursday' => (int) $active_thursday,
                ':start_time_thursday' => $start_time_thursday,
                ':end_time_thursday' => $end_time_thursday,
                ':active_friday' => (int) $active_friday,
                ':start_time_friday' => $start_time_friday,
                ':end_time_friday' => $end_time_friday,
                ':active_saturday' => (int) $active_saturday,
                ':start_time_saturday' => $start_time_saturday,
                ':end_time_saturday' => $end_time_saturday,
                ':active_sunday' => (int) $active_sunday,
                ':start_time_sunday' => $start_time_sunday,
                ':end_time_sunday' => $end_time_sunday
            ]);

            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error updating schedule: " . $e->getMessage());
            return false;
        }
    }

    public function deleteSchedule(int $schedule_id): bool
    {
        try {
            $stmt = $this->pdo->prepare("DELETE FROM schedules WHERE schedule_id = :schedule_id");
            $stmt->execute([':schedule_id' => $schedule_id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error deleting schedule: " . $e->getMessage());
            return false;
        }
    }

    public function getById(int $schedule_id): ?array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM schedules WHERE schedule_id = :schedule_id");
            $stmt->execute([':schedule_id' => $schedule_id]);
            $result = $stmt->fetch();
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Error fetching schedule by id: " . $e->getMessage());
            return null;
        }
    }

    public function getByLabId(int $lab_id): array
    {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM schedules WHERE lab_id = :lab_id");
            $stmt->execute([':lab_id' => $lab_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error fetching schedule by lab_id: " . $e->getMessage());
            return [];
        }
    }

}