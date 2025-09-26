<?php
session_start();
require_once 'config/database.php';

$userId = $_GET['id'] ?? 0;

if (!$userId) {
    header('Location: admin-users.php');
    exit;
}

// Get user details
try {
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            COUNT(qr.id) as total_quiz_attempts,
            AVG(qr.percentage) as avg_score,
            MAX(qr.percentage) as highest_score,
            MIN(qr.percentage) as lowest_score,
            COUNT(DISTINCT qr.category) as categories_attempted
        FROM users u
        LEFT JOIN quiz_results qr ON u.id = qr.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: admin-users.php');
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Get quiz history
try {
    $stmt = $pdo->prepare("
        SELECT 
            qr.*,
            DATE(qr.created_at) as quiz_date,
            TIME(qr.created_at) as quiz_time
        FROM quiz_results qr
        WHERE qr.user_id = ?
        ORDER BY qr.created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $quizHistory = $stmt->fetchAll();
} catch (PDOException $e) {
    $quizHistory = [];
}

// Get category performance
try {
    $stmt = $pdo->prepare("
        SELECT 
            category,
            COUNT(*) as attempts,
            AVG(percentage) as avg_score,
            MAX(percentage) as best_score,
            MIN(percentage) as worst_score
        FROM quiz_results
        WHERE user_id = ?
        GROUP BY category
        ORDER BY avg_score DESC
    ");
    $stmt->execute([$userId]);
    $categoryStats = $stmt->fetchAll();
} catch (PDOException $e) {
    $categoryStats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> - User Details</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-avatar-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        
        .user-info h1 {
            margin: 0;
            color: #333;
            font-size: 28px;
        }
        
        .user-info p {
            color: #666;
            margin: 5px 0;
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
        
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .card h3 {
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .quiz-history {
            max-height: 500px;
            overflow-y: auto;
        }
        
        .quiz-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
            transition: background 0.3s;
        }
        
        .quiz-item:hover {
            background: #f8f9fa;
        }
        
        .quiz-item:last-child {
            border-bottom: none;
        }
        
        .quiz-info h4 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }
        
        .quiz-info p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .quiz-score {
            text-align: right;
        }
        
        .score-big {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .score-percentage {
            font-size: 14px;
            color: #666;
        }
        
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        
        .category-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .category-item:last-child {
            border-bottom: none;
        }
        
        .category-name {
            font-weight: 600;
            color: #333;
        }
        
        .category-stats {
            text-align: right;
            font-size: 14px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        @media (max-width: 768px) {
            .grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .user-header {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="user-header">
                <div class="user-avatar-large">
                    <?= strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)) ?>
                </div>
                <div class="user-info">
                    <h1><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h1>
                    <p><i class='bx bx-envelope'></i> <?= htmlspecialchars($user['email']) ?></p>
                    <p><i class='bx bx-calendar'></i> Joined <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
                    <p><i class='bx bx-id-badge'></i> User ID: #<?= $user['id'] ?></p>
                </div>
            </div>
            <div>
                <a href="admin-users.php" class="btn btn-info">
                    <i class='bx bx-arrow-back'></i> Back to Users
                </a>
                <a href="mailto:<?= $user['email'] ?>" class="btn btn-success">
                    <i class='bx bx-mail-send'></i> Send Email
                </a>
            </div>
        </div>

        <div class="grid">
            <!-- User Statistics -->
            <div class="card">
                <h3><i class='bx bxs-bar-chart-alt-2'></i> User Statistics</h3>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number"><?= $user['total_quiz_attempts'] ?></div>
                        <div class="stat-label">Quiz Attempts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $user['total_quizzes'] ?></div>
                        <div class="stat-label">Completed Quizzes</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($user['avg_score'] ?: 0, 1) ?>%</div>
                        <div class="stat-label">Average Score</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $user['best_score'] ?>%</div>
                        <div class="stat-label">Best Score</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= $user['categories_attempted'] ?></div>
                        <div class="stat-label">Categories Tried</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= number_format($user['highest_score'] ?: 0, 1) ?>%</div>
                        <div class="stat-label">Highest Score</div>
                    </div>
                </div>
            </div>

            <!-- Category Performance -->
            <div class="card">
                <h3><i class='bx bxs-category'></i> Category Performance</h3>
                <?php if (count($categoryStats) > 0): ?>
                    <div style="max-height: 350px; overflow-y: auto;">
                        <?php foreach ($categoryStats as $category): ?>
                            <div class="category-item">
                                <div>
                                    <div class="category-name"><?= htmlspecialchars($category['category']) ?></div>
                                    <small><?= $category['attempts'] ?> attempt<?= $category['attempts'] != 1 ? 's' : '' ?></small>
                                </div>
                                <div class="category-stats">
                                    <div><strong><?= number_format($category['avg_score'], 1) ?>%</strong> avg</div>
                                    <small>Best: <?= number_format($category['best_score'], 1) ?>%</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p style="text-align: center; color: #666; padding: 40px;">No quiz attempts yet</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Performance Chart -->
        <div class="card full-width">
            <h3><i class='bx bxs-chart'></i> Performance Over Time</h3>
            <div class="chart-container">
                <canvas id="performanceChart"></canvas>
            </div>
        </div>

        <!-- Quiz History -->
        <div class="card full-width">
            <h3><i class='bx bxs-time'></i> Recent Quiz History</h3>
            <?php if (count($quizHistory) > 0): ?>
                <div class="quiz-history">
                    <?php foreach ($quizHistory as $quiz): ?>
                        <div class="quiz-item">
                            <div class="quiz-info">
                                <h4><?= htmlspecialchars($quiz['category']) ?></h4>
                                <p>
                                    <?= date('M j, Y', strtotime($quiz['created_at'])) ?> at 
                                    <?= date('g:i A', strtotime($quiz['created_at'])) ?>
                                </p>
                            </div>
                            <div class="quiz-score">
                                <div class="score-big"><?= $quiz['score'] ?>/<?= $quiz['total_questions'] ?></div>
                                <div class="score-percentage">
                                    <?php 
                                    $percentage = $quiz['percentage'];
                                    if ($percentage >= 80) {
                                        echo "<span class='badge badge-success'>$percentage%</span>";
                                    } elseif ($percentage >= 60) {
                                        echo "<span class='badge badge-warning'>$percentage%</span>";
                                    } else {
                                        echo "<span class='badge badge-danger'>$percentage%</span>";
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 40px;">No quiz history available</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Performance Chart
        const ctx = document.getElementById('performanceChart').getContext('2d');
        
        const chartData = {
            labels: [
                <?php foreach (array_reverse($quizHistory) as $quiz): ?>
                '<?= date('M j', strtotime($quiz['created_at'])) ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Quiz Scores (%)',
                data: [
                    <?php foreach (array_reverse($quizHistory) as $quiz): ?>
                    <?= $quiz['percentage'] ?>,
                    <?php endforeach; ?>
                ],
                borderColor: 'rgb(102, 126, 234)',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Quiz Performance Trend'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>