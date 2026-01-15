<?php


require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'ვარჯიშების მართვა';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $img_sql = "SELECT image FROM workouts WHERE id = $id";
    $img_result = mysqli_query($conn, $img_sql);
    $img_row = mysqli_fetch_assoc($img_result);
    
    if ($img_row && $img_row['image'] && file_exists("../uploads/workouts/" . $img_row['image'])) {
        unlink("../uploads/workouts/" . $img_row['image']);
    }
    
    $delete_sql = "DELETE FROM workouts WHERE id = $id";
    if (mysqli_query($conn, $delete_sql)) {
        show_message('ვარჯიში წარმატებით წაიშალა', 'success');
    }
    redirect('workouts.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $title = clean($_POST['title']);
    $description = clean($_POST['description']);
    $difficulty = clean($_POST['difficulty_level']);
    $duration = (int)$_POST['duration'];
    $category_id = (int)$_POST['category_id'];
    $instructor_id = (int)$_POST['instructor_id'];
    
    $image_name = '';
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $upload = upload_file($_FILES['image'], '../uploads/workouts');
        if ($upload['success']) {
            $image_name = $upload['filename'];
        }
    }
    
    if ($id > 0) {
        $update_sql = "UPDATE workouts SET title = ?, description = ?, difficulty_level = ?, 
                       duration = ?, category_id = ?, instructor_id = ?";
        
        if ($image_name) {
            $update_sql .= ", image = ?";
        }
        
        $update_sql .= " WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $update_sql);
        
        if ($image_name) {
            mysqli_stmt_bind_param($stmt, "sssiiisi", $title, $description, $difficulty, 
                                   $duration, $category_id, $instructor_id, $image_name, $id);
        } else {
            mysqli_stmt_bind_param($stmt, "sssiiii", $title, $description, $difficulty, 
                                   $duration, $category_id, $instructor_id, $id);
        }
        
        if (mysqli_stmt_execute($stmt)) {
            show_message('ვარჯიში განახლდა', 'success');
        }
        mysqli_stmt_close($stmt);
        
    } else {
        $insert_sql = "INSERT INTO workouts (title, description, difficulty_level, duration, 
                       category_id, instructor_id, image) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_sql);
        mysqli_stmt_bind_param($stmt, "sssiiis", $title, $description, $difficulty, 
                               $duration, $category_id, $instructor_id, $image_name);
        
        if (mysqli_stmt_execute($stmt)) {
            show_message('ვარჯიში დაემატა', 'success');
        }
        mysqli_stmt_close($stmt);
    }
    
    redirect('workouts.php');
}

$edit_workout = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_sql = "SELECT * FROM workouts WHERE id = $edit_id";
    $edit_result = mysqli_query($conn, $edit_sql);
    $edit_workout = mysqli_fetch_assoc($edit_result);
}

$workouts_sql = "
    SELECT w.*, c.name as category_name, i.name as instructor_name
    FROM workouts w
    LEFT JOIN categories c ON w.category_id = c.id
    LEFT JOIN instructors i ON w.instructor_id = i.id
    ORDER BY w.created_at DESC
";
$workouts_result = mysqli_query($conn, $workouts_sql);

$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");
$instructors_result = mysqli_query($conn, "SELECT * FROM instructors ORDER BY name");

include 'admin_header.php';
?>

<div class="admin-container">
    
    <div class="admin-admin_header">
        <h1>💪 ვარჯიშების მართვა</h1>
        <a href="index.php" class="btn-secondary">← დაბრუნება</a>
    </div>
    
    <div class="card">
        <h2><?php echo $edit_workout ? 'ვარჯიშის რედაქტირება' : 'ახალი ვარჯიშის დამატება'; ?></h2>
        
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_workout): ?>
                <input type="hidden" name="id" value="<?php echo $edit_workout['id']; ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="title">სათაური *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo $edit_workout ? htmlspecialchars($edit_workout['title']) : ''; ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="duration">ხანგრძლივობა (წუთები) *</label>
                    <input type="number" id="duration" name="duration" class="form-control" 
                           value="<?php echo $edit_workout ? $edit_workout['duration'] : ''; ?>" 
                           required min="1">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">აღწერა *</label>
                <textarea id="description" name="description" class="form-control" rows="4" required><?php echo $edit_workout ? htmlspecialchars($edit_workout['description']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="difficulty_level">სირთულე *</label>
                    <select id="difficulty_level" name="difficulty_level" class="form-control" required>
                        <option value="beginner" <?php echo ($edit_workout && $edit_workout['difficulty_level'] === 'beginner') ? 'selected' : ''; ?>>დამწყები</option>
                        <option value="intermediate" <?php echo ($edit_workout && $edit_workout['difficulty_level'] === 'intermediate') ? 'selected' : ''; ?>>საშუალო</option>
                        <option value="advanced" <?php echo ($edit_workout && $edit_workout['difficulty_level'] === 'advanced') ? 'selected' : ''; ?>>მოწინავე</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category_id">კატეგორია *</label>
                    <select id="category_id" name="category_id" class="form-control" required>
                        <option value="">აირჩიეთ...</option>
                        <?php mysqli_data_seek($categories_result, 0); ?>
                        <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($edit_workout && $edit_workout['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="instructor_id">ინსტრუქტორი</label>
                    <select id="instructor_id" name="instructor_id" class="form-control">
                        <option value="0">არცერთი</option>
                        <?php mysqli_data_seek($instructors_result, 0); ?>
                        <?php while ($inst = mysqli_fetch_assoc($instructors_result)): ?>
                            <option value="<?php echo $inst['id']; ?>" 
                                    <?php echo ($edit_workout && $edit_workout['instructor_id'] == $inst['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($inst['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="image">სურათი (JPG, PNG, GIF - მაქს 5MB)</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                <?php if ($edit_workout && $edit_workout['image']): ?>
                    <p style="margin-top: 0.5rem; color: #6B7280; font-size: 0.9rem;">
                        მიმდინარე: <?php echo htmlspecialchars($edit_workout['image']); ?>
                    </p>
                    <img src="../uploads/workouts/<?php echo htmlspecialchars($edit_workout['image']); ?>" 
                         style="max-width: 200px; margin-top: 0.5rem; border-radius: 8px;" id="imagePreview">
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-primary">
                    <?php echo $edit_workout ? 'განახლება' : 'დამატება'; ?>
                </button>
                <?php if ($edit_workout): ?>
                    <a href="workouts.php" class="btn-secondary">გაუქმება</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card">
        <h2>ყველა ვარჯიში (<?php echo mysqli_num_rows($workouts_result); ?>)</h2>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>სურათი</th>
                        <th>სახელი</th>
                        <th>კატეგორია</th>
                        <th>ინსტრუქტორი</th>
                        <th>სირთულე</th>
                        <th>ხანგრძლივობა</th>
                        <th>თარიღი</th>
                        <th>ქმედებები</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($workout = mysqli_fetch_assoc($workouts_result)): ?>
                        <tr>
                            <td><?php echo $workout['id']; ?></td>
                            <td>
                                <?php if ($workout['image']): ?>
                                    <img src="../uploads/workouts/<?php echo htmlspecialchars($workout['image']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #E5E7EB; border-radius: 4px; display: flex; align-items: center; justify-content: center;">💪</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($workout['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($workout['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($workout['instructor_name'] ?: '-'); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $workout['difficulty_level']; ?>">
                                    <?php echo get_difficulty_label($workout['difficulty_level']); ?>
                                </span>
                            </td>
                            <td><?php echo format_duration($workout['duration']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($workout['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $workout['id']; ?>" class="btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">✏️</a>
                                    <a href="?delete=<?php echo $workout['id']; ?>" 
                                       class="btn-danger" 
                                       style="font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                       onclick="return confirm('დარწმუნებული ხართ?')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<style>
    .form-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .table-responsive {
        overflow-x: auto;
    }
</style>

<?php include 'admin_footer.php'; ?>