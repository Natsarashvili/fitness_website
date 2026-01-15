<?php


require_once 'config/database.php';
require_once 'includes/functions.php';

require_login();

$page_title = 'ჩემი პროფილი';
$user_id = $_SESSION['user_id'];

$user_sql = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_sql);
$user = mysqli_fetch_assoc($user_result);

$stats_sql = "
    SELECT 
        COUNT(DISTINCT up.workout_id) as completed_workouts,
        COUNT(DISTINCT r.workout_id) as reviewed_workouts,
        COALESCE(AVG(r.rating), 0) as avg_rating_given
    FROM users u
    LEFT JOIN user_progress up ON u.id = up.user_id
    LEFT JOIN reviews r ON u.id = r.user_id
    WHERE u.id = $user_id
";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

$progress_sql = "
    SELECT up.*, w.title, w.image, w.difficulty_level, w.duration
    FROM user_progress up
    JOIN workouts w ON up.workout_id = w.id
    WHERE up.user_id = $user_id
    ORDER BY up.completed_date DESC
    LIMIT 10
";
$progress_result = mysqli_query($conn, $progress_sql);

$reviews_sql = "
    SELECT r.*, w.title as workout_title
    FROM reviews r
    JOIN workouts w ON r.workout_id = w.id
    WHERE r.user_id = $user_id
    ORDER BY r.created_at DESC
    LIMIT 5
";
$reviews_result = mysqli_query($conn, $reviews_sql);

include 'includes/header.php';
?>

<div class="profile-page">
    
    <div class="profile-header card">
        <div class="profile-avatar">
            <div class="avatar-circle">
                <?php echo strtoupper(substr($user['username'], 0, 2)); ?>
            </div>
        </div>
        
        <div class="profile-info">
            <h1><?php echo htmlspecialchars($user['username']); ?></h1>
            <p style="color: #6B7280;">📧 <?php echo htmlspecialchars($user['email']); ?></p>
            <p style="color: #6B7280; font-size: 0.9rem;">
                📅 რეგისტრაცია: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
            </p>
            
            <?php if ($user['role'] === 'admin'): ?>
                <span class="badge" style="background: var(--secondary-color); color: white; margin-top: 0.5rem;">
                    👑 ადმინისტრატორი
                </span>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card card">
            <div class="stat-icon">✅</div>
            <div class="stat-number"><?php echo $stats['completed_workouts']; ?></div>
            <div class="stat-label">დასრულებული ვარჯიში</div>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">⭐</div>
            <div class="stat-number"><?php echo $stats['reviewed_workouts']; ?></div>
            <div class="stat-label">შეფასებული ვარჯიში</div>
        </div>
        
        <div class="stat-card card">
            <div class="stat-icon">📊</div>
            <div class="stat-number"><?php echo number_format($stats['avg_rating_given'], 1); ?></div>
            <div class="stat-label">საშუალო რეიტინგი</div>
        </div>
    </div>
    
    <div class="profile-content">
        
        <div class="profile-section">
            <div class="card">
                <h2>📋 დასრულებული ვარჯიშები</h2>
                
                <?php if (mysqli_num_rows($progress_result) > 0): ?>
                    <div class="progress-list">
                        <?php while ($progress = mysqli_fetch_assoc($progress_result)): ?>
                            <div class="progress-item">
                                <div class="progress-item-header">
                                    <div>
                                        <h4>
                                            <a href="workout_detail.php?id=<?php echo $progress['workout_id']; ?>">
                                                <?php echo htmlspecialchars($progress['title']); ?>
                                            </a>
                                        </h4>
                                        <div class="progress-meta">
                                            <span class="badge badge-<?php echo $progress['difficulty_level']; ?>">
                                                <?php echo get_difficulty_label($progress['difficulty_level']); ?>
                                            </span>
                                            <span>⏱️ <?php echo format_duration($progress['duration']); ?></span>
                                        </div>
                                    </div>
                                    <div class="progress-date">
                                        ✅ <?php echo date('d.m.Y', strtotime($progress['completed_date'])); ?>
                                    </div>
                                </div>
                                
                                <?php if ($progress['notes']): ?>
                                    <div class="progress-notes">
                                        <strong>შენიშვნები:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($progress['notes'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">💪</div>
                        <p>ჯერ არ გაქვს დასრულებული ვარჯიში</p>
                        <a href="workouts.php" class="btn-primary" style="margin-top: 1rem;">
                            დაიწყე ახლავე
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-section">
            <div class="card">
                <h2>⭐ ჩემი შეფასებები</h2>
                
                <?php if (mysqli_num_rows($reviews_result) > 0): ?>
                    <div class="reviews-list">
                        <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                            <div class="review-item">
                                <h4>
                                    <a href="workout_detail.php?id=<?php echo $review['workout_id']; ?>">
                                        <?php echo htmlspecialchars($review['workout_title']); ?>
                                    </a>
                                </h4>
                                
                                <div class="review-rating">
                                    <?php echo display_rating($review['rating']); ?>
                                </div>
                                
                                <?php if ($review['comment']): ?>
                                    <p class="review-comment">
                                        <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <p class="review-date">
                                    <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">⭐</div>
                        <p>ჯერ არ გაქვს შეფასებები</p>
                        <a href="workouts.php" class="btn-primary" style="margin-top: 1rem;">
                            იპოვე ვარჯიში
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
    
    <div class="card">
        <h2>📈 აქტივობა ბოლო 7 დღეში</h2>
        
        <?php
        $activity_sql = "
            SELECT DATE(completed_date) as date, COUNT(*) as count
            FROM user_progress
            WHERE user_id = $user_id 
              AND completed_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            GROUP BY DATE(completed_date)
            ORDER BY date ASC
        ";
        $activity_result = mysqli_query($conn, $activity_sql);
        
        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $days[$date] = 0;
        }
        
        while ($activity = mysqli_fetch_assoc($activity_result)) {
            $days[$activity['date']] = $activity['count'];
        }
        ?>
        
        <div class="activity-chart">
            <?php foreach ($days as $date => $count): ?>
                <div class="activity-day">
                    <div class="activity-bar" style="height: <?php echo ($count > 0) ? ($count * 50) : 10; ?>px; background: <?php echo ($count > 0) ? 'var(--primary-color)' : '#E5E7EB'; ?>;">
                    </div>
                    <div class="activity-label">
                        <?php echo date('d/m', strtotime($date)); ?>
                    </div>
                    <div class="activity-count"><?php echo $count; ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
</div>

<style>
    .profile-page {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .profile-header {
        display: flex;
        gap: 2rem;
        align-items: center;
        margin-bottom: 2rem;
    }
    
    .avatar-circle {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: 700;
    }
    
    .profile-info h1 {
        margin-bottom: 0.5rem;
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
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6B7280;
        font-size: 0.9rem;
    }
    
    .profile-content {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 2rem;
    }
    
    .progress-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .progress-item {
        padding: 1rem;
        background: #F9FAFB;
        border-radius: 8px;
        border-left: 4px solid var(--secondary-color);
    }
    
    .progress-item-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        gap: 1rem;
    }
    
    .progress-item h4 {
        margin-bottom: 0.5rem;
    }
    
    .progress-item h4 a {
        color: var(--dark-color);
        text-decoration: none;
    }
    
    .progress-item h4 a:hover {
        color: var(--primary-color);
    }
    
    .progress-meta {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        font-size: 0.85rem;
    }
    
    .progress-date {
        color: var(--secondary-color);
        font-size: 0.85rem;
        font-weight: 600;
        white-space: nowrap;
    }
    
    .progress-notes {
        margin-top: 0.5rem;
        padding-top: 0.5rem;
        border-top: 1px solid #E5E7EB;
        font-size: 0.9rem;
    }
    
    .progress-notes p {
        margin-top: 0.25rem;
        color: #6B7280;
    }
    
    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    
    .review-item {
        padding: 1rem;
        background: #F9FAFB;
        border-radius: 8px;
    }
    
    .review-item h4 {
        margin-bottom: 0.5rem;
    }
    
    .review-item h4 a {
        color: var(--dark-color);
        text-decoration: none;
    }
    
    .review-item h4 a:hover {
        color: var(--primary-color);
    }
    
    .review-rating {
        margin: 0.5rem 0;
    }
    
    .review-comment {
        color: #4B5563;
        font-size: 0.9rem;
        line-height: 1.6;
        margin: 0.5rem 0;
    }
    
    .review-date {
        color: #6B7280;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }
    
    .empty-state {
        text-align: center;
        padding: 3rem 2rem;
        color: #6B7280;
    }
    
    .activity-chart {
        display: flex;
        justify-content: space-around;
        align-items: flex-end;
        height: 200px;
        padding: 1rem;
        background: #F9FAFB;
        border-radius: 8px;
        gap: 0.5rem;
    }
    
    .activity-day {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }
    
    .activity-bar {
        width: 100%;
        background: var(--primary-color);
        border-radius: 4px 4px 0 0;
        transition: all 0.3s;
        min-height: 10px;
    }
    
    .activity-label {
        font-size: 0.75rem;
        color: #6B7280;
    }
    
    .activity-count {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary-color);
    }
    
    @media (max-width: 768px) {
        .profile-header {
            flex-direction: column;
            text-align: center;
        }
        
        .profile-content {
            grid-template-columns: 1fr;
        }
        
        .activity-chart {
            height: 150px;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>