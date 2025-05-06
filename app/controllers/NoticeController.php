<?php
require __DIR__ . '/../models/Notice.php';
require __DIR__ . '/../../config/db.php';

class NoticeController
{
    private $noticeModel;

    public function __construct($pdo)
    {
        $this->noticeModel = new Notice($pdo);
    }

    public function createNotice(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!is_array($data)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON format"
            ]);
            return;
        }

        $requiredFields = ['admin_id', 'lab_id', 'title', 'message'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || trim($data[$field]) === '');
        if (!empty($missingFields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missingFields)
            ]);
            return;
        }

        try {
            $success = $this->noticeModel->createNotice(
                (int) $data['admin_id'],
                (int) $data['lab_id'],
                trim($data['title']),
                trim($data['message'])
            );
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "Notice created successfully" : "Notice was not created"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while creating the notice"
            ]);
            error_log("Notice creation error: " . $e->getMessage());
        }
    }

    public function getNoticesByLabId(): void
    {
        $lab_id = $_GET['lab_id'] ?? null;

        if ($lab_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing lab_id parameter"
            ]);
            return;
        }

        try {
            $notices = $this->noticeModel->getByLabId((int) $lab_id);
            echo json_encode([
                "status" => "success",
                "message" => "Notices fetched successfully",
                "data" => $notices
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while fetching notices"
            ]);
            error_log("Fetch notices error: " . $e->getMessage());
        }
    }

    public function getNoticesByAdminId(): void
    {
        $admin_id = $_GET['admin_id'] ?? null;

        if ($admin_id === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing admin_id parameter"
            ]);
            return;
        }

        try {
            $notices = $this->noticeModel->getByAdminId((int) $admin_id);
            echo json_encode([
                "status" => "success",
                "message" => "Notices fetched successfully",
                "data" => $notices
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while fetching notices"
            ]);
            error_log("Fetch notices error: " . $e->getMessage());
        }
    }

    public function deleteNotice(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        if (!is_array($data)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON format"
            ]);
            return;
        }

        $requiredFields = ['admin_id', 'notice_id'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || trim($data[$field]) === '');
        if (!empty($missingFields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missingFields)
            ]);
            return;
        }

        try {
            $success = $this->noticeModel->deleteNotice(
                (int) $data['admin_id'],
                (int) $data['notice_id']
            );
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "Notice deleted successfully" : "Notice was not deleted"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while deleting the notice"
            ]);
            error_log("Delete notice error: " . $e->getMessage());
        }
    }
}