<?php
require __DIR__ . "/../models/Lab.php";
require __DIR__ . "/../../config/db.php";

class LabController
{
    private $labModel;

    public function __construct($pdo)
    {
        $this->labModel = new Lab($pdo);
    }

    // Create Lab
    public function createLab(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (
            empty($data['name']) ||
            empty($data['location']) ||
            empty($data['capacity']) ||
            empty($data['description']) ||
            empty($data['institution']) ||
            empty($data['campus']) ||
            empty($data['specialization']) ||
            empty($data['creator_id'])
        ) {
            http_response_code(400);
            echo json_encode(["error" => "All fields are required."]);
            return;
        }

        $name = htmlspecialchars(strip_tags($data['name']));
        $location = htmlspecialchars(strip_tags($data['location']));
        $capacity = (int) $data['capacity'];
        $description = htmlspecialchars(strip_tags($data['description']));
        $institution = htmlspecialchars(strip_tags($data['institution']));
        $campus = htmlspecialchars(strip_tags($data['campus']));
        $specialization = htmlspecialchars(strip_tags($data['specialization']));
        $creator_id = (int) $data['creator_id'];

        if ($success = $this->labModel->createLab($name, $location, $capacity, $description, $institution, $campus, $specialization, $creator_id)) {
            http_response_code(201);
            echo json_encode([
                "status" => "success",
                "message" => "Lab was created successfully",
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Failed to create lab."]);
        }
    }

    // Get all Labs
    public function getAllLabs(): void
    {
        $labs = $this->labModel->getAllLabs();
        if ($labs) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $labs,
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "No labs found.",
            ]);
        }
    }

    // Get Lab by ID
    public function getLabById(): void
    {
        $lab_id = $_GET['lab_id'] ?? null;

        if (empty($lab_id)) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Lab ID is required.",
            ]);
            return;
        }

        $lab_id = (int) $lab_id;
        
        if ($lab_id <= 0) {
            http_response_code(400);
            echo json_encode([
                "status" => "error",
                "message" => "Invalid Lab ID.",
            ]);
            return;
        }
        
        $lab = $this->labModel->getLabById($lab_id);
        
        if ($lab) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $lab,
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "Lab not found.",
            ]);
        }
    }
}
