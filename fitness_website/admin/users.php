<?php
/**
 * ადმინ პანელი - მომხმარებლების მართვა
 */

require_once '../config/database.php';
require_once '../includes/functions.php';

require_admin();

$page_title = 'მომხმარებლების მართვა';

// წაშლა
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // არ შეიძლება საკუთარი თავის წაშლა
    if ($id == $_SESSION['user_id']) {
        show_message('საკუთარი თავი ვერ წაშლით', 'error');
        redirect('users.php');
    }
    
    mysqli_query($conn, "DELETE FROM users WHERE id = $id");
    show_message('მომხმარებელი წაიშალა', 'success');
    redirect('users.php');
}

// როლის შეცვლა
if (isset($_GET['toggle_role'])) {
    $id = (int)$_GET['toggle_role'];
    
    $user_sql = "SELECT role FROM users WHERE id = $id";
    $user_result = mysqli_query($conn, $user_sql);
    $user = mysqli_fetch_assoc($user_result);
    
    $new_role = ($user['role'] === 'admin') ? 'user' : 'admin';
    
    mysqli_query($conn, "UPDATE users SET role = '$new_role' WHERE id = $id");
    show_message('როლი შეიცვალა', 'success');
    redirect('users.php');
}

$users_result = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");

include 'admin_header.php';
?>

<div class="admin-container">
    
    <div class="admin-admin_header">
        <h1>👥 მომხმარებლების მართვა</h1>
        <a href="index.php" class="btn-secondary">← დაბრუნება</a>
    </div>
    
    <div class="card">
        <h2>ყველა მომხმარებელი (<?php echo mysqli_num_rows($users_result); ?>)</h2>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>სახელი</th>
                        <th>ელ-ფოსტა</th>
                        <th>როლი</th>
                        <th>რეგისტრაცია</th>
                        <th>ქმედებები</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge" style="background: #DBEAFE; color: #1E40AF; font-size: 0.75rem; margin-left: 0.5rem;">თქვენ</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <span class="badge" style="background: var(--secondary-color); color: white;">👑 ადმინი</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #E5E7EB; color: #4B5563;">მომხმარებელი</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <a href="?toggle_role=<?php echo $user['id']; ?>" 
                                           class="btn-secondary" 
                                           style="font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                           onclick="return confirm('როლის შეცვლა?')">
                                            🔄
                                        </a>
                                        <a href="?delete=<?php echo $user['id']; ?>" 
                                           class="btn-danger" 
                                           style="font-size: 0.85rem; padding: 0.4rem 0.8rem;"
                                           onclick="return confirm('დარწმუნებული ხართ?')">
                                            🗑️
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #6B7280; font-size: 0.85rem;">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <h3>ინფორმაცია</h3>
        <ul style="line-height: 2; color: #6B7280;">
            <li>🔄 - როლის შეცვლა (ადმინი ↔ მომხმარებელი)</li>
            <li>🗑️ - მომხმარებლის წაშლა</li>
            <li>საკუთარი თავის წაშლა შეუძლებელია</li>
            <li>მომხმარებლის წაშლისას წაიშლება მისი ყველა პროგრესი და შეფასება</li>
        </ul>
    </div>
    
</div>

<?php include 'admin_footer.php'; ?>