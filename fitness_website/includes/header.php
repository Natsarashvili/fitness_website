<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ka">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>FitLife - ფიტნეს ინსტრუქციები</title>
    

    <link rel="stylesheet" href="css/style.css">
    

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Georgian:wght@300;400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    

    <nav class="navbar">
        <div class="container">
            <div class="nav-wrapper">
                

                <a href="index.php" class="logo">
                    💪 <span>FitLife</span>
                </a>
                

                <ul class="nav-menu">
                    <li><a href="index.php" class="nav-link">მთავარი</a></li>
                    <li><a href="workouts.php" class="nav-link">ვარჯიშები</a></li>
                    <li><a href="search.php" class="nav-link">ძებნა</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>

                        <li><a href="profile.php" class="nav-link">ჩემი პროფილი</a></li>
                        
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>

                            <li><a href="admin/index.php" class="nav-link admin-link">ადმინ პანელი</a></li>
                        <?php endif; ?>
                        
                        <li><a href="logout.php" class="nav-link logout-link">გასვლა</a></li>
                        <li><span class="user-greeting">გამარჯობა, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span></li>
                    <?php else: ?>

                        <li><a href="login.php" class="nav-link">შესვლა</a></li>
                        <li><a href="register.php" class="btn-primary">რეგისტრაცია</a></li>
                    <?php endif; ?>
                </ul>
                

                <button class="mobile-menu-toggle" id="mobileMenuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>
    

    <main class="main-content">
        <div class="container">
            
            <?php
            if (isset($_SESSION['message'])) {
                $type = $_SESSION['message_type'] ?? 'success';
                $message = $_SESSION['message'];
                $class = ($type === 'success') ? 'alert-success' : 'alert-error';
                
                echo "<div class='alert $class'>$message</div>";
                
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>