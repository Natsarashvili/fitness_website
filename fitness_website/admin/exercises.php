<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'სავარჯიშოების მართვა';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM exercises WHERE id = $id");
    show_message('სავარჯიშო წაიშალა', 'success');
    redirect('exercises.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $workout_id = (int)$_POST['workout_id'];
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    $sets = (int)$_POST['sets'];
    $reps = (int)$_POST['reps'];
    $video_url = clean($_POST['video_url']);
    $order_number = (int)$_POST['order_number'];
    
    if ($id > 0) {
        $sql = "UPDATE exercises SET workout_id = ?, name = ?, description = ?, 
                sets = ?, reps = ?, video_url = ?, order_number = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issiiisi", $workout_id, $name, $description, 
                               $sets, $reps, $video_url, $order_number, $id);
    } else {
        $sql = "INSERT INTO exercises (workout_id, name, description, sets, reps, video_url, order_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "issiisi", $workout_id, $name, $description, 
                               $sets, $reps, $video_url, $order_number);
    }
    
    mysqli_stmt_execute($stmt);
    show_message('სავარჯიშო შენახულია', 'success');
    mysqli_stmt_close($stmt);
    redirect('exercises.php');
}

$edit_exercise = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM exercises WHERE id = $edit_id");
    $edit_exercise = mysqli_fetch_assoc($edit_result);
}

$exercises_sql = "
    SELECT e.*, w.title as workout_title
    FROM exercises e
    JOIN workouts w ON e.workout_id = w.id
    ORDER BY w.title, e.order_number
";
$exercises_result = mysqli_query($conn, $exercises_sql);

$workouts_result = mysqli_query($conn, "SELECT id, title FROM workouts ORDER BY title");

include 'admin_header.php';
?>

<div class="admin-container">
    
    <div class="admin-admin_header">
        <h1>🏃 სავარჯიშოების მართვა</h1>
        <a href="index.php" class="btn-secondary">← დაბრუნება</a>
    </div>
    
    <div class="card">
        <h2><?php echo $edit_exercise ? 'სავარჯიშოს რედაქტირება' : 'ახალი სავარჯიშოს დამატება'; ?></h2>
        
        <form method="POST">
            <?php if ($edit_exercise): ?>
                <input type="hidden" name="id" value="<?php echo $edit_exercise['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="workout_id">ვარჯიში *</label>
                <select id="workout_id" name="workout_id" class="form-control" required>
                    <option value="">აირჩიეთ ვარჯიში...</option>
                    <?php mysqli_data_seek($workouts_result, 0); ?>
                    <?php while ($w = mysqli_fetch_assoc($workouts_result)): ?>
                        <option value="<?php echo $w['id']; ?>" 
                                <?php echo ($edit_exercise && $edit_exercise['workout_id'] == $w['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($w['title']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="name">სავარჯიშოს სახელი *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo $edit_exercise ? htmlspecialchars($edit_exercise['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">აღწერა</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_exercise ? htmlspecialchars($edit_exercise['description']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="sets">სერიები</label>
                    <input type="number" id="sets" name="sets" class="form-control" 
                           value="<?php echo $edit_exercise ? $edit_exercise['sets'] : '3'; ?>" min="0">
                </div>
                
                <div class="form-group">
                    <label for="reps">გამეორებები</label>
                    <input type="number" id="reps" name="reps" class="form-control" 
                           value="<?php echo $edit_exercise ? $edit_exercise['reps'] : '10'; ?>" min="0">
                </div>
                
                <div class="form-group">
                    <label for="order_number">რიგითობა</label>
                    <input type="number" id="order_number" name="order_number" class="form-control" 
                           value="<?php echo $edit_exercise ? $edit_exercise['order_number'] : '1'; ?>" min="1">
                </div>
            </div>
            
            <div class="form-group">
                <label for="video_url">ვიდეო URL (არასავალდებულო)</label>
                <input type="url" id="video_url" name="video_url" class="form-control" 
                       value="<?php echo $edit_exercise ? htmlspecialchars($edit_exercise['video_url']) : ''; ?>" 
                       placeholder="https://youtube.com/...">
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-primary">
                    <?php echo $edit_exercise ? 'განახლება' : 'დამატება'; ?>
                </button>
                <?php if ($edit_exercise): ?>
                    <a href="exercises.php" class="btn-secondary">გაუქმება</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card">
        <h2>ყველა სავარჯიშო (<?php echo mysqli_num_rows($exercises_result); ?>)</h2>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ვარჯიში</th>
                        <th>სახელი</th>
                        <th>სერია × გამეორება</th>
                        <th>რიგითობა</th>
                        <th>ვიდეო</th>
                        <th>ქმედებები</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($ex = mysqli_fetch_assoc($exercises_result)): ?>
                        <tr>
                            <td><?php echo $ex['id']; ?></td>
                            <td><?php echo htmlspecialchars($ex['workout_title']); ?></td>
                            <td><strong><?php echo htmlspecialchars($ex['name']); ?></strong></td>
                            <td><?php echo $ex['sets'] . ' × ' . $ex['reps']; ?></td>
                            <td><?php echo $ex['order_number']; ?></td>
                            <td>
                                <?php if ($ex['video_url']): ?>
                                    <a href="<?php echo htmlspecialchars($ex['video_url']); ?>" target="_blank">🎥</a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $ex['id']; ?>" class="btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">✏️</a>
                                    <a href="?delete=<?php echo $ex['id']; ?>" 
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
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
</style>

<?php include 'admin_footer.php'; ?>