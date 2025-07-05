<?php
function register_user($nama, $email, $password, $role, $conn) {
    $sql = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        return false;
    }

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);
    
    return $stmt_insert->execute();
}

function authenticate_user($email, $password, $conn) {
    $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return $user;
        }
    }
    return false;
}

function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
}

function redirect_if_logged_in() {
    if (isset($_SESSION['user_id'])) {
        redirect_based_on_role($_SESSION['role']);
    }
}

function redirect_based_on_role($role) {
    if ($role == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($role == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}
?>