<?php
require __DIR__ . '/../models/Schedule.php';
require __DIR__ . '/../../config/db.php';

class ScheduleController
{
    private $scheduleModel;
    public function __construct($pdo)
    {
        $this->scheduleModel = new Schedule($pdo);
    }

    public function createSchedule(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON format"
            ]);
            return;
        }

        $requiredFields = [
            'lab_id', 
            'active_monday', 'start_time_monday', 'end_time_monday',
            'active_tuesday', 'start_time_tuesday', 'end_time_tuesday',
            'active_wednesday', 'start_time_wednesday', 'end_time_wednesday',
            'active_thursday', 'start_time_thursday', 'end_time_thursday',
            'active_friday', 'start_time_friday', 'end_time_friday',
            'active_saturday', 'start_time_saturday', 'end_time_saturday',
            'active_sunday', 'start_time_sunday', 'end_time_sunday'
        ];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || trim($data[$field]) === '');
        if (!empty($missingFields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missingFields)
            ]);
            return;
        }

        try{
            $success = $this->scheduleModel->createSchedule(
                (int)$data['lab_id'],
                (bool)$data['active_monday'], trim($data['start_time_monday']), trim($data['end_time_monday']),
                (bool)$data['active_tuesday'], trim($data['start_time_tuesday']), trim($data['end_time_tuesday']),
                (bool)$data['active_wednesday'], trim($data['start_time_wednesday']), trim($data['end_time_wednesday']),
                (bool)$data['active_thursday'], trim($data['start_time_thursday']), trim($data['end_time_thursday']),
                (bool)$data['active_friday'], trim($data['start_time_friday']), trim($data['end_time_friday']),
                (bool)$data['active_saturday'], trim($data['start_time_saturday']), trim($data['end_time_saturday']),
                (bool)$data['active_sunday'], trim($data['start_time_sunday']), trim($data['end_time_sunday'])
            );
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "Schedule created successfully" : "Schedule was not created"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while creating the schedule"
            ]);
            error_log("Schedule creation error: " . $e->getMessage());
        }
    }

    public function updateSchedule(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON format"
            ]);
            return;
        }

        $requiredFields = [
            'schedule_id', 'lab_id', 'active_monday', 'start_time_monday', 'end_time_monday',
            'active_tuesday', 'start_time_tuesday', 'end_time_tuesday',
            'active_wednesday', 'start_time_wednesday', 'end_time_wednesday',
            'active_thursday', 'start_time_thursday', 'end_time_thursday',
            'active_friday', 'start_time_friday', 'end_time_friday',
            'active_saturday', 'start_time_saturday', 'end_time_saturday',
            'active_sunday', 'start_time_sunday', 'end_time_sunday'
        ];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || trim($data[$field]) === '');
        if (!empty($missingFields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missingFields)
            ]);
            return;
        }

        try{
            $success = $this->scheduleModel->updateSchedule(
                (int)$data['schedule_id'],
                (int)$data['lab_id'],
                (bool)$data['active_monday'], trim($data['start_time_monday']), trim($data['end_time_monday']),
                (bool)$data['active_tuesday'], trim($data['start_time_tuesday']), trim($data['end_time_tuesday']),
                (bool)$data['active_wednesday'], trim($data['start_time_wednesday']), trim($data['end_time_wednesday']),
                (bool)$data['active_thursday'], trim($data['start_time_thursday']), trim($data['end_time_thursday']),
                (bool)$data['active_friday'], trim($data['start_time_friday']), trim($data['end_time_friday']),
                (bool)$data['active_saturday'], trim($data['start_time_saturday']), trim($data['end_time_saturday']),
                (bool)$data['active_sunday'], trim($data['start_time_sunday']), trim($data['end_time_sunday'])
            );
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "Schedule updated successfully" : "Schedule was not updated"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while updating the schedule"
            ]);
            error_log("Schedule update error: " . $e->getMessage());
        }
    }

    public function deleteSchedule(): void
    {
        $shcedule_id = $_GET['schedule_id'] ?? null;
        if ($shcedule_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing schedule_id"
            ]);
            return;
        }
        try {
            $success = $this->scheduleModel->deleteSchedule((int)$shcedule_id);
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "Schedule deleted successfully" : "Schedule was not deleted"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while deleting the schedule"
            ]);
            error_log("Schedule deletion error: " . $e->getMessage());
        }
    }

    public function getById(): void
    {
        $schedule_id = $_GET['schedule_id'] ?? null;
        if ($schedule_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing schedule_id"
            ]);
            return;
        }
        try {
            $schedule = $this->scheduleModel->getById((int)$schedule_id);
            if ($schedule) {
                echo json_encode([
                    "status" => "success",
                    "data" => $schedule
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Schedule not found"
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while fetching the schedule"
            ]);
            error_log("Schedule fetch error: " . $e->getMessage());
        }
    }

    public function getByLabId(): void
    {
        $lab_id = $_GET['lab_id'] ?? null;
        if ($lab_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing lab_id"
            ]);
            return;
        }
        try {
            $schedules = $this->scheduleModel->getByLabId((int)$lab_id);
            if ($schedules) {
                echo json_encode([
                    "status" => "success",
                    "data" => $schedules
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No schedules found for this lab"
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while fetching the schedules"
            ]);
            error_log("Schedule fetch error: " . $e->getMessage());
        }
    }
}