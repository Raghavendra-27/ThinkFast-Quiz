<?php
// User Management Dashboard
session_start();

// Simple admin check - you might want to add proper admin authentication
$isAdmin = true; // Set this to your admin check logic

if (!$isAdmin) {
    die("Access denied. Admin privileges required.");
}

require_once 'config/database.php';

// Handle user actions
$action = $_GET['action'] ?? '';
$message = '';

if ($action === 'delete' && isset($_GET['id'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        $message = "<div class='alert success'>User deleted successfully!</div>";
    } catch (PDOException $e) {
        $message = "<div class='alert error'>Error deleting user: " . $e->getMessage() . "</div>";
    }
}

// Get all users with their stats
try {
    $stmt = $pdo->query("
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.email,
            u.total_quizzes,
            u.best_score,
            u.created_at,
            u.updated_at,
            COUNT(qr.id) as quiz_attempts,
            AVG(qr.percentage) as avg_score
        FROM users u
        LEFT JOIN quiz_results qr ON u.id = qr.user_id
        GROUP BY u.id
        ORDER BY u.created_at DESC
    ");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Get total statistics
try {
    $stats = $pdo->query("
        SELECT 
            COUNT(DISTINCT u.id) as total_users,
            COUNT(qr.id) as total_quiz_attempts,
            AVG(qr.percentage) as overall_avg_score,
            MAX(qr.percentage) as highest_score
        FROM users u
        LEFT JOIN quiz_results qr ON u.id = qr.user_id
    ")->fetch();
} catch (PDOException $e) {
    $stats = ['total_users' => 0, 'total_quiz_attempts' => 0, 'overall_avg_score' => 0, 'highest_score' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Quiz App Admin</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .header h1 {
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            margin-bottom: 10px;
            opacity: 0.8;
        }
        
        .stat-card .number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .search-section {
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .search-box {
            flex: 1;
            min-width: 300px;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary { background: #667eea; color: white; }
        .btn-danger { background: #e74c3c; color: white; }
        .btn-success { background: #27ae60; color: white; }
        .btn-info { background: #3498db; color: white; }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .users-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
        }
        
        .user-details h4 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }
        
        .user-details p {
            margin: 2px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #cce5ff; color: #004085; }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: #ddd;
        }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            .header { flex-direction: column; gap: 15px; align-items: flex-start; }
            .search-section { flex-direction: column; }
            .search-box { min-width: auto; }
            
            .users-table {
                overflow-x: auto;
            }
            
            table {
                min-width: 800px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                <i class='bx bxs-user-account'></i>
                User Management Dashboard
            </h1>
            <div>
                <a href="index.html" class="btn btn-info">
                    <i class='bx bx-home'></i> Back to Quiz
                </a>
                <a href="export-users.php" class="btn btn-success">
                    <i class='bx bx-download'></i> Export Users
                </a>
            </div>
        </div>

        <?= $message ?>

        <!-- Statistics Cards -->
        <div class="stats">
            <div class="stat-card">
                <i class='bx bxs-user-account'></i>
                <div class="number"><?= $stats['total_users'] ?></div>
                <div class="label">Total Users</div>
            </div>
            <div class="stat-card">
                <i class='bx bxs-trophy'></i>
                <div class="number"><?= $stats['total_quiz_attempts'] ?></div>
                <div class="label">Quiz Attempts</div>
            </div>
            <div class="stat-card">
                <i class='bx bxs-bar-chart-alt-2'></i>
                <div class="number"><?= number_format($stats['overall_avg_score'], 1) ?>%</div>
                <div class="label">Average Score</div>
            </div>
            <div class="stat-card">
                <i class='bx bxs-medal'></i>
                <div class="number"><?= number_format($stats['highest_score'], 1) ?>%</div>
                <div class="label">Highest Score</div>
            </div>
        </div>

        <!-- Search Section -->
        <div class="search-section">
            <input type="text" id="searchBox" class="search-box" placeholder="Search users by name or email...">
            <button onclick="exportToCSV()" class="btn btn-success">
                <i class='bx bx-export'></i> Export CSV
            </button>
        </div>

        <!-- Users Table -->
        <div class="users-table">
            <?php if (count($users) > 0): ?>
                <table id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Quiz Stats</th>
                            <th>Performance</th>
                            <th>Joined</th>
                            <th>Last Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                                        </div>
                                        <div class="user-details">
                                            <h4><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h4>
                                            <p>ID: #<?= $user['id'] ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <span class="badge badge-info"><?= $user['total_quizzes'] ?> Quizzes</span><br>
                                        <small><?= $user['quiz_attempts'] ?> Total Attempts</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>Best: <?= $user['best_score'] ?>%</strong><br>
                                        <small>Avg: <?= $user['avg_score'] ? number_format($user['avg_score'], 1) : 0 ?>%</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong><?= date('M j, Y', strtotime($user['created_at'])) ?></strong><br>
                                        <small><?= date('g:i A', strtotime($user['created_at'])) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <?php if ($user['updated_at'] !== $user['created_at']): ?>
                                            <strong><?= date('M j, Y', strtotime($user['updated_at'])) ?></strong><br>
                                            <small><?= date('g:i A', strtotime($user['updated_at'])) ?></small>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Never</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="user-details.php?id=<?= $user['id'] ?>" class="btn btn-info" title="View Details">
                                            <i class='bx bx-show'></i>
                                        </a>
                                        <a href="?action=delete&id=<?= $user['id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')" 
                                           class="btn btn-danger" title="Delete User">
                                            <i class='bx bx-trash'></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class='bx bxs-user-x'></i>
                    <h3>No Users Found</h3>
                    <p>No users have signed up yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchBox').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('#usersTable tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Export to CSV
        function exportToCSV() {
            const table = document.getElementById('usersTable');
            let csv = [];
            
            // Headers
            csv.push(['Name', 'Email', 'Total Quizzes', 'Best Score', 'Average Score', 'Joined Date'].join(','));
            
            // Data
            <?php foreach ($users as $user): ?>
            csv.push([
                '<?= addslashes($user['first_name'] . ' ' . $user['last_name']) ?>',
                '<?= addslashes($user['email']) ?>',
                '<?= $user['total_quizzes'] ?>',
                '<?= $user['best_score'] ?>',
                '<?= $user['avg_score'] ? number_format($user['avg_score'], 1) : 0 ?>',
                '<?= date('Y-m-d H:i:s', strtotime($user['created_at'])) ?>'
            ].join(','));
            <?php endforeach; ?>
            
            // Download
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.setAttribute('hidden', '');
            a.setAttribute('href', url);
            a.setAttribute('download', 'quiz_users_' + new Date().toISOString().split('T')[0] + '.csv');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                location.reload();
            }
        }, 30000);
    </script>
</body>
</html>