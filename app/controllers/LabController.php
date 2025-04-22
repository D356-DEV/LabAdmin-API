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

    // Get Creator Labs
    public function getCreatorLabs(): void
    {
        if (!isset($_GET['admin_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The admin_id is missing"
            ]);
            return;
        }

        $admin_id = (int) $_GET['admin_id'];

        if ($admin_id < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The admin_id must be greater than 0"
            ]);
            return;
        }

        $result = $this->labModel->getCreatorLabs($admin_id);

        if (!empty($result)) {
            echo json_encode([
                "status" => "success",
                "message" => "The creator labs have been retrieved successfully",
                "data" => $result
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No labs found for this admin_id"
            ]);
        }
    }

    // Update Lab's name
    public function updateName(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['name'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or name is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateName($data['lab_id'], $data['name']);

        if ($response) {
            echo json_encode([
                "status" => "success",
                "message" => "The Lab's name has been updated"
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "There was a problem updating the Lab's name."
            ]);
        }
    }

    // Update Lab's institution
    public function updateInstitution(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['institution'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or institution is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateInstitution((int) $data['lab_id'], $data['institution']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The institution has been updated"
                : "There was a problem updating the institution."
        ]);
    }

    // Update Lab's campus
    public function updateCampus(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['campus'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or campus is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateCampus((int) $data['lab_id'], $data['campus']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The campus has been updated"
                : "There was a problem updating the campus."
        ]);
    }

    // Update Lab's specialization
    public function updateSpecialization(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['specialization'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or specialization is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateSpecialization((int) $data['lab_id'], $data['specialization']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The specialization has been updated"
                : "There was a problem updating the specialization."
        ]);
    }

    // Update Lab's location
    public function updateLocation(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['location'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or location is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateLocation((int) $data['lab_id'], $data['location']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The location has been updated"
                : "There was a problem updating the location."
        ]);
    }

    // Update Lab's description
    public function updateDescription(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['description'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or description is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateDescription((int) $data['lab_id'], $data['description']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The description has been updated"
                : "There was a problem updating the description."
        ]);
    }

    // Update Lab's capacity
    public function updateCapacity(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || !isset($data['capacity'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or capacity is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateCapacity((int) $data['lab_id'], (int) $data['capacity']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The capacity has been updated"
                : "There was a problem updating the capacity."
        ]);
    }

    // Update Lab's admin ID
    public function updateAdminId(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || !isset($data['admin_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or admin_id is missing"
            ]);
            return;
        }

        if ((int) $data['lab_id'] < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id is not valid"
            ]);
            return;
        }

        $response = $this->labModel->updateAdminId((int) $data['lab_id'], (int) $data['admin_id']);

        echo json_encode([
            "status" => $response ? "success" : "error",
            "message" => $response
                ? "The admin ID has been updated"
                : "There was a problem updating the admin ID."
        ]);
    }

    // Delete Lab by lab_id and creator_id
    public function deleteLab(): void
    {
        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (empty($data['lab_id']) || empty($data['creator_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or creator_id is missing"
            ]);
            return;
        }

        $lab_id = (int) $data['lab_id'];
        $creator_id = (int) $data['creator_id'];

        if ($lab_id < 1 || $creator_id < 1) {
            echo json_encode([
                "status" => "error",
                "message" => "The lab_id or creator_id is not valid"
            ]);
            return;
        }

        $deleted = $this->labModel->deleteLab($lab_id, $creator_id);

        echo json_encode([
            "status" => $deleted ? "success" : "error",
            "message" => $deleted
                ? "The lab has been successfully deleted."
                : "Failed to delete the lab. Please check the lab_id and creator_id."
        ]);
    }

}
