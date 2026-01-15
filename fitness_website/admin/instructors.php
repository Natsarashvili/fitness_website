<?php

require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'ინსტრუქტორების მართვა';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    $img_sql = "SELECT photo FROM instructors WHERE id = $id";
    $img_result = mysqli_query($conn, $img_sql);
    $img_row = mysqli_fetch_assoc($img_result);
    
    if ($img_row && $img_row['photo'] && file_exists("../uploads/instructors/" . $img_row['photo'])) {
        unlink("../uploads/instructors/" . $img_row['photo']);
    }
    
    mysqli_query($conn, "DELETE FROM instructors WHERE id = $id");
    show_message('ინსტრუქტორი წაიშალა', 'success');
    redirect('instructors.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = clean($_POST['name']);
    $bio = clean($_POST['bio']);
    $specialization = clean($_POST['specialization']);
    $experience_years = (int)$_POST['experience_years'];
    
    $photo_name = '';
    
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $upload = upload_file($_FILES['photo'], '../uploads/instructors');
        if ($upload['success']) {
            $photo_name = $upload['filename'];
        }
    }
    
    if ($id > 0) {
        $sql = "UPDATE instructors SET name = ?, bio = ?, specialization = ?, experience_years = ?";
        if ($photo_name) {
            $sql .= ", photo = ?";
        }
        $sql .= " WHERE id = ?";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($photo_name) {
            mysqli_stmt_bind_param($stmt, "sssisi", $name, $bio, $specialization, $experience_years, $photo_name, $id);
        } else {
            mysqli_stmt_bind_param($stmt, "sssii", $name, $bio, $specialization, $experience_years, $id);
        }
    } else {
        $sql = "INSERT INTO instructors (name, bio, specialization, experience_years, photo) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssis", $name, $bio, $specialization, $experience_years, $photo_name);
    }
    
    mysqli_stmt_execute($stmt);
    show_message('ინსტრუქტორი შენახულია', 'success');
    mysqli_stmt_close($stmt);
    redirect('instructors.php');
}

$edit_instructor = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM instructors WHERE id = $edit_id");
    $edit_instructor = mysqli_fetch_assoc($edit_result);
}

$instructors_result = mysqli_query($conn, "SELECT * FROM instructors ORDER BY name");

include 'admin_header.php';
?>

<div class="admin-container">
    
    <div class="admin-admin_header">
        <h1>👨‍🏫 ინსტრუქტორების მართვა</h1>
        <a href="index.php" class="btn-secondary">← დაბრუნება</a>
    </div>
    
    <div class="card">
        <h2><?php echo $edit_instructor ? 'ინსტრუქტორის რედაქტირება' : 'ახალი ინსტრუქტორის დამატება'; ?></h2>
        
        <form method="POST" enctype="multipart/form-data">
            <?php if ($edit_instructor): ?>
                <input type="hidden" name="id" value="<?php echo $edit_instructor['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">სახელი და გვარი *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo $edit_instructor ? htmlspecialchars($edit_instructor['name']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="bio">ბიოგრაფია</label>
                <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo $edit_instructor ? htmlspecialchars($edit_instructor['bio']) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="specialization">სპეციალიზაცია</label>
                    <input type="text" id="specialization" name="specialization" class="form-control" 
                           value="<?php echo $edit_instructor ? htmlspecialchars($edit_instructor['specialization']) : ''; ?>" 
                           placeholder="მაგ: ძალოვნი ვარჯიშები">
                </div>
                
                <div class="form-group">
                    <label for="experience_years">გამოცდილება (წლები)</label>
                    <input type="number" id="experience_years" name="experience_years" class="form-control" 
                           value="<?php echo $edit_instructor ? $edit_instructor['experience_years'] : '0'; ?>" 
                           min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label for="photo">ფოტო</label>
                <input type="file" id="photo" name="photo" class="form-control" accept="image/*">
                <?php if ($edit_instructor && $edit_instructor['photo']): ?>
                    <img src="../uploads/instructors/<?php echo htmlspecialchars($edit_instructor['photo']); ?>" 
                         style="max-width: 150px; margin-top: 0.5rem; border-radius: 8px;">
                <?php endif; ?>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-primary">
                    <?php echo $edit_instructor ? 'განახლება' : 'დამატება'; ?>
                </button>
                <?php if ($edit_instructor): ?>
                    <a href="instructors.php" class="btn-secondary">გაუქმება</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card">
        <h2>ყველა ინსტრუქტორი (<?php echo mysqli_num_rows($instructors_result); ?>)</h2>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ფოტო</th>
                        <th>სახელი</th>
                        <th>სპეციალიზაცია</th>
                        <th>გამოცდილება</th>
                        <th>ქმედებები</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($inst = mysqli_fetch_assoc($instructors_result)): ?>
                        <tr>
                            <td><?php echo $inst['id']; ?></td>
                            <td>
                                <?php if ($inst['photo']): ?>
                                    <img src="../uploads/instructors/<?php echo htmlspecialchars($inst['photo']); ?>" 
                                         style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                <?php else: ?>
                                    <div style="width: 50px; height: 50px; background: #E5E7EB; border-radius: 50%; display: flex; align-items: center; justify-content: center;">👨‍🏫</div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($inst['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($inst['specialization']); ?></td>
                            <td><?php echo $inst['experience_years']; ?> წელი</td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $inst['id']; ?>" class="btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">✏️</a>
                                    <a href="?delete=<?php echo $inst['id']; ?>" 
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
</style>

<?php include 'admin_footer.php'; ?>