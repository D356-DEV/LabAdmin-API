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
        empty($data['creator_id']) ||
        empty($data['manager_id'])
    ) {
        http_response_code(400);
        echo json_encode(["error" => "All fields are required."]);
        return;
    }

    $name = htmlspecialchars(strip_tags($data['name']));
    $location = htmlspecialchars(strip_tags($data['location']));
    $capacity = (int) htmlspecialchars(strip_tags($data['capacity']));
    $description = htmlspecialchars(strip_tags($data['description']));
    $institution = htmlspecialchars(strip_tags($data['institution']));
    $campus = htmlspecialchars(strip_tags($data['campus']));
    $specialization = htmlspecialchars(strip_tags($data['specialization']));
    $creator_id = (int) htmlspecialchars(strip_tags($data['creator_id']));
    $manager_id = (int) htmlspecialchars(strip_tags($data['manager_id']));



    if ($success = $this->labModel->createLab() {
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

}