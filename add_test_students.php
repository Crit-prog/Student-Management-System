<?php
include 'db_connect.php';

echo "<h2>Adding Test Students</h2>";

// First, let's check what classes exist
$classes_query = "SELECT id, CONCAT(level, ' - ', section) as class_name FROM classes ORDER BY level, section";
$classes = $conn->query($classes_query);
if(!$classes || $classes->num_rows == 0) {
    $classes_query = "SELECT id, class as class_name FROM class_list ORDER BY class";
    $classes = $conn->query($classes_query);
}

if($classes && $classes->num_rows > 0) {
    echo "<h3>Available Classes:</h3>";
    $class_options = [];
    while($class = $classes->fetch_assoc()) {
        echo "<p>ID: {$class['id']} - {$class['class_name']}</p>";
        $class_options[] = $class['id'];
    }
    
    // Check if students already exist
    $student_count = $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'];
    
    if($student_count == 0) {
        echo "<h3>Adding Test Students:</h3>";
        
        $test_students = [
            ['STU001', 'John', 'Doe', 'Male', 'john.doe@email.com'],
            ['STU002', 'Jane', 'Smith', 'Female', 'jane.smith@email.com'],
            ['STU003', 'Mike', 'Johnson', 'Male', 'mike.johnson@email.com'],
            ['STU004', 'Sarah', 'Williams', 'Female', 'sarah.williams@email.com'],
            ['STU005', 'David', 'Brown', 'Male', 'david.brown@email.com'],
            ['STU006', 'Lisa', 'Davis', 'Female', 'lisa.davis@email.com'],
            ['STU007', 'Tom', 'Wilson', 'Male', 'tom.wilson@email.com'],
            ['STU008', 'Amy', 'Taylor', 'Female', 'amy.taylor@email.com'],
            ['STU009', 'Chris', 'Anderson', 'Male', 'chris.anderson@email.com'],
            ['STU010', 'Emma', 'Thomas', 'Female', 'emma.thomas@email.com']
        ];
        
        foreach($test_students as $index => $student) {
            // Distribute students across available classes
            $class_id = $class_options[$index % count($class_options)];
            
            $sql = "INSERT INTO students (student_code, firstname, lastname, gender, email, class_id, date_created) 
                    VALUES ('{$student[0]}', '{$student[1]}', '{$student[2]}', '{$student[3]}', '{$student[4]}', '$class_id', NOW())";
            
            if($conn->query($sql)) {
                echo "<p style='color: green;'>✓ Added student: {$student[1]} {$student[2]} (Class ID: $class_id)</p>";
            } else {
                echo "<p style='color: red;'>✗ Error adding {$student[1]} {$student[2]}: " . $conn->error . "</p>";
            }
        }
    } else {
        echo "<p>Students already exist ($student_count students found)</p>";
    }
} else {
    echo "<p style='color: red;'>No classes found. Please add classes first.</p>";
}

echo "<h3>Current Data Summary:</h3>";
echo "<p>Students: " . $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] . " records</p>";
echo "<p>Classes: " . $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'] . " + " . $conn->query("SELECT COUNT(*) as count FROM class_list")->fetch_assoc()['count'] . " records</p>";
echo "<p>Subjects: " . $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'] . " + " . $conn->query("SELECT COUNT(*) as count FROM subject_list")->fetch_assoc()['count'] . " records</p>";

echo "<p><a href='admin_manage_scores.php'>Go to Manage Scores</a></p>";
?>