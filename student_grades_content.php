<div class="col-lg-12">
    <div class="card card-outline card-success">
        <div class="card-header bg-success">
            <h3 class="card-title text-white"><b><i class="fas fa-chart-line"></i> My Grades Overview</b></h3>
        </div>
        <div class="card-body">
            <!-- Very visible test content -->
            <div class="alert alert-success alert-dismissible" style="font-size: 18px; padding: 20px;">
                <h4><i class="fas fa-check-circle"></i> SUCCESS!</h4>
                <p><strong>The Grades section is now working!</strong></p>
                <hr>
                <?php 
                $student_id = $_SESSION['rs_id'];
                $student_qry = $conn->query("SELECT * FROM students WHERE id = '$student_id'");
                $student = $student_qry->fetch_assoc();
                ?>
                <p><strong>Student:</strong> <?php echo $student['firstname'] . ' ' . $student['lastname'] ?></p>
                <p><strong>Student ID:</strong> <?php echo $student['student_code'] ?></p>
            </div>

            <!-- Simple grades display -->
            <div class="row">
                <div class="col-md-4">
                    <div class="info-box bg-info">
                        <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Exams Taken</span>
                            <span class="info-box-number">
                                <?php 
                                $exam_count = $conn->query("SELECT COUNT(*) as count FROM quiz_student_question qa INNER JOIN quiz_list q ON qa.quiz_id = q.id WHERE qa.student_id = '$student_id' AND q.qtype = 1")->fetch_assoc();
                                echo $exam_count['count'];
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-box bg-success">
                        <span class="info-box-icon"><i class="fas fa-question-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Quizzes Taken</span>
                            <span class="info-box-number">
                                <?php 
                                $quiz_count = $conn->query("SELECT COUNT(*) as count FROM quiz_student_question qa INNER JOIN quiz_list q ON qa.quiz_id = q.id WHERE qa.student_id = '$student_id' AND q.qtype = 2")->fetch_assoc();
                                echo $quiz_count['count'];
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="info-box bg-warning">
                        <span class="info-box-icon"><i class="fas fa-tasks"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Activities Done</span>
                            <span class="info-box-number">
                                <?php 
                                $activity_count = $conn->query("SELECT COUNT(*) as count FROM quiz_student_question qa INNER JOIN quiz_list q ON qa.quiz_id = q.id WHERE qa.student_id = '$student_id' AND q.qtype = 3")->fetch_assoc();
                                echo $activity_count['count'];
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test visibility -->
            <div class="mt-3 p-3" style="background-color: #f0f8ff; border: 2px solid #007bff;">
                <h5>🎉 If you can see this, the My Grades tab is working perfectly!</h5>
                <p>Current time: <?php echo date('Y-m-d H:i:s'); ?></p>
            </div>
        </div>
    </div>
</div>
