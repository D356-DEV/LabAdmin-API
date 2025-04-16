<?php
class User
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // Create a new user
    public function createUser(
        $username,
        $first_name,
        $last_name,
        $birth_date,
        $password,
        $institution,
        $campus,
        $student_carreer,
        $email,
        $phone
    ): bool {
        // Check if the user already exists
        $stmt_check = $this->pdo->prepare("SELECT user_id FROM users WHERE email = ? OR username = ?");
        $stmt_check->execute([$email, $username]);

        if ($stmt_check->fetch()) {
            return false;
        }

        // Hash the password securely
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $this->pdo->beginTransaction();

            // Insert the new user
            $stmt = $this->pdo->prepare(
                "INSERT INTO users (username, first_name, last_name, birth_date, password_hash, institution, campus, student_carreer, email, phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );

            $stmt->execute([$username, $first_name, $last_name, $birth_date, $password_hash, $institution, $campus, $student_carreer, $email, $phone]);

            // Commit transaction
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    // Login a user
    public function loginUser($identifier, $password): ?array
    {
        $stmt = $this->pdo->prepare("SELECT user_id, password_hash FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        // Verify password
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return null;
        }

        // Generate new session token
        $new_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+1 month'));

        // Update session token in database
        $stmt = $this->pdo->prepare("UPDATE users SET session_token = ?, token_expiry = ? WHERE user_id = ?");

        if ($stmt->execute([$new_token, $token_expiry, $user['user_id']])) {
            return [
                'user_id' => $user['user_id'],
                'session_token' => $new_token,
                'token_expiry' => $token_expiry
            ];
        } else {
            return null;
        }
    }

    //Verify a user's session token
    public function verifyToken($user_id, $session_token): bool
    {
        $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE user_id = ? AND session_token = ? AND token_expiry > NOW()");
        $stmt->execute([$user_id, $session_token]);
        return $stmt->fetch() ? true : false;
    }

    // Get a user by ID
    public function getUserById($user_id, $session_token): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = ? AND session_token = ? AND token_expiry > NOW()");
        $stmt->execute([$user_id, $session_token]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    // Update Password
    public function updatePassword(int $user_id, string $password, string $session_token): bool
    {
        $password = trim($password);
        if (strlen($password) < 8)
            return false;

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':password_hash' => $password_hash,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Password No Token
    public function updatePasswordNoToken(string $email, string $password_hash): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :password_hash WHERE email = :email");
        $stmt->execute([
            ':email' => $email,
            ':password_hash' => $password_hash
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Email
    public function updateEmail(int $user_id, string $email, string $session_token): bool
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET email = :email WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':email' => $email,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update First Name
    public function updateFirstName(int $user_id, string $first_name, string $session_token): bool
    {
        $first_name = trim($first_name);
        if (empty($first_name))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET first_name = :first_name WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':first_name' => $first_name,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Last Name
    public function updateLastName(int $user_id, string $last_name, string $session_token): bool
    {
        $last_name = trim($last_name);
        if (empty($last_name))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET last_name = :last_name WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':last_name' => $last_name,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Birth Date
    public function updateBirthDate(int $user_id, string $birth_date, string $session_token): bool
    {
        $birth_date = trim($birth_date);
        if (empty($birth_date))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET birth_date = :birth_date WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':birth_date' => $birth_date,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Institution
    public function updateInstitution(int $user_id, string $institution, string $session_token): bool
    {
        $institution = trim($institution);
        if (empty($institution))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET institution = :institution WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':institution' => $institution,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Campus
    public function updateCampus(int $user_id, string $campus, string $session_token): bool
    {
        $campus = trim($campus);
        if (empty($campus))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET campus = :campus WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':campus' => $campus,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Student Code
    public function updateStudentCode(int $user_id, string $student_code, string $session_token): bool
    {
        $student_code = trim($student_code);
        if (empty($student_code))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET student_code = :student_code WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':student_code' => $student_code,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Student Carreer
    public function updateStudentCarreer(int $user_id, string $student_carreer, string $session_token): bool
    {
        $student_carreer = trim($student_carreer);
        if (empty($student_carreer))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET student_carreer = :student_carreer WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':student_career' => $student_carreer,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Update Phone
    public function updatePhone(int $user_id, string $phone, string $session_token): bool
    {
        $phone = trim($phone);
        if (empty($phone))
            return false;

        $stmt = $this->pdo->prepare("UPDATE users SET phone = :phone WHERE user_id = :user_id AND session_token = :session_token");
        $stmt->execute([
            ':phone' => $phone,
            ':user_id' => $user_id,
            ':session_token' => $session_token
        ]);

        return $stmt->rowCount() > 0;
    }

    // Is email already in use?
    public function emailInUse($email): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() ? true : false;
    }

    // Get email by usrname
    public function getEmailByUsername($username): ?string
    {
        $stmt = $this->pdo->prepare("SELECT email FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $email = $stmt->fetch();
        return $email ? $email['email'] : null;
    }

    // Is username already in use?
    public function usernameInUse($username): bool
    {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        return $stmt->fetch() ? true : false;
    }

    // Update user Profile Image
    public function updateProfileImage($user_id, $session_token, $profile_image): bool
    {
        $stmt = $this->pdo->prepare("UPDATE users SET profile_image = ? WHERE user_id = ? AND session_token = ? AND token_expiry > NOW()");
        return $stmt->execute([$profile_image, $user_id, $session_token]) ?? false;
    }

}
