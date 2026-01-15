<?php


require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'ვარჯიშები';

$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$difficulty_filter = isset($_GET['difficulty']) ? clean($_GET['difficulty']) : '';
$search_query = isset($_GET['search']) ? clean($_GET['search']) : '';

$sql = "
    SELECT w.*, c.name as category_name, i.name as instructor_name,
           COALESCE(AVG(r.rating), 0) as avg_rating,
           COUNT(DISTINCT r.id) as review_count
    FROM workouts w
    LEFT JOIN categories c ON w.category_id = c.id
    LEFT JOIN instructors i ON w.instructor_id = i.id
    LEFT JOIN reviews r ON w.id = r.workout_id
    WHERE 1=1
";

if ($category_filter > 0) {
    $sql .= " AND w.category_id = " . $category_filter;
}

if (!empty($difficulty_filter)) {
    $sql .= " AND w.difficulty_level = '" . mysqli_real_escape_string($conn, $difficulty_filter) . "'";
}

if (!empty($search_query)) {
    $search_safe = mysqli_real_escape_string($conn, $search_query);
    $sql .= " AND (w.title LIKE '%$search_safe%' OR w.description LIKE '%$search_safe%')";
}

$sql .= " GROUP BY w.id ORDER BY w.created_at DESC";

$workouts_result = mysqli_query($conn, $sql);

$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_sql);

include 'includes/header.php';
?>

<h1>ყველა ვარჯიში</h1>

<div class="filters-section card">
    <form method="GET" action="workouts.php" class="filters-form">
        
        <div class="filter-group">
            <label for="search">🔍 ძებნა</label>
            <input 
                type="text" 
                id="search" 
                name="search" 
                class="form-control"
                placeholder="მაგ: კარდიო, ძალოვნი..."
                value="<?php echo htmlspecialchars($search_query); ?>"
            >
        </div>
        
        <div class="filter-group">
            <label for="category">📁 კატეგორია</label>
            <select id="category" name="category" class="form-control">
                <option value="0">ყველა</option>
                <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                    <option 
                        value="<?php echo $cat['id']; ?>"
                        <?php echo ($category_filter == $cat['id']) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label for="difficulty">📊 სირთულე</label>
            <select id="difficulty" name="difficulty" class="form-control">
                <option value="">ყველა</option>
                <option value="beginner" <?php echo ($difficulty_filter == 'beginner') ? 'selected' : ''; ?>>
                    დამწყები
                </option>
                <option value="intermediate" <?php echo ($difficulty_filter == 'intermediate') ? 'selected' : ''; ?>>
                    საშუალო
                </option>
                <option value="advanced" <?php echo ($difficulty_filter == 'advanced') ? 'selected' : ''; ?>>
                    მოწინავე
                </option>
            </select>
        </div>
        
        <div class="filter-buttons">
            <button type="submit" class="btn-primary">ძებნა</button>
            <a href="workouts.php" class="btn-secondary">გასუფთავება</a>
        </div>
    </form>
</div>

<div class="results-info">
    <p>
        ნაპოვნია: <strong><?php echo mysqli_num_rows($workouts_result); ?></strong> ვარჯიში
        
        <?php if ($category_filter > 0 || !empty($difficulty_filter) || !empty($search_query)): ?>
            <a href="workouts.php" style="margin-left: 1rem; color: var(--danger-color);">
                ✕ ფილტრების მოხსნა
            </a>
        <?php endif; ?>
    </p>
</div>

<?php if (mysqli_num_rows($workouts_result) > 0): ?>
    <div class="card-grid">
        <?php while ($workout = mysqli_fetch_assoc($workouts_result)): ?>
            <div class="card workout-card">
                
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
                
                <div class="workout-info">
                    <h3><?php echo htmlspecialchars($workout['title']); ?></h3>
                    
                    <p class="workout-description">
                        <?php echo htmlspecialchars(substr($workout['description'], 0, 120)) . '...'; ?>
                    </p>
                    
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
                    
                    <a href="workout_detail.php?id=<?php echo $workout['id']; ?>" class="btn-primary" style="margin-top: 1rem; width: 100%; text-align: center;">
                        დეტალურად
                    </a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
    
<?php else: ?>
    <div class="alert alert-error">
        😔 ვარჯიშები ვერ მოიძებნა. სცადეთ სხვა ფილტრები.
    </div>
<?php endif; ?>

<style>
    .filters-section {
        margin-bottom: 2rem;
    }
    
    .filters-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
    }
    
    .filter-buttons {
        display: flex;
        gap: 0.5rem;
    }
    
    .filter-buttons button,
    .filter-buttons a {
        flex: 1;
    }
    
    .results-info {
        margin-bottom: 1.5rem;
        padding: 1rem;
        background: #F3F4F6;
        border-radius: 6px;
    }
    
    .results-info p {
        margin: 0;
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
        margin: 0.5rem 0;
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
        .filters-form {
            grid-template-columns: 1fr;
        }
        
        .filter-buttons {
            flex-direction: column;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>