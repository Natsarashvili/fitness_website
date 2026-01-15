<?php


require_once 'config/database.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    redirect('index.php');
}

$page_title = 'შესვლა';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'გთხოვთ შეავსოთ ყველა ველი';
    } else {
        
        $sql = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $username, $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password'])) {
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                
                show_message('კეთილი იყოს თქვენი მობრძანება, ' . $user['username'] . '!', 'success');
                
                if ($user['role'] === 'admin') {
                    redirect('admin/index.php');
                } else {
                    redirect('index.php');
                }
                
            } else {
                $error = 'პაროლი არასწორია';
            }
        } else {
            $error = 'მომხმარებელი ვერ მოიძებნა';
        }
        
        mysqli_stmt_close($stmt);
    }
}

include 'includes/header.php';
?>

<div class="auth-container">
    <div class="auth-card card">
        <h2 class="text-center">შესვლა</h2>
        <p class="text-center" style="color: #6B7280; margin-bottom: 2rem;">
            შედი შენს ანგარიშში
        </p>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            
            <div class="form-group">
                <label for="username">მომხმარებლის სახელი ან ელ-ფოსტა *</label>
                <input 
                    type="text" 
                    id="username" 
                    name="username" 
                    class="form-control"
                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                    required
                    autofocus
                >
            </div>
            
            <div class="form-group">
                <label for="password">პაროლი *</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control"
                    required
                >
            </div>
            
            <button type="submit" class="btn-primary" style="width: 100%;">
                შესვლა
            </button>
        </form>
        
        <p class="text-center" style="margin-top: 1.5rem;">
            არ გაქვს ანგარიში? 
            <a href="register.php" style="color: var(--primary-color); font-weight: 600;">რეგისტრაცია</a>
        </p>
        
        <div style="margin-top: 2rem; padding: 1rem; background: #F3F4F6; border-radius: 6px; font-size: 0.9rem;">
            <strong>ტესტისთვის:</strong><br>
            ადმინი: <code>admin</code> / <code>admin123</code>
        </div>
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
    
    code {
        background: #E5E7EB;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
</style>

<?php include 'includes/footer.php'; ?>