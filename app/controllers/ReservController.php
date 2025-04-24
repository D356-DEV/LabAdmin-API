<?php
require __DIR__ . '/../models/Reserv.php';
require __DIR__ . '/../../config/db.php';

class ReservController
{
    private $reservModel;

    public function __construct($pdo)
    {
        $this->reservModel = new Reserv($pdo);
    }

    public function createReserv(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!is_array($data)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON format"
            ]);
            return;
        }

        $requiredFields = ['reserv_date', 'start_time', 'end_time', 'lab_id', 'user_id', 'description'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || trim($data[$field]) === '');

        if (!empty($missingFields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missingFields)
            ]);
            return;
        }

        try {
            $success = $this->reservModel->createReserv(
                trim($data['reserv_date']),
                trim($data['start_time']),
                trim($data['end_time']),
                (int) $data['lab_id'],
                (int) $data['user_id'],
                trim($data['description']),
            );
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "Reserv created successfully" : "Reserv was not created"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while creating the reserv"
            ]);
            error_log("Reserv creation error: " . $e->getMessage());
        }
    }

    public function acceptReserv(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!isset($data['reserv_id'], $data['admin_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing reserv_id or admin_id"
            ]);
            return;
        }

        $success = $this->reservModel->acceptReserv((int) $data['reserv_id'], (int) $data['admin_id']);

        echo json_encode([
            "status" => $success ? "success" : "error",
            "message" => $success ? "Reserv accepted" : "Could not accept reserv"
        ]);
    }

    public function rejectReserv(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!isset($data['reserv_id'], $data['admin_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing reserv_id or admin_id"
            ]);
            return;
        }

        $success = $this->reservModel->rejectReserv((int) $data['reserv_id'], (int) $data['admin_id']);

        echo json_encode([
            "status" => $success ? "success" : "error",
            "message" => $success ? "Reserv rejected" : "Could not reject reserv"
        ]);
    }

    public function getReservById(): void
    {
        $id = $_GET['reserv_id'] ?? null;

        if (!$id) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing reserv_id"
            ]);
            return;
        }

        $reserv = $this->reservModel->getReservById((int) $id);

        echo json_encode([
            "status" => $reserv ? "success" : "error",
            "message" => $reserv ? "Reservation retrieved successfully" : "No reservation found",
            "data" => $reserv
        ]);
    }

    public function getAllReservs(): void
    {
        $reservs = $this->reservModel->getAllReservs();

        echo json_encode([
            "status" => $reservs ? "success" : "error",
            "message" => $reservs ? "Reservations retrieved successfully" : "No reservations found",
            "data" => $reservs
        ]);
    }

    public function getReservsByLabId(): void
    {
        $lab_id = $_GET['lab_id'] ?? null;

        if (!$lab_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing lab_id"
            ]);
            return;
        }

        $reservs = $this->reservModel->getReservsByLabId((int) $lab_id);

        echo json_encode([
            "status" => $reservs ? "success" : "error",
            "message" => $reservs ? "Reservations retrieved successfully" : "No reservations found",
            "data" => $reservs
        ]);
    }

    public function getReservsByUserId(): void
    {
        $user_id = $_GET['user_id'] ?? null;

        if (!$user_id) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id"
            ]);
            return;
        }

        $reservs = $this->reservModel->getReservsByUserId((int) $user_id);

        echo json_encode([
            "status" => $reservs ? "success" : "error",
            "message" => $reservs ? "Reservations retrieved successfully" : "No reservations found",
            "data" => $reservs
        ]);
    }

    public function getReservsByLabIdAndStatus(): void
    {
        $lab_id = $_GET['lab_id'] ?? null;
        $status = $_GET['status'] ?? null;

        if (!$lab_id || !$status) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing lab_id or status"
            ]);
            return;
        }

        $reservs = $this->reservModel->getReservsByLabIdAndStatus((int) $lab_id, $status);

        echo json_encode([
            "status" => $reservs ? "success" : "error",
            "message" => $reservs ? "Reservations retrieved successfully" : "No reservations found",
            "data" => $reservs
        ]);
    }

}