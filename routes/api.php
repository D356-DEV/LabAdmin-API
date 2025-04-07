<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once __DIR__ . "/../app/controllers/UserController.php";
require_once __DIR__ . "/../app/controllers/AdminController.php";
require_once __DIR__ . "/../app/controllers/LabController.php";
require_once __DIR__ . "/../app/controllers/BotController.php";
require_once __DIR__ . "/../app/controllers/ChatBot.php";


// Get request method and path
$method = $_SERVER['REQUEST_METHOD'];
$path = explode("?", $_SERVER['REQUEST_URI'], 2)[0];
$segments = explode("/", trim($path, "/"));

// Controller´s instances 
$userController = new UserController($pdo);
$adminController = new AdminController($pdo);
$labController = new LabController($pdo);
$botController = new BotController($pdo);
$chatBot = new ChatBot();

switch ($segments[0]) {

    // USERS ENDPOINTS
    case "users":
        if (!isset($segments[1])) {
            echo json_encode(["status" => "error", "message" => "Missing users action"]);
            exit;
        }
        switch ($method) {
            case "GET":
                if ($segments[1] === "verify_email") {
                    $userController->verifyEmail();
                } elseif ($segments[1] === "verify_username") {
                    $userController->verifyUsername();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid users GET action"]);
                }
                break;

            case "POST":
                if ($segments[1] === "create_user") {
                    $userController->createUser();
                } elseif ($segments[1] === "login_user") {
                    $userController->loginUser();
                } elseif ($segments[1] === "verify_token") {
                    $userController->verifyToken();
                } elseif ($segments[1] === "get_user") {
                    $userController->getUser();
                } elseif ($segments[1] === "update_password") {
                    $userController->updatePassword();
                } elseif ($segments[1] === "update_email") {
                    $userController->updateEmail();
                } elseif ($segments[1] === "profile_image") {
                    $userController->updateProfileImage();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid users POST action"]);
                }
                break;

            default:
                echo json_encode(["status" => "error", "message" => "Invalid method for users endpoint"]);
        }
        break;

    case "admins":
        if (!isset($segments[1])) {
            echo json_encode(["status" => "error", "message" => "Missing admins action"]);
            exit;
        }
        switch ($method) {
            case "GET":
                if ($segments[1] === "is_admin") {
                    $adminController->isUserAdmin();
                } elseif ($segments[1] === "get_by_user") {
                    $adminController->getAdminByUser();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid admins GET action"]);
                }
                break;
            default:
                echo json_encode(["status" => "error", "message" => "Invalid method for admins endpoint"]);
        }
        break;

    // LABS ENDPOINTS
    case "labs":
        if (!isset($segments[1])) {
            echo json_encode(["status" => "error", "message" => "Missing labs action"]);
            exit;
        }
        switch ($method) {
            case "GET":
                if ($segments[1] === "get_labs") {
                    $labController->getAllLabs();
                } elseif ($segments[1] === "get_lab") {
                    $labController->getLabById();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid labs GET action"]);
                }
                break;

            case "POST":
                if ($segments[1] === "create_lab") {
                    $labController->createLab();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid labs POST action"]);
                }
                break;

            default:
                echo json_encode(["status" => "error", "message" => "Invalid method for labs endpoint"]);
        }
        break;

    // BOT ENDPOINTS
    case "bot":
        if (!isset($segments[1])) {
            echo json_encode(["status" => "error", "message" => "Missing bot action"]);
            exit;
        }
        switch ($method) {
            case "GET":
                if ($segments[1] === "get_dataset") {
                    $botController->getBotDataset();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid bot GET action"]);
                }
                break;
            case "POST":
                if ($segments[1] === "handle_question") {
                    $chatBot->handleQuestion();
                } else {
                    echo json_encode(["status" => "error", "message" => "Invalid bot POST action"]);
                }
                break;
            default:
                echo json_encode(["status" => "error", "message" => "Invalid method for bot endpoint"]);
        }
        break;
    // DEFAULT CASE
    default:
        echo json_encode(["status" => "success", "message" => "You are reaching the LabAdmin API!"]);
        break;
}
