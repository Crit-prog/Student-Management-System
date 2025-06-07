<?php
$student_id = $_SESSION['rs_id'];

// Get activity results for the student
$activity_qry = $conn->query("
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
    AND q.qtype = 3
    ORDER BY qa.date_updated DESC
");
?>

<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="thead-dark">
            <tr>
                <th>Activity Title</th>
                <th>Subject</th>
                <th>Score</th>
                <th>Total</th>
                <th>Percentage</th>
                <th>Grade</th>
                <th>Date Submitted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if($activity_qry->num_rows > 0): ?>
                <?php while($row = $activity_qry->fetch_assoc()): ?>
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
                            <button class="btn btn-sm btn-outline-primary" onclick="view_grade_details(<?php echo $row['quiz_id'] ?>, 'activity')">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">No activity records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>