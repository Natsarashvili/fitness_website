<?php
/**
 * ძებნის გვერდი
 * 
 * გაფართოებული ძებნა ყველა ველის მიხედვით
 */

require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = 'ძებნა';

// ძებნის პარამეტრები
$search = isset($_GET['q']) ? clean($_GET['q']) : '';
$results = [];
$search_performed = false;

if (!empty($search) && strlen($search) >= 2) {
    $search_performed = true;
    $search_safe = mysqli_real_escape_string($conn, $search);
    
    // ვეძებთ ვარჯიშებში
    $workouts_sql = "
        SELECT 'workout' as type, w.id, w.title as name, w.description, c.name as category,
               w.image as image_path
        FROM workouts w
        LEFT JOIN categories c ON w.category_id = c.id
        WHERE w.title LIKE '%$search_safe%' 
           OR w.description LIKE '%$search_safe%'
        LIMIT 10
    ";
    
    // ვეძებთ კატეგორიებში
    $categories_sql = "
        SELECT 'category' as type, id, name, description, icon as image_path
        FROM categories
        WHERE name LIKE '%$search_safe%' 
           OR description LIKE '%$search_safe%'
        LIMIT 5
    ";
    
    // ვეძებთ ინსტრუქტორებში
    $instructors_sql = "
        SELECT 'instructor' as type, id, name, bio as description, 
               specialization as category, photo as image_path
        FROM instructors
        WHERE name LIKE '%$search_safe%' 
           OR bio LIKE '%$search_safe%'
           OR specialization LIKE '%$search_safe%'
        LIMIT 5
    ";
    
    // ვაერთიანებთ ყველა შედეგს
    $union_sql = "($workouts_sql) UNION ($categories_sql) UNION ($instructors_sql)";
    $results_query = mysqli_query($conn, $union_sql);
    
    while ($row = mysqli_fetch_assoc($results_query)) {
        $results[] = $row;
    }
}

include 'includes/header.php';
?>

<div class="search-page">
    
    <h1 class="text-center">🔍 ძებნა</h1>
    <p class="text-center" style="color: #6B7280; margin-bottom: 2rem;">
        იპოვე ვარჯიშები, კატეგორიები და ინსტრუქტორები
    </p>
    
    <!-- ძებნის ფორმა -->
    <div class="search-form-container card">
        <form method="GET" action="search.php" class="search-form">
            <div class="search-input-wrapper">
                <input 
                    type="text" 
                    name="q" 
                    id="searchInput"
                    class="form-control search-input" 
                    placeholder="ჩაწერე რასაც ეძებ..."
                    value="<?php echo htmlspecialchars($search); ?>"
                    autofocus
                    required
                >
                <button type="submit" class="btn-primary search-button">
                    🔍 ძებნა
                </button>
            </div>
            <p style="margin-top: 0.5rem; color: #6B7280; font-size: 0.9rem;">
                მინიმუმ 2 სიმბოლო
            </p>
        </form>
        
        <!-- სწრაფი ბმულები -->
        <div class="quick-links">
            <p style="font-weight: 600; margin-bottom: 0.5rem;">პოპულარული ძებნები:</p>
            <div class="quick-links-buttons">
                <a href="?q=კარდიო" class="quick-link">კარდიო</a>
                <a href="?q=ძალოვნი" class="quick-link">ძალოვნი</a>
                <a href="?q=იოგა" class="quick-link">იოგა</a>
                <a href="?q=დამწყები" class="quick-link">დამწყები</a>
                <a href="?q=HIIT" class="quick-link">HIIT</a>
            </div>
        </div>
    </div>
    
    <!-- შედეგები -->
    <?php if ($search_performed): ?>
        <div class="search-results">
            <h2>
                შედეგები "<span style="color: var(--primary-color);"><?php echo htmlspecialchars($search); ?></span>"-ისთვის
                <span style="color: #6B7280; font-size: 1rem; font-weight: 400;">
                    (ნაპოვნია: <?php echo count($results); ?>)
                </span>
            </h2>
            
            <?php if (count($results) > 0): ?>
                <div class="results-list">
                    <?php 
                    // ჯგუფურად გავაჩინოთ ტიპის მიხედვით
                    $grouped = [];
                    foreach ($results as $result) {
                        $grouped[$result['type']][] = $result;
                    }
                    ?>
                    
                    <!-- ვარჯიშები -->
                    <?php if (isset($grouped['workout'])): ?>
                        <div class="result-group">
                            <h3>💪 ვარჯიშები</h3>
                            <div class="card-grid">
                                <?php foreach ($grouped['workout'] as $workout): ?>
                                    <div class="card result-card">
                                        <?php if ($workout['image_path']): ?>
                                            <img 
                                                src="uploads/workouts/<?php echo htmlspecialchars($workout['image_path']); ?>" 
                                                alt="<?php echo htmlspecialchars($workout['name']); ?>"
                                                class="result-image"
                                            >
                                        <?php endif; ?>
                                        
                                        <h4><?php echo htmlspecialchars($workout['name']); ?></h4>
                                        <p style="color: #6B7280; font-size: 0.9rem;">
                                            <?php echo htmlspecialchars(substr($workout['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        
                                        <?php if ($workout['category']): ?>
                                            <p style="color: #6B7280; font-size: 0.85rem;">
                                                📁 <?php echo htmlspecialchars($workout['category']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <a href="workout_detail.php?id=<?php echo $workout['id']; ?>" class="btn-primary" style="margin-top: 1rem; width: 100%; text-align: center;">
                                            დეტალურად
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- კატეგორიები -->
                    <?php if (isset($grouped['category'])): ?>
                        <div class="result-group">
                            <h3>📁 კატეგორიები</h3>
                            <div class="card-grid">
                                <?php foreach ($grouped['category'] as $category): ?>
                                    <div class="card result-card">
                                        <div style="font-size: 3rem; text-align: center; margin-bottom: 1rem;">
                                            <?php
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
                                        <h4 class="text-center"><?php echo htmlspecialchars($category['name']); ?></h4>
                                        <p style="color: #6B7280; font-size: 0.9rem; text-align: center;">
                                            <?php echo htmlspecialchars($category['description']); ?>
                                        </p>
                                        <a href="workouts.php?category=<?php echo $category['id']; ?>" class="btn-primary" style="margin-top: 1rem; width: 100%; text-align: center;">
                                            ვარჯიშების ნახვა
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- ინსტრუქტორები -->
                    <?php if (isset($grouped['instructor'])): ?>
                        <div class="result-group">
                            <h3>👨‍🏫 ინსტრუქტორები</h3>
                            <div class="card-grid">
                                <?php foreach ($grouped['instructor'] as $instructor): ?>
                                    <div class="card result-card">
                                        <?php if ($instructor['image_path']): ?>
                                            <img 
                                                src="uploads/instructors/<?php echo htmlspecialchars($instructor['image_path']); ?>" 
                                                alt="<?php echo htmlspecialchars($instructor['name']); ?>"
                                                style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 1rem; display: block; object-fit: cover;"
                                            >
                                        <?php else: ?>
                                            <div style="width: 100px; height: 100px; border-radius: 50%; margin: 0 auto 1rem; background: var(--primary-color); color: white; display: flex; align-items: center; justify-content: center; font-size: 2rem;">
                                                👨‍🏫
                                            </div>
                                        <?php endif; ?>
                                        
                                        <h4 class="text-center"><?php echo htmlspecialchars($instructor['name']); ?></h4>
                                        
                                        <?php if ($instructor['category']): ?>
                                            <p style="color: var(--primary-color); text-align: center; font-weight: 600; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($instructor['category']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <p style="color: #6B7280; font-size: 0.9rem; text-align: center;">
                                            <?php echo htmlspecialchars(substr($instructor['description'], 0, 80)) . '...'; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    😔 არაფერი მოიძებნა "<strong><?php echo htmlspecialchars($search); ?></strong>"-ისთვის
                    <p style="margin-top: 1rem;">სცადეთ:</p>
                    <ul style="margin-top: 0.5rem;">
                        <li>სხვა საძიებო სიტყვები</li>
                        <li>უფრო ზოგადი ტერმინები</li>
                        <li>სხვადასხვა ვარიანტები</li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <!-- როცა ძებნა არ არის შესრულებული -->
        <div class="search-tips card">
            <h3>💡 ძებნის რჩევები</h3>
            <ul style="line-height: 2;">
                <li>გამოიყენე მინიმუმ 2 სიმბოლო</li>
                <li>ეძებე ვარჯიშების სახელით (მაგ: "კარდიო", "იოგა")</li>
                <li>ეძებე ინსტრუქტორის სახელით</li>
                <li>ეძებე კატეგორიების მიხედვით</li>
                <li>გამოიყენე ქართული ენა</li>
            </ul>
        </div>
    <?php endif; ?>
    
</div>

<style>
    .search-page {
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .search-form-container {
        margin-bottom: 2rem;
    }
    
    .search-input-wrapper {
        display: flex;
        gap: 1rem;
    }
    
    .search-input {
        flex: 1;
        font-size: 1.1rem;
    }
    
    .search-button {
        white-space: nowrap;
    }
    
    .quick-links {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-color);
    }
    
    .quick-links-buttons {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .quick-link {
        padding: 0.5rem 1rem;
        background: #F3F4F6;
        border-radius: 20px;
        text-decoration: none;
        color: var(--dark-color);
        font-size: 0.9rem;
        transition: all 0.3s;
    }
    
    .quick-link:hover {
        background: var(--primary-color);
        color: white;
    }
    
    .search-results {
        margin-top: 2rem;
    }
    
    .result-group {
        margin-bottom: 3rem;
    }
    
    .result-group h3 {
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .result-card {
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .result-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .search-tips ul {
        color: #6B7280;
        padding-left: 1.5rem;
    }
    
    @media (max-width: 768px) {
        .search-input-wrapper {
            flex-direction: column;
        }
        
        .quick-links-buttons {
            flex-direction: column;
        }
        
        .quick-link {
            text-align: center;
        }
    }
</style>

<?php include 'includes/footer.php'; ?>