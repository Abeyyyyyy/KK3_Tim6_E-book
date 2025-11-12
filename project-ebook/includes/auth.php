<?php
// Pastikan session_start() hanya dipanggil sekali
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class Auth {
    private $db;
    private $conn;

    public function __construct() {
        require_once 'database.php';
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }

    public function login($identifier, $password) {
        try {
            // Bisa login dengan username atau email
            $query = "SELECT id, username, password, role, full_name FROM users WHERE username = :identifier OR email = :identifier";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":identifier", $identifier);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Check password (for demo, use 'password' for all accounts)
                if ($password === "password" || password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['full_name'] = $user['full_name'];
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public function redirectIfNotLoggedIn() {
        if (!$this->isLoggedIn()) {
            header("Location: ../login.php");
            exit();
        }
    }

    public function logout() {
        session_destroy();
        header("Location: ../login.php");
        exit();
    }

    public function hasRole($allowedRoles) {
        if (!is_array($allowedRoles)) {
            $allowedRoles = [$allowedRoles];
        }
        return isset($_SESSION['role']) && in_array($_SESSION['role'], $allowedRoles);
    }

    public function redirectIfNotAuthorized($allowedRoles) {
        $this->redirectIfNotLoggedIn();
        if (!$this->hasRole($allowedRoles)) {
            header("Location: ../unauthorized.php");
            exit();
        }
    }
}
?>