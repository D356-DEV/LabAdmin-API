<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . "/../app/controllers/UserController.php";

// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = explode("?", $_SERVER['REQUEST_URI'], 2)[0];
$segments = explode("/", trim($path, "/"));

// Ensure at least one segment exists
if (!isset($segments[1])) {
    echo json_encode(["status" => "succes", "message" => "You are reaching the LabAdmin API."]);
    exit;
}

// Controller´s instances 
$userController = new UserController($pdo);

switch ($segments[1]) {

    // USERS ENDPOINTS
    case "users":
        if (!isset($segments[2])) {
            echo json_encode(["status" => "error", "message" => "Missing users action"]);
            exit;
        }
        switch ($method) {
            case "GET":
                if ($segments[2] === "verify_email") {
                    $userController->verifyEmail();
                } elseif ($segments[2] === "verify_username") {
                    $userController->verifyUsername();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid users GET action"]);
                }
                break;

            case "POST":
                if ($segments[2] === "create_user") {
                    $userController->createUser();
                } elseif ($segments[2] === "login_user") {
                    $userController->loginUser();
                } elseif ($segments[2] === "verify_token") {
                    $userController->verifyToken();
                } elseif ($segments[2] === "get_user") {
                    $userController->getUser();
                } elseif ($segments[2] === "profile_image") {
                    $userController->updateProfileImage();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid users POST action"]);
                }
                break;

            default:
                echo json_encode(["status" => "error", "message" => "Invalid method for users endpoint"]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid endpoint"]);
        break;
}
