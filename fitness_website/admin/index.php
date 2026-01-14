<?php
/**
 * ადმინის მთავარი გვერდი (Dashboard)
 * 
 * სტატისტიკა და სწრაფი წვდომა
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

// მხოლოდ ადმინებისთვის
require_admin();

$page_title = 'ადმინ პანელი';

// სტატისტიკა
$stats_sql = "
    SELECT 
        (SELECT COUNT(*) FROM workouts) as total_workouts,
        (SELECT COUNT(*) FROM exercises) as total_exercises,
        (SELECT COUNT(*) FROM categories) as total_categories,
        (SELECT COUNT(*) FROM instructors) as total_instructors,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
        (SELECT COUNT(*) FROM reviews) as total_reviews,
        (SELECT COUNT(*) FROM user_progress) as total_progress
";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

// უახლესი ვარჯიშები
$recent_workouts_sql = "SELECT * FROM workouts ORDER BY created_at DESC LIMIT 5";
$recent_workouts = mysqli_query($conn, $recent_workouts_sql);

// უახლესი მომხმარებლები
$recent_users_sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = mysqli_query($conn, $recent_users_sql);

include 'admin_header.php';
?>

<div class="admin-container">
    
    <div class="admin-admin_header">
        <h1>👑 ადმინისტრატორის პანელი</h1>
        <p style="color: #6B7280;">სისტემის მართვა და კონტროლი</p>
    </div>
    
    <!-- სტატისტიკის ბარათები -->
    <div class="stats-grid">
        <div class="stat-card card">
            <div class="stat-icon">💪</div>
            <div class="stat-number"><?php echo $stats['total_workouts']; ?></div>
            <div class="stat-label">ვარჯიშები</div>
            <a href="workouts.php" class="stat-link">მართვა →</a>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">🏃</div>
            <div class="stat-number"><?php echo $stats['total_exercises']; ?></div>
            <div class="stat-label">სავარჯიშოები</div>
            <a href="exercises.php" class="stat-link">მართვა →</a>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">📁</div>
            <div class="stat-number"><?php echo $stats['total_categories']; ?></div>
            <div class="stat-label">კატეგორიები</div>
            <a href="categories.php" class="stat-link">მართვა →</a>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-number"><?php echo $stats['total_instructors']; ?></div>
            <div class="stat-label">ინსტრუქტორები</div>
            <a href="instructors.php" class="stat-link">მართვა →</a>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">👥</div>
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">მომხმარებლები</div>
            <a href="users.php" class="stat-link">მართვა →</a>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">⭐</div>
            <div class="stat-number"><?php echo $stats['total_reviews']; ?></div>
            <div class="stat-label">შეფასებები</div>
        </div>
    </div>
    
    <!-- ორი სვეტი -->
    <div class="admin-content">
        
        <!-- უახლესი ვარჯიშები -->
        <div class="card">
            <h2>უახლესი ვარჯიშები</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>სახელი</th>
                        <th>სირთულე</th>
                        <th>ხანგრძლივობა</th>
                        <th>თარიღი</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($workout = mysqli_fetch_assoc($recent_workouts)): ?>
                        <tr>
                            <td>
                                <a href="workouts.php"><?php echo htmlspecialchars($workout['title']); ?></a>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $workout['difficulty_level']; ?>">
                                    <?php echo get_difficulty_label($workout['difficulty_level']); ?>
                                </span>
                            </td>
                            <td><?php echo format_duration($workout['duration']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($workout['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- უახლესი მომხმარებლები -->
        <div class="card">
            <h2>უახლესი მომხმარებლები</h2>
            <table class="table">
                <thead>
                    <tr>
                        <th>სახელი</th>
                        <th>ელ-ფოსტა</th>
                        <th>როლი</th>
                        <th>თარიღი</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($recent_users)): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge" style="background: var(--secondary-color); color: white;">ადმინი</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #E5E7EB; color: #4B5563;">მომხმარებელი</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
    </div>
    
    <!-- სწრაფი ქმედებები -->
    <div class="quick-actions card">
        <h2>სწრაფი ქმედებები</h2>
        <div class="quick-actions-grid">
            <a href="workouts.php" class="quick-action-btn">
                <span class="quick-action-icon">➕</span>
                ახალი ვარჯიში
            </a>
            <a href="exercises.php" class="quick-action-btn">
                <span class="quick-action-icon">➕</span>
                ახალი სავარჯიშო
            </a>
            <a href="categories.php" class="quick-action-btn">
                <span class="quick-action-icon">➕</span>
                ახალი კატეგორია
            </a>
            <a href="instructors.php" class="quick-action-btn">
                <span class="quick-action-icon">➕</span>
                ახალი ინსტრუქტორი
            </a>
        </div>
    </div>
    
</div>

<style>
    .admin-container {
        max-width: 1400px;
        margin: 0 auto;
    }
    
    .admin-header {
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--border-color);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        text-align: center;
        padding: 2rem;
        position: relative;
    }
    
    .stat-link {
        display: block;
        margin-top: 1rem;
        color: var(--primary-color);
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
    }
    
    .stat-link:hover {
        text-decoration: underline;
    }
    
    .admin-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .quick-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .quick-action-btn {
        padding: 1.5rem;
        background: var(--light-bg);
        border: 2px solid var(--border-color);
        border-radius: 8px;
        text-decoration: none;
        color: var(--dark-color);
        font-weight: 600;
        text-align: center;
        transition: all 0.3s;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
    }
    
    .quick-action-btn:hover {
        border-color: var(--primary-color);
        background: white;
        transform: translateY(-2px);
    }
    
    .quick-action-icon {
        font-size: 2rem;
    }
    
    @media (max-width: 768px) {
        .admin-content {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php include 'admin_footer.php'; ?>