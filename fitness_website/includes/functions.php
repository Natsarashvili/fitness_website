<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}


function redirect($page) {
    header("Location: $page");
    exit();
}


function show_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}


function display_message() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'];
        $message = $_SESSION['message'];
        
        $class = ($type === 'success') ? 'alert-success' : 'alert-danger';
        
        echo "<div class='alert $class'>$message</div>";
        
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
}


function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}


function verify_password($password, $hash) {
    return password_verify($password, $hash);
}


function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}


function upload_file($file, $target_dir) {

    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $max_size = 5 * 1024 * 1024; // 
    

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'ფაილის ატვირთვისას მოხდა შეცდომა'];
    }
    
    if ($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'ფაილი ძალიან დიდია (მაქს 5MB)'];
    }
    
    if (!in_array($file['type'], $allowed_types)) {
        return ['success' => false, 'message' => 'დაშვებულია მხოლოდ სურათები (JPG, PNG, GIF)'];
    }
    

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_path = $target_dir . '/' . $filename;
    

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return ['success' => true, 'filename' => $filename];
    }
    
    return ['success' => false, 'message' => 'ფაილის შენახვა ვერ მოხერხდა'];
}


function sanitize_search($search) {
    global $conn;
    return mysqli_real_escape_string($conn, clean($search));
}


function get_difficulty_label($level) {
    $labels = [
        'beginner' => 'დამწყები',
        'intermediate' => 'საშუალო',
        'advanced' => 'მოწინავე'
    ];
    return $labels[$level] ?? $level;
}


function format_duration($minutes) {
    if ($minutes < 60) {
        return $minutes . ' წთ';
    }
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . 'სთ ' . ($mins > 0 ? $mins . 'წთ' : '');
}


function display_rating($rating) {
    $output = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= '⭐';
        } else {
            $output .= '☆';
        }
    }
    return $output;
}


function require_admin() {
    if (!is_admin()) {
        show_message('ამ გვერდზე წვდომა მხოლოდ ადმინისთვისაა', 'error');
        redirect('../index.php');
    }
}


function require_login() {
    if (!is_logged_in()) {
        show_message('გთხოვთ შეხვიდეთ სისტემაში', 'error');
        redirect('login.php');
    }
}
?>