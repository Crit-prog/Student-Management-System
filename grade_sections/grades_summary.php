<?php
$student_id = $_SESSION['rs_id'];

// Get overall statistics
$exam_stats = $conn->query("
    SELECT 
        COUNT(*) as total_count,
        AVG(ROUND((qa.score/qa.total_score)*100, 2)) as avg_percentage
    FROM quiz_student_question qa 
    INNER JOIN quiz_list q ON qa.quiz_id = q.id 
    WHERE qa.student_id = '$student_id' AND q.qtype = 1
")->fetch_assoc();

$quiz_stats = $conn->query("
    SELECT 
        COUNT(*) as total_count,
        AVG(ROUND((qa.score/qa.total_score)*100, 2)) as avg_percentage
    FROM quiz_student_question qa 
    INNER JOIN quiz_list q ON qa.quiz_id = q.id 
    WHERE qa.student_id = '$student_id' AND q.qtype = 2
")->fetch_assoc();

$activity_stats = $conn->query("
    SELECT 
        COUNT(*) as total_count,
        AVG(ROUND((qa.score/qa.total_score)*100, 2)) as avg_percentage
    FROM quiz_student_question qa 
    INNER JOIN quiz_list q ON qa.quiz_id = q.id 
    WHERE qa.student_id = '$student_id' AND q.qtype = 3
")->fetch_assoc();
?>

<div class="row">
    <!-- Summary Cards -->
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h4><?php echo $exam_stats['total_count'] ?></h4>
                <p>Total Exams</p>
                <small>Average: <?php echo round($exam_stats['avg_percentage'] ?? 0, 1) ?>%</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body text-center">
                <h4><?php echo $quiz_stats['total_count'] ?></h4>
                <p>Total Quizzes</p>
                <small>Average: <?php echo round($quiz_stats['avg_percentage'] ?? 0, 1) ?>%</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h4><?php echo $activity_stats['total_count'] ?></h4>
                <p>Total Activities</p>
                <small>Average: <?php echo round($activity_stats['avg_percentage'] ?? 0, 1) ?>%</small>
            </div>
        </div>
    </div>
</div>

<!-- Overall Performance Chart -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5>Overall Performance</h5>
            </div>
            <div class="card-body">
                <?php
                $overall_avg = (($exam_stats['avg_percentage'] ?? 0) + ($quiz_stats['avg_percentage'] ?? 0) + ($activity_stats['avg_percentage'] ?? 0)) / 3;
                $grade_class = '';
                $grade_letter = '';
                
                if($overall_avg >= 90) { $grade_class = 'success'; $grade_letter = 'A'; }
                elseif($overall_avg >= 80) { $grade_class = 'info'; $grade_letter = 'B'; }
                elseif($overall_avg >= 70) { $grade_class = 'warning'; $grade_letter = 'C'; }
                elseif($overall_avg >= 60) { $grade_class = 'secondary'; $grade_letter = 'D'; }
                else { $grade_class = 'danger'; $grade_letter = 'F'; }
                ?>
                
                <div class="text-center">
                    <h2 class="text-<?php echo $grade_class ?>">
                        <?php echo round($overall_avg, 1) ?>% 
                        <span class="badge badge-<?php echo $grade_class ?> ml-2"><?php echo $grade_letter ?></span>
                    </h2>
                    <p>Overall Average Grade</p>
                </div>
            </div>
        </div>
    </div>
</div>
