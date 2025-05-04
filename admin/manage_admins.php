<?php
session_start();
require_once '../config/database.php';

// Check if super admin is logged in
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'super_admin') {
    header('Location: login.php');
    exit();
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $username = $_POST['username'];
                $password = $_POST['password'];
                $role = $_POST['role'];
                
                try {
                    // Check if username already exists
                    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->fetch()) {
                        $error = "Username already exists";
                    } else {
                        // Insert new admin
                        $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
                        $stmt->execute([$username, $password, $role]);
                        $message = "Admin account created successfully";
                    }
                } catch (PDOException $e) {
                    $error = "Error creating admin account: " . $e->getMessage();
                }
                break;
                
            case 'edit':
                $id = $_POST['id'];
                $username = $_POST['username'];
                $role = $_POST['role'];
                
                try {
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $role, $id]);
                    $message = "Admin account updated successfully";
                } catch (PDOException $e) {
                    $error = "Error updating admin account: " . $e->getMessage();
                }
                break;
        }
    }
}

// Handle GET actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    if ($_GET['action'] === 'toggle') {
        try {
            $stmt = $pdo->prepare("UPDATE admins SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $message = "Admin status updated successfully";
        } catch (PDOException $e) {
            $error = "Error updating admin status: " . $e->getMessage();
        }
    }
}

// Fetch all admins
$admins = $pdo->query("SELECT * FROM admins ORDER BY role DESC, username")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - InnoLearn</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .modern-card{
            padding:20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand" href="super_admin_dashboard.php">InnoLearn Super Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="super_admin_dashboard.php">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="section-title mb-0">Manage Admins</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
                <i class="bi bi-person-plus me-2"></i>Add New Admin
            </button>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="modern-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($admin['username']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $admin['role'] === 'super_admin' ? 'danger' : 'primary'; ?>">
                                            <?php echo ucfirst($admin['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $admin['is_active'] ? 'success' : 'danger'; ?>">
                                            <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                            <a href="?action=toggle&id=<?php echo $admin['id']; ?>" 
                                               class="btn btn-sm btn-<?php echo $admin['is_active'] ? 'danger' : 'success'; ?>">
                                                <?php echo $admin['is_active'] ? 'Disable' : 'Enable'; ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Admin Modal -->
    <div class="modal fade" id="addAdminModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Admin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Admin</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 