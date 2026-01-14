<?php
/**
 * ვარჯიშის დეტალური გვერდი
 * 
 * აქ ნაჩვენებია:
 * - ვარჯიშის სრული ინფორმაცია
 * - სავარჯიშოები (exercises)
 * - შეფასებები და კომენტარები
 * - პროგრესის მარკირება
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

// ვიღებთ ვარჯიშის ID-ს
$workout_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($workout_id <= 0) {
    show_message('ვარჯიში ვერ მოიძებნა', 'error');
    redirect('workouts.php');
}

// ვარჯიშის ინფორმაცია
$workout_sql = "
    SELECT w.*, c.name as category_name, i.name as instructor_name, i.bio as instructor_bio,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.id) as review_count
    FROM workouts w
    LEFT JOIN categories c ON w.category_id = c.id
    LEFT JOIN instructors i ON w.instructor_id = i.id
    LEFT JOIN reviews r ON w.id = r.workout_id
    WHERE w.id = $workout_id
    GROUP BY w.id
";
$workout_result = mysqli_query($conn, $workout_sql);

if (mysqli_num_rows($workout_result) == 0) {
    show_message('ვარჯიში ვერ მოიძებნა', 'error');
    redirect('workouts.php');
}

$workout = mysqli_fetch_assoc($workout_result);
$page_title = $workout['title'];

// სავარჯიშოების მიღება
$exercises_sql = "SELECT * FROM exercises WHERE workout_id = $workout_id ORDER BY order_number ASC";
$exercises_result = mysqli_query($conn, $exercises_sql);

// შეფასებების მიღება
$reviews_sql = "
    SELECT r.*, u.username 
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.workout_id = $workout_id
    ORDER BY r.created_at DESC
    LIMIT 10
";
$reviews_result = mysqli_query($conn, $reviews_sql);

// თუ მომხმარებელი შესულია - შევამოწმოთ გაიარა თუ არა
$user_completed = false;
if (is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $check_sql = "SELECT id FROM user_progress WHERE user_id = $user_id AND workout_id = $workout_id";
    $check_result = mysqli_query($conn, $check_sql);
    $user_completed = mysqli_num_rows($check_result) > 0;
}

// პროგრესის მარკირება
if (isset($_POST['mark_complete']) && is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $notes = clean($_POST['notes'] ?? '');
    
    if (!$user_completed) {
        $insert_sql = "INSERT INTO user_progress (user_id, workout_id, completed_date, notes) 
                       VALUES ($user_id, $workout_id, CURDATE(), ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "s", $notes);
        
        if (mysqli_stmt_execute($stmt)) {
            show_message('ვარჯიში მონიშნულია როგორც დასრულებული! 🎉', 'success');
            $user_completed = true;
        }
        mysqli_stmt_close($stmt);
    }
}

// შეფასების დამატება
if (isset($_POST['add_review']) && is_logged_in()) {
    $user_id = $_SESSION['user_id'];
    $rating = (int)$_POST['rating'];
    $comment = clean($_POST['comment']);
    
    if ($rating >= 1 && $rating <= 5) {
        // შევამოწმოთ უკვე შეაფასა თუ არა
        $check_review_sql = "SELECT id FROM reviews WHERE user_id = $user_id AND workout_id = $workout_id";
        $check_review = mysqli_query($conn, $check_review_sql);
        
        if (mysqli_num_rows($check_review) == 0) {
            $insert_review_sql = "INSERT INTO reviews (user_id, workout_id, rating, comment) 
                                  VALUES ($user_id, $workout_id, ?, ?)";
            $stmt = mysqli_prepare($conn, $insert_review_sql);
            mysqli_stmt_bind_param($stmt, "is", $rating, $comment);
            
            if (mysqli_stmt_execute($stmt)) {
                show_message('შეფასება წარმატებით დაემატა!', 'success');
                redirect('workout_detail.php?id=' . $workout_id);
            }
            mysqli_stmt_close($stmt);
        } else {
            show_message('თქვენ უკვე შეაფასეთ ეს ვარჯიში', 'error');
        }
    }
}

include 'includes/header.php';
?>

<!-- ვარჯიშის Header -->
<div class="workout-header">
    <div class="workout-header-content">
        <div class="workout-header-info">
            <h1><?php echo htmlspecialchars($workout['title']); ?></h1>
            
            <div class="workout-badges">
                <span class="badge badge-<?php echo $workout['difficulty_level']; ?>">
                    <?php echo get_difficulty_label($workout['difficulty_level']); ?>
                </span>
                
                <span class="badge" style="background: #DBEAFE; color: #1E40AF;">
                    📁 <?php echo htmlspecialchars($workout['category_name']); ?>
                </span>
                
                <span class="badge" style="background: #FEF3C7; color: #92400E;">
                    ⏱️ <?php echo format_duration($workout['duration']); ?>
                </span>
                
                <?php if ($workout['avg_rating'] > 0): ?>
                    <span class="badge" style="background: #FEE2E2; color: #991B1B;">
                        <?php echo display_rating(round($workout['avg_rating'])); ?>
                        (<?php echo $workout['review_count']; ?>)
                    </span>
                <?php endif; ?>
            </div>
            
            <?php if ($workout['instructor_name']): ?>
                <p style="margin-top: 1rem; color: #6B7280;">
                    👨‍🏫 <strong>ინსტრუქტორი:</strong> <?php echo htmlspecialchars($workout['instructor_name']); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <?php if ($workout['image']): ?>
            <div class="workout-header-image">
                <img src="uploads/workouts/<?php echo htmlspecialchars($workout['image']); ?>" alt="<?php echo htmlspecialchars($workout['title']); ?>">
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- მთავარი კონტენტი -->
<div class="workout-content">
    
    <!-- აღწერა -->
    <section class="card">
        <h2>აღწერა</h2>
        <p style="line-height: 1.8; color: #4B5563;">
            <?php echo nl2br(htmlspecialchars($workout['description'])); ?>
        </p>
    </section>
    
    <!-- სავარჯიშოები -->
    <section class="card">
        <h2>სავარჯიშოები</h2>
        
        <?php if (mysqli_num_rows($exercises_result) > 0): ?>
            <div class="exercises-list">
                <?php $counter = 1; ?>
                <?php while ($exercise = mysqli_fetch_assoc($exercises_result)): ?>
                    <div class="exercise-item">
                        <div class="exercise-number"><?php echo $counter++; ?></div>
                        <div class="exercise-details">
                            <h3><?php echo htmlspecialchars($exercise['name']); ?></h3>
                            <p><?php echo htmlspecialchars($exercise['description']); ?></p>
                            
                            <?php if ($exercise['sets'] || $exercise['reps']): ?>
                                <div class="exercise-meta">
                                    <?php if ($exercise['sets']): ?>
                                        <span>📋 <?php echo $exercise['sets']; ?> სერია</span>
                                    <?php endif; ?>
                                    
                                    <?php if ($exercise['reps']): ?>
                                        <span>🔄 <?php echo $exercise['reps']; ?> გამეორება</span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($exercise['video_url']): ?>
                                <a href="<?php echo htmlspecialchars($exercise['video_url']); ?>" target="_blank" class="btn-secondary" style="margin-top: 0.5rem; display: inline-block;">
                                    🎥 ვიდეო ინსტრუქცია
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="color: #6B7280;">სავარჯიშოები ჯერ არ არის დამატებული</p>
        <?php endif; ?>
    </section>
    
    <!-- პროგრესის მარკირება -->
    <?php if (is_logged_in()): ?>
        <section class="card">
            <h2>ჩემი პროგრესი</h2>
            
            <?php if ($user_completed): ?>
                <div class="alert alert-success">
                    ✅ თქვენ უკვე გაიარეთ ეს ვარჯიში!
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="notes">შენიშვნები (არასავალდებულო)</label>
                        <textarea id="notes" name="notes" class="form-control" placeholder="როგორ იყო ვარჯიში? რა შეგრძნებები გქონდა?"></textarea>
                    </div>
                    
                    <button type="submit" name="mark_complete" class="btn-primary">
                        ✓ მონიშვნა როგორც დასრულებული
                    </button>
                </form>
            <?php endif; ?>
        </section>
    <?php endif; ?>
    
    <!-- შეფასებები -->
    <section class="card">
        <h2>შეფასებები და კომენტარები</h2>
        
        <!-- შეფასების ფორმა -->
        <?php if (is_logged_in()): ?>
            <div class="review-form">
                <h3>დატოვეთ თქვენი შეფასება</h3>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>რეიტინგი *</label>
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" id="star<?php echo $i; ?>" name="rating" value="<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>">⭐</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="comment">კომენტარი</label>
                        <textarea id="comment" name="comment" class="form-control" rows="4" placeholder="თქვენი აზრი ვარჯიშზე..."></textarea>
                    </div>
                    
                    <button type="submit" name="add_review" class="btn-primary">
                        შეფასების დამატება
                    </button>
                </form>
            </div>
            <hr style="margin: 2rem 0;">
        <?php else: ?>
            <div class="alert alert-error">
                შეფასების დასატოვებლად გთხოვთ <a href="login.php">შეხვიდეთ</a> სისტემაში
            </div>
        <?php endif; ?>
        
        <!-- არსებული შეფასებები -->
        <?php if (mysqli_num_rows($reviews_result) > 0): ?>
            <div class="reviews-list">
                <?php while ($review = mysqli_fetch_assoc($reviews_result)): ?>
                    <div class="review-item">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($review['username']); ?></strong>
                            <span class="review-rating">
                                <?php echo display_rating($review['rating']); ?>
                            </span>
                        </div>
                        <p class="review-date">
                            <?php echo date('d.m.Y', strtotime($review['created_at'])); ?>
                        </p>
                        <?php if ($review['comment']): ?>
                            <p class="review-comment">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p style="color: #6B7280;">შეფასებები ჯერ არ არის</p>
        <?php endif; ?>
    </section>
    
</div>

<style>
    /* Header */
    .workout-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .workout-header-content {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2rem;
        align-items: center;
    }
    
    .workout-header h1 {
        color: white;
        margin-bottom: 1rem;
    }
    
    .workout-badges {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }
    
    .workout-header-image img {
        width: 100%;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    
    /* სავარჯიშოები */
    .exercises-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .exercise-item {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: #F9FAFB;
        border-radius: 8px;
        border-left: 4px solid var(--primary-color);
    }
    
    .exercise-number {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        flex-shrink: 0;
    }
    
    .exercise-details h3 {
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }
    
    .exercise-details p {
        color: #6B7280;
        margin-bottom: 0.5rem;
    }
    
    .exercise-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.9rem;
        color: #4B5563;
    }
    
    /* შეფასებები */
    .review-form {
        background: #F9FAFB;
        padding: 1.5rem;
        border-radius: 8px;
        margin-bottom: 2rem;
    }
    
    .star-rating {
        display: flex;
        flex-direction: row-reverse;
        justify-content: flex-end;
        gap: 0.5rem;
    }
    
    .star-rating input {
        display: none;
    }
    
    .star-rating label {
        font-size: 2rem;
        cursor: pointer;
        filter: grayscale(100%);
        transition: all 0.2s;
    }
    
    .star-rating label:hover,
    .star-rating label:hover ~ label,
    .star-rating input:checked ~ label {
        filter: grayscale(0%);
    }
    
    .reviews-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    
    .review-item {
        padding: 1rem;
        background: #F9FAFB;
        border-radius: 8px;
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .review-date {
        color: #6B7280;
        font-size: 0.85rem;
        margin-bottom: 0.5rem;
    }
    
    .review-comment {
        color: #4B5563;
        line-height: 1.6;
    }
    
    @media (max-width: 768px) {
        .workout-header-content {
            grid-template-columns: 1fr;
        }
        
        .workout-header-image {
            order: -1;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>