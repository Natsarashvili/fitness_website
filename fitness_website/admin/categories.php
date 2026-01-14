<?php
/**
 * ადმინ პანელი - კატეგორიების მართვა
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'კატეგორიების მართვა';

// წაშლა
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    mysqli_query($conn, "DELETE FROM categories WHERE id = $id");
    show_message('კატეგორია წაიშალა', 'success');
    redirect('categories.php');
}

// დამატება/რედაქტირება
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = clean($_POST['name']);
    $description = clean($_POST['description']);
    
    if ($id > 0) {
        $sql = "UPDATE categories SET name = ?, description = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $name, $description, $id);
    } else {
        $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $name, $description);
    }
    
    mysqli_stmt_execute($stmt);
    show_message('კატეგორია შენახულია', 'success');
    mysqli_stmt_close($stmt);
    redirect('categories.php');
}

$edit_category = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_result = mysqli_query($conn, "SELECT * FROM categories WHERE id = $edit_id");
    $edit_category = mysqli_fetch_assoc($edit_result);
}

$categories_result = mysqli_query($conn, "SELECT * FROM categories ORDER BY name");

include 'admin_header.php';;
?>

<div class="admin-container">
    
    <div class="admin-admin_header">
        <h1>📁 კატეგორიების მართვა</h1>
        <a href="index.php" class="btn-secondary">← დაბრუნება</a>
    </div>
    
    <div class="card">
        <h2><?php echo $edit_category ? 'კატეგორიის რედაქტირება' : 'ახალი კატეგორიის დამატება'; ?></h2>
        
        <form method="POST">
            <?php if ($edit_category): ?>
                <input type="hidden" name="id" value="<?php echo $edit_category['id']; ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="name">სახელი *</label>
                <input type="text" id="name" name="name" class="form-control" 
                       value="<?php echo $edit_category ? htmlspecialchars($edit_category['name']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="description">აღწერა</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?php echo $edit_category ? htmlspecialchars($edit_category['description']) : ''; ?></textarea>
            </div>
            
            <div style="display: flex; gap: 1rem;">
                <button type="submit" class="btn-primary">
                    <?php echo $edit_category ? 'განახლება' : 'დამატება'; ?>
                </button>
                <?php if ($edit_category): ?>
                    <a href="categories.php" class="btn-secondary">გაუქმება</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="card">
        <h2>ყველა კატეგორია (<?php echo mysqli_num_rows($categories_result); ?>)</h2>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>სახელი</th>
                        <th>აღწერა</th>
                        <th>ქმედებები</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                        <tr>
                            <td><?php echo $cat['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($cat['name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($cat['description']); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="?edit=<?php echo $cat['id']; ?>" class="btn-secondary" style="font-size: 0.85rem; padding: 0.4rem 0.8rem;">✏️</a>
                                    <a href="?delete=<?php echo $cat['id']; ?>" 
                                       class="btn-danger" 
                                       style="font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                       onclick="return confirm('დარწმუნებული ხართ? ყველა ვარჯიში ამ კატეგორიაში დაკარგავს კატეგორიას.')">🗑️</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
</div>

<?php include 'admin_footer.php'; ?>