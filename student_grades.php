<!DOCTYPE html>
<html lang="en">
<?php 
session_start();
include 'db_connect.php';

if(!isset($_SESSION['rs_id']))
    header('location:login.php');

ob_start();
if(!isset($_SESSION['system'])){
    $system = $conn->query("SELECT * FROM system_settings")->fetch_array();
    foreach($system as $k => $v){
        $_SESSION['system'][$k] = $v;
    }
}
ob_end_flush();
include 'header.php';

$student_id = $_SESSION['rs_id'];
$student_qry = $conn->query("SELECT * FROM students WHERE id = '$student_id'");
$student = $student_qry->fetch_assoc();
?>
<body class="hold-transition layout-fixed layout-navbar-fixed layout-footer-fixed sidebar-collapse">
<div class="wrapper">
  <?php include 'topbar.php' ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>My Grades</h1>
          </div>
          <div class="col-sm-6">
            <div class="float-right">
              <a href="student_results.php" class="btn btn-success">
                <i class="fas fa-arrow-left"></i> Back to Results
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">
        <!-- Student Info Card -->
        <div class="row">
          <div class="col-12">
            <div class="card card-success">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user-graduate"></i> Student Information</h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-6">
                    <strong>Name:</strong> <?php echo $student['firstname'] . ' ' . $student['lastname'] ?>
                  </div>
                  <div class="col-md-6">
                    <strong>Student ID:</strong> <?php echo $student['student_code'] ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Grades Summary -->
        <div class="row">
          <div class="col-md-4">
            <div class="info-box bg-info">
              <span class="info-box-icon"><i class="fas fa-file-alt"></i></span>
              <div class="info-box-content">
                <span class="info-box-text">Exams Taken</span>
                <span class="info-box-number">
                  <?php 
                  $exam_count = $conn->query("SELECT COUNT(*) as count FROM quiz_student_list qsl INNER JOIN quiz_list q ON qsl.quiz_id = q.id WHERE qsl.student_id = '$student_id' AND q.qtype = 1")->fetch_assoc()['count'];
                  echo $exam_count;
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
                  $quiz_count = $conn->query("SELECT COUNT(*) as count FROM quiz_student_list qsl INNER JOIN quiz_list q ON qsl.quiz_id = q.id WHERE qsl.student_id = '$student_id' AND q.qtype = 2")->fetch_assoc()['count'];
                  echo $quiz_count;
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
                  $activity_count = $conn->query("SELECT COUNT(*) as count FROM quiz_student_list qsl INNER JOIN quiz_list q ON qsl.quiz_id = q.id WHERE qsl.student_id = '$student_id' AND q.qtype = 3")->fetch_assoc()['count'];
                  echo $activity_count;
                  ?>
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Grades -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fas fa-chart-bar"></i> Grade Details</h3>
              </div>
              <div class="card-body">
                <?php
                // Try to find the correct table for grades
                $grades_found = false;
                $possible_grade_queries = [
                    // Option 1: quiz_student_list table
                    "SELECT 
                        q.title,
                        CASE 
                          WHEN q.qtype = 1 THEN 'Exam'
                          WHEN q.qtype = 2 THEN 'Quiz' 
                          WHEN q.qtype = 3 THEN 'Activity'
                          ELSE 'Unknown'
                        END as type,
                        s.subject,
                        qsl.score,
                        qsl.total_score,
                        ROUND((qsl.score/qsl.total_score)*100, 2) as percentage,
                        qsl.date_updated
                      FROM quiz_student_list qsl 
                      INNER JOIN quiz_list q ON qsl.quiz_id = q.id 
                      INNER JOIN subject_list s ON q.subject_id = s.id
                      WHERE qsl.student_id = '$student_id'
                      ORDER BY qsl.date_updated DESC",
                    
                    // Option 2: quiz_answers table
                    "SELECT 
                        q.title,
                        CASE 
                          WHEN q.qtype = 1 THEN 'Exam'
                          WHEN q.qtype = 2 THEN 'Quiz' 
                          WHEN q.qtype = 3 THEN 'Activity'
                          ELSE 'Unknown'
                        END as type,
                        s.subject,
                        SUM(qa.is_right) as score,
                        COUNT(*) as total_score,
                        ROUND((SUM(qa.is_right)/COUNT(*))*100, 2) as percentage,
                        MAX(qa.date_updated) as date_updated
                      FROM quiz_answers qa 
                      INNER JOIN quiz_list q ON qa.quiz_id = q.id 
                      INNER JOIN subject_list s ON q.subject_id = s.id
                      WHERE qa.student_id = '$student_id'
                      GROUP BY qa.quiz_id, q.title, q.qtype, s.subject
                      ORDER BY MAX(qa.date_updated) DESC"
                ];
                
                foreach($possible_grade_queries as $query) {
                    try {
                        $grades_qry = $conn->query($query);
                        if($grades_qry && $grades_qry->num_rows > 0) {
                            $grades_found = true;
                            break;
                        }
                    } catch(Exception $e) {
                        continue;
                    }
                }
                ?>
                
                <table class="table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>Assessment</th>
                      <th>Type</th>
                      <th>Subject</th>
                      <th>Score</th>
                      <th>Total</th>
                      <th>Percentage</th>
                      <th>Grade</th>
                      <th>Date Taken</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    if($grades_found):
                      while($row = $grades_qry->fetch_assoc()):
                        $percentage = $row['percentage'];
                        $grade_letter = '';
                        $grade_class = '';
                        
                        if($percentage >= 90) { $grade_letter = 'A'; $grade_class = 'success'; }
                        elseif($percentage >= 80) { $grade_letter = 'B'; $grade_class = 'info'; }
                        elseif($percentage >= 70) { $grade_letter = 'C'; $grade_class = 'warning'; }
                        elseif($percentage >= 60) { $grade_letter = 'D'; $grade_class = 'secondary'; }
                        else { $grade_letter = 'F'; $grade_class = 'danger'; }
                    ?>
                    <tr>
                      <td><?php echo $row['title'] ?></td>
                      <td><span class="badge badge-primary"><?php echo $row['type'] ?></span></td>
                      <td><?php echo $row['subject'] ?></td>
                      <td><?php echo $row['score'] ?></td>
                      <td><?php echo $row['total_score'] ?></td>
                      <td><?php echo $percentage ?>%</td>
                      <td><span class="badge badge-<?php echo $grade_class ?>"><?php echo $grade_letter ?></span></td>
                      <td><?php echo date('M d, Y', strtotime($row['date_updated'])) ?></td>
                    </tr>
                    <?php 
                      endwhile;
                    else:
                    ?>
                    <tr>
                      <td colspan="8" class="text-center">
                        <div class="alert alert-info">
                          <i class="fas fa-info-circle"></i> 
                          No grades available yet, or unable to find the correct database table.
                          <br><small>Please check with your administrator if this persists.</small>
                        </div>
                      </td>
                    </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<?php include 'footer.php' ?>
</body>
</html>
