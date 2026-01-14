<?php
/**
 * მთავარი გვერდი
 * 
 * აქ ნაჩვენებია:
 * - მისალმება
 * - პოპულარული ვარჯიშები
 * - კატეგორიები
 * - სტატისტიკა
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'მთავარი';

// ვიღებთ პოპულარულ ვარჯიშებს (ბოლო 6)
$workouts_sql = "
    SELECT w.*, c.name as category_name, i.name as instructor_name,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.id) as review_count
    FROM workouts w
    LEFT JOIN categories c ON w.category_id = c.id
    LEFT JOIN instructors i ON w.instructor_id = i.id
    LEFT JOIN reviews r ON w.id = r.workout_id
    GROUP BY w.id
    ORDER BY w.created_at DESC
    LIMIT 6
";
$workouts_result = mysqli_query($conn, $workouts_sql);

// ვიღებთ კატეგორიებს
$categories_sql = "SELECT * FROM categories LIMIT 5";
$categories_result = mysqli_query($conn, $categories_sql);

// სტატისტიკა
$stats_sql = "
    SELECT 
        (SELECT COUNT(*) FROM workouts) as total_workouts,
        (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
        (SELECT COUNT(*) FROM instructors) as total_instructors,
        (SELECT COUNT(*) FROM categories) as total_categories
";
$stats_result = mysqli_query($conn, $stats_sql);
$stats = mysqli_fetch_assoc($stats_result);

include 'includes/header.php';
?>

<!-- Hero Section - მთავარი ბანერი -->
<section class="hero">
    <div class="hero-content">
        <h1>💪 მოგესალმებით FitLife-ზე!</h1>
        <p class="hero-text">
            შენი ჯანსაღი ცხოვრების პარტნიორი. აირჩიე ვარჯიში და დაიწყე ცვლილებები დღესვე!
        </p>
        
        <?php if (!is_logged_in()): ?>
            <div class="hero-buttons">
                <a href="register.php" class="btn-primary">დაიწყე ახლავე</a>
                <a href="workouts.php" class="btn-secondary">ნახე ვარჯიშები</a>
            </div>
        <?php else: ?>
            <div class="hero-buttons">
                <a href="workouts.php" class="btn-primary">ნახე ყველა ვარჯიში</a>
                <a href="profile.php" class="btn-secondary">ჩემი პროფილი</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- სტატისტიკა -->
<section class="stats-section">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">🏋️</div>
            <div class="stat-number"><?php echo $stats['total_workouts']; ?></div>
            <div class="stat-label">ვარჯიში</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👥</div>
            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
            <div class="stat-label">მომხმარებელი</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">👨‍🏫</div>
            <div class="stat-number"><?php echo $stats['total_instructors']; ?></div>
            <div class="stat-label">ინსტრუქტორი</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-number"><?php echo $stats['total_categories']; ?></div>
            <div class="stat-label">კატეგორია</div>
        </div>
    </div>
</section>

<!-- კატეგორიები -->
<section class="categories-section">
    <h2 class="text-center">კატეგორიები</h2>
    <div class="categories-grid">
        <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
            <a href="workouts.php?category=<?php echo $category['id']; ?>" class="category-card card">
                <div class="category-icon">
                    <?php
                    // იქონები კატეგორიებისთვის
                    $icons = [
                        'კარდიო' => '🏃',
                        'ძალოვნი' => '💪',
                        'იოგა' => '🧘',
                        'HIIT' => '🔥',
                        'სტრეჩინგი' => '🤸'
                    ];
                    echo $icons[$category['name']] ?? '🎯';
                    ?>
                </div>
                <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                <p><?php echo htmlspecialchars($category['description']); ?></p>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<!-- პოპულარული ვარჯიშები -->
<section class="workouts-section">
    <h2 class="text-center">უახლესი ვარჯიშები</h2>
    
    <?php if (mysqli_num_rows($workouts_result) > 0): ?>
        <div class="card-grid">
            <?php while ($workout = mysqli_fetch_assoc($workouts_result)): ?>
                <div class="card workout-card">
                    
                    <!-- სურათი -->
                    <?php if ($workout['image']): ?>
                        <img 
                            src="uploads/workouts/<?php echo htmlspecialchars($workout['image']); ?>" 
                            alt="<?php echo htmlspecialchars($workout['title']); ?>"
                            class="workout-image"
                        >
                    <?php else: ?>
                        <div class="workout-image" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                            💪
                        </div>
                    <?php endif; ?>
                    
                    <!-- ინფორმაცია -->
                    <div class="workout-info">
                        <h3><?php echo htmlspecialchars($workout['title']); ?></h3>
                        
                        <p class="workout-description">
                            <?php echo htmlspecialchars(substr($workout['description'], 0, 100)) . '...'; ?>
                        </p>
                        
                        <!-- დეტალები -->
                        <div class="workout-meta">
                            <span class="badge badge-<?php echo $workout['difficulty_level']; ?>">
                                <?php echo get_difficulty_label($workout['difficulty_level']); ?>
                            </span>
                            
                            <span>⏱️ <?php echo format_duration($workout['duration']); ?></span>
                            
                            <?php if ($workout['avg_rating'] > 0): ?>
                                <span>
                                    <?php echo display_rating(round($workout['avg_rating'])); ?>
                                    (<?php echo $workout['review_count']; ?>)
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($workout['category_name']): ?>
                            <p style="margin-top: 0.5rem; color: #6B7280; font-size: 0.9rem;">
                                📁 <?php echo htmlspecialchars($workout['category_name']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <?php if ($workout['instructor_name']): ?>
                            <p style="color: #6B7280; font-size: 0.9rem;">
                                👨‍🏫 <?php echo htmlspecialchars($workout['instructor_name']); ?>
                            </p>
                        <?php endif; ?>
                        
                        <!-- ღილაკი -->
                        <a href="workout_detail.php?id=<?php echo $workout['id']; ?>" class="btn-primary" style="margin-top: 1rem; width: 100%; text-align: center;">
                            დეტალურად
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-3">
            <a href="workouts.php" class="btn-primary">ყველა ვარჯიშის ნახვა →</a>
        </div>
        
    <?php else: ?>
        <div class="alert alert-error">
            ვარჯიშები ჯერ არ არის დამატებული
        </div>
    <?php endif; ?>
</section>

<style>
    /* Hero Section */
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 4rem 2rem;
        border-radius: 12px;
        margin-bottom: 3rem;
        text-align: center;
    }
    
    .hero h1 {
        color: white;
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }
    
    .hero-text {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.95;
    }
    
    .hero-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    /* სტატისტიკა */
    .stats-section {
        margin: 3rem 0;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }
    
    .stat-card {
        background: white;
        padding: 2rem;
        border-radius: 8px;
        text-align: center;
        box-shadow: var(--shadow);
    }
    
    .stat-icon {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #6B7280;
        font-size: 0.9rem;
    }
    
    /* კატეგორიები */
    .categories-section {
        margin: 3rem 0;
    }
    
    .categories-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .category-card {
        text-align: center;
        text-decoration: none;
        color: var(--dark-color);
        transition: all 0.3s;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .category-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
    
    .category-card h3 {
        margin-bottom: 0.5rem;
    }
    
    .category-card p {
        color: #6B7280;
        font-size: 0.9rem;
    }
    
    /* ვარჯიშის ბარათი */
    .workouts-section {
        margin: 3rem 0;
    }
    
    .workout-card {
        display: flex;
        flex-direction: column;
    }
    
    .workout-info {
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    
    .workout-description {
        color: #6B7280;
        flex: 1;
    }
    
    .workout-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 1.8rem;
        }
        
        .hero-text {
            font-size: 1rem;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>