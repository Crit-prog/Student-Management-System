<?php
$student_id = $_SESSION['rs_id'];

// Get quiz results for the student
$quiz_qry = $conn->query("
    SELECT 
        q.title,
        q.id as quiz_id,
        qa.score,
        qa.total_score,
        qa.date_updated as date_taken,
        s.subject,
        ROUND((qa.score/qa.total_score)*100, 2) as percentage
    FROM quiz_student_question qa 
    INNER JOIN quiz_list q ON qa.quiz_id = q.id 
    INNER JOIN subject_list s ON q.subject_id = s.id
    WHERE qa.student_id = '$student_id' 
    AND q.qtype = 2
    ORDER BY qa.date_updated DESC
");
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Quiz Title</th>
                <th>Subject</th>
                <th>Score</th>
                <th>Total</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Date Taken</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($quiz_qry->num_rows > 0): ?>
                <?php while($row = $quiz_qry->fetch_assoc()): ?>
                    <?php 
                        $percentage = $row['percentage'];
                        $grade = '';
                        $grade_class = '';
                        
                        if($percentage >= 90) {
                            $grade = 'A';
                            $grade_class = 'badge-success';
                        } elseif($percentage >= 80) {
                            $grade = 'B';
                            $grade_class = 'badge-info';
                        } elseif($percentage >= 70) {
                            $grade = 'C';
                            $grade_class = 'badge-warning';
                        } elseif($percentage >= 60) {
                            $grade = 'D';
                            $grade_class = 'badge-secondary';
                        } else {
                            $grade = 'F';
                            $grade_class = 'badge-danger';
                        }
                    ?>
                    <tr>
                        <td><?php echo $row['title'] ?></td>
                        <td><?php echo $row['subject'] ?></td>
                        <td class="text-center"><?php echo $row['score'] ?></td>
                        <td class="text-center"><?php echo $row['total_score'] ?></td>
                        <td class="text-center"><?php echo $percentage ?>%</td>
                        <td class="text-center">
                            <span class="badge <?php echo $grade_class ?>"><?php echo $grade ?></span>
                        </td>
                        <td><?php echo date('M d, Y h:i A', strtotime($row['date_taken'])) ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="view_grade_details(<?php echo $row['quiz_id'] ?>, 'quiz')">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No quiz records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Quiz Statistics -->
<?php if($quiz_qry->num_rows > 0): ?>
    <?php
    // Reset query to calculate statistics
    $quiz_qry = $conn->query("
        SELECT 
            qa.score,
            qa.total_score,
            ROUND((qa.score/qa.total_score)*100, 2) as percentage
        FROM quiz_student_question qa 
        INNER JOIN quiz_list q ON qa.quiz_id = q.id 
        WHERE qa.student_id = '$student_id' 
        AND q.qtype = 2
    ");
    
    $total_quizzes = $quiz_qry->num_rows;
    $total_percentage = 0;
    $highest_score = 0;
    $lowest_score = 100;
    
    while($stat_row = $quiz_qry->fetch_assoc()) {
        $percentage = $stat_row['percentage'];
        $total_percentage += $percentage;
        
        if($percentage > $highest_score) {
            $highest_score = $percentage;
        }
        
        if($percentage < $lowest_score) {
            $lowest_score = $percentage;
        }
    }
    
    $average_score = round($total_percentage / $total_quizzes, 2);
    ?>
    
    <div class="row mt-3">
        <div class="col-md-3">
            <div class="info-box bg-info">
                <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Average Score</span>
                    <span class="info-box-number"><?php echo $average_score ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-success">
                <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Highest Score</span>
                    <span class="info-box-number"><?php echo $highest_score ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-warning">
                <span class="info-box-icon"><i class="fas fa-arrow-down"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Lowest Score</span>
                    <span class="info-box-number"><?php echo $lowest_score ?>%</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box bg-secondary">
                <span class="info-box-icon"><i class="fas fa-list"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Quizzes</span>
                    <span class="info-box-number"><?php echo $total_quizzes ?></span>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>