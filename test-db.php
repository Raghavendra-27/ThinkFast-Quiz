<?php
echo "<h2>Database Connection Test</h2>";
echo "<p>Testing connection to quiz_app database...</p>";

try {
    require_once 'config/database.php';
    
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Users table exists</p>";
        
        // Count users
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch()['count'];
        echo "<p>👥 Current users in database: <strong>$count</strong></p>";
        
    } else {
        echo "<p style='color: orange;'>⚠️ Users table does not exist</p>";
        echo "<p>Run the database/init.sql script to create tables</p>";
    }
    
    // Test if quiz_results table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'quiz_results'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green;'>✅ Quiz results table exists</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Quiz results table does not exist</p>";
    }
    
    echo "<hr>";
    echo "<h3>Database Information:</h3>";
    $stmt = $pdo->query("SELECT DATABASE() as db_name");
    $dbName = $stmt->fetch()['db_name'];
    echo "<p>Database: <strong>$dbName</strong></p>";
    
    $stmt = $pdo->query("SELECT VERSION() as version");
    $version = $stmt->fetch()['version'];
    echo "<p>MySQL Version: <strong>$version</strong></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>❌ Database connection failed:</p>";
    echo "<p style='color: red; background: #ffe6e6; padding: 10px; border-radius: 5px;'>" . $e->getMessage() . "</p>";
    
    echo "<h3>Troubleshooting Steps:</h3>";
    echo "<ol>";
    echo "<li>Make sure MySQL server is running</li>";
    echo "<li>Check if database 'quiz_app' exists</li>";
    echo "<li>Verify username and password in config/database.php</li>";
    echo "<li>Check if MySQL is running on port 3306</li>";
    echo "</ol>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
}
p {
    margin: 10px 0;
    line-height: 1.5;
}
ol {
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}
</style>