<?php
require __DIR__ . "/../models/User.php";
require __DIR__ . "/../../config/db.php";

class UserController
{
    private $userModel;

    public function __construct($pdo)
    {
        $this->userModel = new User($pdo);
    }

    // Helper Image Validation
    private function validateImage($image, $maxMB): bool
    {
        $maxSize = $maxMB * 1024 * 1024;
        $image_data = base64_decode($image);

        if ($image_data === false) {
            return false;
        }

        if (strlen($image_data) > $maxSize) {
            return false;
        }

        $image_info = getimagesizefromstring($image_data);

        $allowed_formats = ['image/jpeg', 'image/png', 'image/gif'];
        if ($image_info === false || !in_array($image_info['mime'], $allowed_formats)) {
            return false;
        }

        return true;
    }

    // Get Bearer Token
    private function getBearerToken(): ?string
    {
        $headers = getallheaders();
        if (empty($headers['Authorization'])) {
            return null;
        }

        $authParts = explode(" ", $headers['Authorization']);
        if (count($authParts) !== 2 || strtolower($authParts[0]) !== 'bearer') {
            return null;
        }

        return $authParts[1];
    }

    // Create User
    public function createUser(): void
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

        $requiredFields = ['username', 'first_name', 'last_name', 'birth_date', 'password', 'institution', 'campus', 'student_carreer', 'email', 'phone'];
        $missingFields = array_filter($requiredFields, fn($field) => !isset($data[$field]) || trim($data[$field]) === '');

        if (!empty($missingFields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missingFields)
            ]);
            return;
        }

        try {
            $success = $this->userModel->createUser(
                $data['username'],
                $data['first_name'],
                $data['last_name'],
                $data['birth_date'],
                $data['password'],
                $data['institution'],
                $data['campus'],
                $data['student_carreer'],
                $data['email'],
                $data['phone']
            );
            echo json_encode([
                "status" => $success ? "success" : "error",
                "message" => $success ? "User created successfully" : "User was not created"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred while creating the user"
            ]);
            error_log("User creation error: " . $e->getMessage());
        }
    }

    // Login User
    public function loginUser(): void
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validate required fields
        if (empty($data['identifier']) || empty($data['password'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing identifier or password"
            ]);
            return;
        }

        try {
            // Attempt to login the user
            $user = $this->userModel->loginUser($data['identifier'], $data['password']);

            if ($user !== null) {
                // Respond with success message and user data
                echo json_encode([
                    "status" => "success",
                    "message" => "Login successful",
                    "data" => $user
                ]);
            } else {
                // Respond with error for invalid credentials
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid username or password"
                ]);
            }
        } catch (Exception $e) {
            // Handle any exceptions that occur during the login process
            echo json_encode([
                "status" => "error",
                "message" => "An error occurred: " . $e->getMessage()
            ]);
        }
    }

    // Verify Token
    public function verifyToken(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id"
            ]);
            return;
        }

        $status = $this->userModel->verifyToken($data['user_id'], $session_token);

        echo json_encode([
            "status" => $status ? "success" : "error",
            "message" => $status ? "Valid session token" : "Invalid user_id or session_token"
        ]);
    }

    // Get User
    public function getUser(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id"
            ]);
            return;
        }

        $user = $this->userModel->getUserById($data['user_id'], $session_token);

        echo json_encode($user !== null ? [
            "status" => "success",
            "message" => "User retrieved successfully",
            "data" => $user
        ] : [
            "status" => "error",
            "message" => "User not found or session_token not valid"
        ]);
    }

    // Update Password
    public function updatePassword(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id"
            ]);
            return;
        }

        if (empty($data['password'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing password"
            ]);
            return;
        }

        $user = $this->userModel->updatePassword($data['user_id'], $data['password'], $session_token);

        echo json_encode($user ? [
            "status" => "success",
            "message" => "User password updated successfully"
        ] : [
            "status" => "error",
            "message" => "User password was not updated"
        ]);
    }

    public function updateFirstName(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['first_name'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or first_name"
            ]);
            return;
        }

        $updated = $this->userModel->updateFirstName($data['user_id'], $data['first_name'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "First name updated successfully"
        ] : [
            "status" => "error",
            "message" => "First name was not updated"
        ]);
    }

    public function updateLastName(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['last_name'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or last_name"
            ]);
            return;
        }

        $updated = $this->userModel->updateLastName($data['user_id'], $data['last_name'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Last name updated successfully"
        ] : [
            "status" => "error",
            "message" => "Last name was not updated"
        ]);
    }

    public function updateBirthDate(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['birth_date'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or birth_date"
            ]);
            return;
        }

        $updated = $this->userModel->updateBirthDate($data['user_id'], $data['birth_date'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Birth date updated successfully"
        ] : [
            "status" => "error",
            "message" => "Birth date was not updated"
        ]);
    }

    public function updateInstitution(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['institution'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or institution"
            ]);
            return;
        }

        $updated = $this->userModel->updateInstitution($data['user_id'], $data['institution'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Institution updated successfully"
        ] : [
            "status" => "error",
            "message" => "Institution was not updated"
        ]);
    }

    public function updateCampus(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['campus'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or campus"
            ]);
            return;
        }

        $updated = $this->userModel->updateCampus($data['user_id'], $data['campus'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Campus updated successfully"
        ] : [
            "status" => "error",
            "message" => "Campus was not updated"
        ]);
    }

    public function updateStudentCode(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['student_code'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or student_code"
            ]);
            return;
        }

        $updated = $this->userModel->updateStudentCode($data['user_id'], $data['student_code'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Student code updated successfully"
        ] : [
            "status" => "error",
            "message" => "Student code was not updated"
        ]);
    }

    public function updateStudentCarreer(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['student_carreer'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or student_carreer"
            ]);
            return;
        }

        $updated = $this->userModel->updateStudentCarreer($data['user_id'], $data['student_carreer'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Student carreer updated successfully"
        ] : [
            "status" => "error",
            "message" => "Student carreer was not updated"
        ]);
    }

    public function updatePhone(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['phone'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or phone"
            ]);
            return;
        }

        $updated = $this->userModel->updatePhone($data['user_id'], $data['phone'], $session_token);

        echo json_encode($updated ? [
            "status" => "success",
            "message" => "Phone updated successfully"
        ] : [
            "status" => "error",
            "message" => "Phone was not updated"
        ]);
    }

    // Update Email
    public function updateEmail(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id"
            ]);
            return;
        }

        if (empty($data['email'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing email"
            ]);
            return;
        }

        $user = $this->userModel->updateEmail($data['user_id'], $data['email'], $session_token);

        echo json_encode($user ? [
            "status" => "success",
            "message" => "User email updated successfully"
        ] : [
            "status" => "error",
            "message" => "User email was not updated"
        ]);
    }

    // Verify Email
    public function verifyEmail(): void
    {
        $email = $_GET['email'] ?? null;

        if (empty($email)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing email value"
            ]);
            return;
        }

        $status = $this->userModel->emailInUse($email);

        echo json_encode([
            "status" => $status ? "error" : "success",
            "message" => $status ? "This email is already in use" : "This is a valid email to use"
        ]);
    }

    // Verify Username
    public function verifyUsername(): void
    {
        $username = $_GET['username'] ?? null;

        if (empty($username)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing username value"
            ]);
            return;
        }

        $status = $this->userModel->usernameInUse($username);

        echo json_encode([
            "status" => $status ? "error" : "success",
            "message" => $status ? "This username is already in use" : "This is a valid username to use"
        ]);
    }

    // Update profile image
    public function updateProfileImage(): void
    {
        $session_token = $this->getBearerToken();

        if (empty($session_token)) {
            echo json_encode([
                "status" => "error",
                "message" => "Empty session token"
            ]);
            return;
        }

        $data = json_decode(file_get_contents("php://input"), true);

        if (empty($data['user_id']) || empty($data['profile_image'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing user_id or profile_image"
            ]);
            return;
        }

        if (!$this->validateImage($data['profile_image'], 1)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid image format or size"
            ]);
            return;
        }

        $success = $this->userModel->updateProfileImage($data['user_id'], $session_token, $data['profile_image']);

        echo json_encode([
            "status" => $success ? "success" : "error",
            "message" => $success ? "Profile image updated successfully" : "Profile image was not updated"
        ]);
    }
}
