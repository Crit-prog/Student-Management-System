<?php
include 'db_connect.php';

echo "<h2>Adding Test Data</h2>";

// Check which tables have data and add some if needed
$subjects_count = $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'];
$subject_list_count = $conn->query("SELECT COUNT(*) as count FROM subject_list")->fetch_assoc()['count'];

if($subjects_count == 0 && $subject_list_count == 0) {
    echo "<h3>Adding subjects to subjects table:</h3>";
    $test_subjects = [
        ['MATH101', 'Mathematics', 'Basic Mathematics'],
        ['ENG101', 'English', 'English Language'],
        ['SCI101', 'Science', 'General Science'],
        ['HIST101', 'History', 'World History'],
        ['CS101', 'Computer Science', 'Programming and IT']
    ];
    
    foreach($test_subjects as $subject) {
        $sql = "INSERT INTO subjects (subject_code, subject, description) VALUES ('{$subject[0]}', '{$subject[1]}', '{$subject[2]}')";
        if($conn->query($sql)) {
            echo "<p style='color: green;'>✓ Added subject: {$subject[1]}</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding {$subject[1]}: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p>Subjects already exist (subjects: $subjects_count, subject_list: $subject_list_count)</p>";
}

$classes_count = $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'];
$class_list_count = $conn->query("SELECT COUNT(*) as count FROM class_list")->fetch_assoc()['count'];

if($classes_count == 0 && $class_list_count == 0) {
    echo "<h3>Adding classes to classes table:</h3>";
    $test_classes = [
        ['Grade 1', 'A'],
        ['Grade 1', 'B'],
        ['Grade 2', 'A'],
        ['Grade 2', 'B'],
        ['Grade 3', 'A']
    ];
    
    foreach($test_classes as $class) {
        $sql = "INSERT INTO classes (level, section) VALUES ('{$class[0]}', '{$class[1]}')";
        if($conn->query($sql)) {
            echo "<p style='color: green;'>✓ Added class: {$class[0]} - {$class[1]}</p>";
        } else {
            echo "<p style='color: red;'>✗ Error adding {$class[0]} - {$class[1]}: " . $conn->error . "</p>";
        }
    }
} else {
    echo "<p>Classes already exist (classes: $classes_count, class_list: $class_list_count)</p>";
}

echo "<h3>Current Data Summary:</h3>";
echo "<p>Subjects table: " . $conn->query("SELECT COUNT(*) as count FROM subjects")->fetch_assoc()['count'] . " records</p>";
echo "<p>Subject_list table: " . $conn->query("SELECT COUNT(*) as count FROM subject_list")->fetch_assoc()['count'] . " records</p>";
echo "<p>Classes table: " . $conn->query("SELECT COUNT(*) as count FROM classes")->fetch_assoc()['count'] . " records</p>";
echo "<p>Class_list table: " . $conn->query("SELECT COUNT(*) as count FROM class_list")->fetch_assoc()['count'] . " records</p>";
echo "<p>Students table: " . $conn->query("SELECT COUNT(*) as count FROM students")->fetch_assoc()['count'] . " records</p>";

echo "<p><a href='admin_manage_scores_fixed.php'>Go to Manage Scores (Fixed Version)</a></p>";
?>