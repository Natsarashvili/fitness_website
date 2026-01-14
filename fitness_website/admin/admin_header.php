<?php
/**
 * ადმინ პანელის Header
 * 
 * ცალკე header ადმინისთვის - სწორი CSS/JS ბმულებით
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// შემოწმება - ადმინია თუ არა
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FitLife - ადმინ პანელი</title>
    
    <!-- CSS სტილები (../ რადგან admin საქაღალდეშია) -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Google Fonts (ქართული შრიფტი) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    
    <!-- ადმინის ნავიგაცია -->
    <nav class="navbar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="container">
            <div class="nav-wrapper">
                
                <!-- ლოგო -->
                <a href="index.php" class="logo" style="color: white;">
                    👑 <span>ადმინ პანელი</span>
                </a>
                
                <!-- ადმინის მენიუ -->
                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link" style="color: white;">📊 Dashboard</a></li>
                    <li><a href="workouts.php" class="nav-link" style="color: white;">💪 ვარჯიშები</a></li>
                    <li><a href="exercises.php" class="nav-link" style="color: white;">🏃 სავარჯიშოები</a></li>
                    <li><a href="categories.php" class="nav-link" style="color: white;">📁 კატეგორიები</a></li>
                    <li><a href="instructors.php" class="nav-link" style="color: white;">👨‍🏫 ინსტრუქტორები</a></li>
                    <li><a href="users.php" class="nav-link" style="color: white;">👥 მომხმარებლები</a></li>
                    
                    <li style="border-left: 1px solid rgba(255,255,255,0.3); margin-left: 1rem; padding-left: 1rem;">
                        <a href="../index.php" class="nav-link" style="color: white;">🏠 საიტი</a>
                    </li>
                    <li><a href="../logout.php" class="nav-link logout-link" style="color: #FEE2E2;">გასვლა</a></li>
                    <li><span class="user-greeting" style="color: white;">👑 <?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
                </ul>
                
                <!-- მობილურის ღილაკი -->
                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span style="background: white;"></span>
                    <span style="background: white;"></span>
                    <span style="background: white;"></span>
                </button>
            </div>
        </div>
    </nav>
    
    <!-- მთავარი კონტენტის დასაწყისი -->
    <main class="main-content">
        <div class="container">
            
            <?php
            // შეტყობინებების გამოტანა
            if (isset($_SESSION['message'])) {
                $type = $_SESSION['message_type'] ?? 'success';
                $message = $_SESSION['message'];
                $class = ($type === 'success') ? 'alert-success' : 'alert-error';
                
                echo "<div class='alert $class'>$message</div>";
                
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>