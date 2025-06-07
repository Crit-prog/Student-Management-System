<?php
include 'db_connect.php';

echo "<h2>Database Connection Test</h2>";

// Test connection
if($conn) {
    echo "<p style='color: green;'>✓ Database connection successful</p>";
} else {
    echo "<p style='color: red;'>✗ Database connection failed</p>";
    exit;
}

// Check if tables exist
$tables_to_check = ['subject_list', 'class_list', 'quiz_list', 'students'];

foreach($tables_to_check as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if($result && $result->num_rows > 0) {
        echo "<p style='color: green;'>✓ Table '$table' exists</p>";
        
        // Count records
        $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            echo "<p style='margin-left: 20px;'>Records: $count</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ Table '$table' does not exist</p>";
    }
}

// Test a simple query
echo "<h3>Sample Data:</h3>";
$subjects = $conn->query("SELECT * FROM subject_list LIMIT 5");
if($subjects && $subjects->num_rows > 0) {
    echo "<h4>Subjects:</h4><ul>";
    while($row = $subjects->fetch_assoc()) {
        echo "<li>ID: {$row['id']}, Subject: {$row['subject']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No subjects found</p>";
}

$classes = $conn->query("SELECT * FROM class_list LIMIT 5");
if($classes && $classes->num_rows > 0) {
    echo "<h4>Classes:</h4><ul>";
    while($row = $classes->fetch_assoc()) {
        echo "<li>ID: {$row['id']}, Class: {$row['class']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No classes found</p>";
}
?>