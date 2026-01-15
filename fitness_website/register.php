<?php

require_once 'config/database.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$page_title = 'რეგისტრაცია';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = clean($_POST['username']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    
    if (empty($username)) {
        $errors[] = 'მომხმარებლის სახელი აუცილებელია';
    } elseif (strlen($username) < 3) {
        $errors[] = 'მომხმარებლის სახელი უნდა იყოს მინიმუმ 3 სიმბოლო';
    }
    
    if (empty($email)) {
        $errors[] = 'ელ-ფოსტა აუცილებელია';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ელ-ფოსტის ფორმატი არასწორია';
    }
    
    if (empty($password)) {
        $errors[] = 'პაროლი აუცილებელია';
    } elseif (strlen($password) < 6) {
        $errors[] = 'პაროლი უნდა იყოს მინიმუმ 6 სიმბოლო';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'პაროლები არ ემთხვევა';
    }
    
    if (empty($errors)) {
        
        $check_sql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = 'ეს მომხმარებელი ან ელ-ფოსტა უკვე არსებობს';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $insert_sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')";
            $stmt = mysqli_prepare($conn, $insert_sql);
            mysqli_stmt_bind_param($stmt, "sss", $username, $email, $hashed_password);
            
            if (mysqli_stmt_execute($stmt)) {
                show_message('რეგისტრაცია წარმატებით დასრულდა! შეგიძლიათ შეხვიდეთ', 'success');
                redirect('login.php');
            } else {
                $errors[] = 'რეგისტრაცია ვერ მოხერხდა, სცადეთ თავიდან';
            }
        }
        
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card card">
        <h2 class="text-center">რეგისტრაცია</h2>
        <p class="text-center" style="color: #6B7280; margin-bottom: 2rem;">
            შექმენი ანგარიში და დაიწყე ვარჯიში
        </p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            
            <div class="form-group">
                <label for="username">მომხმარებლის სახელი *</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control" 
                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="email">ელ-ფოსტა *</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">პაროლი * (მინიმუმ 6 სიმბოლო)</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="confirm_password">გაიმეორეთ პაროლი *</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    class="form-control"
                    required
                >
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">
                რეგისტრაცია
            </button>
        </form>
        
        <p class="text-center" style="margin-top: 1.5rem;">
            უკვე გაქვს ანგარიში? 
            <a href="login.php" style="color: var(--primary-color); font-weight: 600;">შესვლა</a>
        </p>
    </div>
</div>

<style>
    .auth-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 60vh;
        padding: 2rem 0;
    }
    
    .auth-card {
        max-width: 450px;
        width: 100%;
    }
</style>

<?php include 'includes/footer.php'; ?>