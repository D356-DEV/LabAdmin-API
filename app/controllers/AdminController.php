<?php

require __DIR__ . '/../models/Admin.php';
require __DIR__ . '/../../config/db.php';

class AdminController
{
    private $adminModel;

    public function __construct($pdo)
    {
        $this->adminModel = new Admin($pdo);
    }

    public function isUserAdmin(): void
    {
        if (empty($_GET['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The user_id is required"
            ]);
            return;
        }

        $user_id = (int) trim($_GET['user_id']);

        try {
            $success = $this->adminModel->isUserAdmin($user_id);
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "The user_id is associated to an admin register" : "User is not an admin"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An unexpected error occurred"
            ]);
        }
    }

    public function getAdminByUser(): void
    {
        if (empty($_GET['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The user_id is required"
            ]);
            return;
        }

        $user_id = (int) trim($_GET['user_id']);

        try {
            $admin = $this->adminModel->getAdminByUser($user_id);

            if ($admin) {
                echo json_encode([
                    "status" => "success",
                    "message" => "Admin data retrieved successfully",
                    "data" => $admin
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "No admin found with the provided user_id"
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An unexpected error occurred"
            ]);
        }
    }
}