<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/json.php';

// Require login and check role
require_login('../admin/login.php');
require_role(ROLE_ADMIN, '../index.php');

$user_id = get_current_user_id();
$user = get_user_by_id($user_id, ROLE_ADMIN);

if (!$user) {
    header('Location: login.php');
    exit;
}

// Get statistics
$students = json_read(STUDENTS_FILE);
$staff = json_read(STAFF_FILE);
$issues = json_read(MAINTENANCE_REQUESTS_FILE);
$activities = json_read(ACTIVITIES_FILE);

$student_count = count($students);
$staff_count = count($staff);
$issue_count = count($issues);
$pending_issues = count(array_filter($issues, fn($i) => ($i['status'] ?? 'pending') === 'pending'));
$completed_issues = count(array_filter($issues, fn($i) => ($i['status'] ?? 'pending') === 'completed'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MainRes Maintenance</title>
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .admin-dashboard {
            display: grid;
            grid-template-columns: 250px 1fr;
            min-height: 100vh;
        }
        
        .admin-sidebar {
            background: linear-gradient(135deg, #1e3a5f 0%, #26648E 100%);
            padding: 2rem 1rem;
            color: white;
        }
        
        .admin-sidebar-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .admin-sidebar-logo img {
            height: 40px;
        }
        
        .admin-nav {
            list-style: none;
            padding: 0;
        }
        
        .admin-nav li {
            margin-bottom: 0.5rem;
        }
        
        .admin-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-nav a i {
            width: 20px;
            text-align: center;
        }
        
        .admin-content {
            padding: 2rem;
            background: #f5f7fa;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .admin-header h1 {
            color: #1e3a5f;
            font-size: 2rem;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.students { background: #e3f2fd; color: #1976d2; }
        .stat-icon.staff { background: #f3e5f5; color: #7b1fa2; }
        .stat-icon.issues { background: #fff3e0; color: #f57c00; }
        .stat-icon.pending { background: #ffebee; color: #c62828; }
        
        .stat-info h3 {
            margin: 0;
            font-size: 2rem;
            color: #1e3a5f;
        }
        
        .stat-info p {
            margin: 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .quick-actions {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .quick-actions h2 {
            margin-top: 0;
            color: #1e3a5f;
            margin-bottom: 1rem;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: #f5f7fa;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #1e3a5f;
        }
        
        .action-btn:hover {
            background: #e3f2fd;
            transform: translateY(-2px);
        }
        
        .action-btn i {
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-logo">
                <img src="../assets/images/logo.png" alt="VUT Logo">
                <div>
                    <div style="font-weight: 800;">VUT MainRes</div>
                    <div style="font-size: 0.8rem; opacity: 0.8;">Admin Panel</div>
                </div>
            </div>
            <ul class="admin-nav">
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="students.php"><i class="fas fa-user-graduate"></i> Students</a></li>
                <li><a href="maintenance-staff.php"><i class="fas fa-tools"></i> Maintenance Staff</a></li>
                <li><a href="issues.php"><i class="fas fa-clipboard-list"></i> Issues</a></li>
                <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1rem;">
                    <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </li>
            </ul>
        </aside>
        
        <main class="admin-content">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <div class="admin-user">
                    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                </div>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon students">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $student_count; ?></h3>
                        <p>Total Students</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon staff">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $staff_count; ?></h3>
                        <p>Maintenance Staff</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon issues">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $issue_count; ?></h3>
                        <p>Total Issues</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $pending_issues; ?></h3>
                        <p>Pending Issues</p>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="students.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Student</span>
                    </a>
                    <a href="maintenance-staff.php" class="action-btn">
                        <i class="fas fa-user-plus"></i>
                        <span>Add Staff</span>
                    </a>
                    <a href="issues.php" class="action-btn">
                        <i class="fas fa-clipboard-list"></i>
                        <span>View Issues</span>
                    </a>
                    <a href="activities.php" class="action-btn">
                        <i class="fas fa-history"></i>
                        <span>View Activity</span>
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
