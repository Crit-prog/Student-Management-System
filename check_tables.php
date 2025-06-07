<?php
include 'db_connect.php';

echo "<h2>Existing Tables in Database</h2>";

// Show all tables
$result = $conn->query("SHOW TABLES");
if($result) {
    echo "<h3>All Tables:</h3><ul>";
    while($row = $result->fetch_array()) {
        $table_name = $row[0];
        echo "<li><strong>$table_name</strong>";
        
        // Show structure of each table
        $desc = $conn->query("DESCRIBE $table_name");
        if($desc) {
            echo "<ul>";
            while($col = $desc->fetch_assoc()) {
                echo "<li>{$col['Field']} - {$col['Type']}</li>";
            }
            echo "</ul>";
        }
        echo "</li>";
    }
    echo "</ul>";
} else {
    echo "Error showing tables: " . $conn->error;
}

// Check for similar table names
echo "<h3>Looking for similar table names:</h3>";
$possible_names = ['subject', 'subjects', 'subject_list', 'class', 'classes', 'class_list'];
foreach($possible_names as $name) {
    $check = $conn->query("SHOW TABLES LIKE '$name'");
    if($check && $check->num_rows > 0) {
        echo "<p style='color: green;'>Found: $name</p>";
    }
}
?>