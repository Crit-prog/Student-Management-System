<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

ob_start();
date_default_timezone_set("Asia/Manila");

// Check if action is set
if(!isset($_GET['action'])) {
    echo "Error: No action specified";
    exit;
}

$action = $_GET['action'];

// Include database connection
include 'db_connect.php';

// Check database connection
if(!$conn) {
    echo "Error: Database connection failed";
    exit;
}

if($action == 'load_assessments'){
    try {
        $where = "WHERE 1=1";
        
        if(!empty($_POST['subject_id'])) {
            $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
            $where .= " AND q.subject_id = '$subject_id'";
        }
        if(!empty($_POST['class_id'])) {
            $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
            $where .= " AND q.class_id = '$class_id'";
        }
        if(!empty($_POST['qtype'])) {
            $qtype = mysqli_real_escape_string($conn, $_POST['qtype']);
            $where .= " AND q.qtype = '$qtype'";
        }
        
        // Try to determine which tables to use for subjects and classes
        $subject_table = 'subjects';
        $subject_field = 'subject';
        $class_table = 'classes';
        $class_field = 'CONCAT(level, " - ", section)';
        
        // Check if subjects table has data, otherwise use subject_list
        $check_subjects = $conn->query("SELECT COUNT(*) as count FROM subjects");
        if(!$check_subjects || $check_subjects->fetch_assoc()['count'] == 0) {
            $subject_table = 'subject_list';
        }
        
        // Check if classes table has data, otherwise use class_list
        $check_classes = $conn->query("SELECT COUNT(*) as count FROM classes");
        if(!$check_classes || $check_classes->fetch_assoc()['count'] == 0) {
            $class_table = 'class_list';
            $class_field = 'class';
        }
        
        $query = "
            SELECT q.*, 
                   COALESCE(s.$subject_field, 'Unknown Subject') as subject, 
                   COALESCE($class_field, 'Unknown Class') as class,
                   CASE 
                       WHEN q.qtype = 1 THEN 'Exam'
                       WHEN q.qtype = 2 THEN 'Quiz' 
                       WHEN q.qtype = 3 THEN 'Activity'
                       ELSE 'Unknown'
                   END as type_name
            FROM quiz_list q 
            LEFT JOIN $subject_table s ON q.subject_id = s.id 
            LEFT JOIN $class_table c ON q.class_id = c.id 
            $where 
            ORDER BY q.date_created DESC
        ";
        
        error_log("Executing query: " . $query);
        
        $assessments = $conn->query($query);
        
        if(!$assessments) {
            echo '<div class="alert alert-danger">
                    <h5>Database Query Error:</h5>
                    <p>' . htmlspecialchars($conn->error) . '</p>
                    <p><strong>Query:</strong> ' . htmlspecialchars($query) . '</p>
                  </div>';
            exit;
        }
        
        if($assessments->num_rows > 0) {
            echo '<div class="alert alert-success">Found ' . $assessments->num_rows . ' assessment(s)</div>';
            echo '<div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Class</th>
                                <th>Total Score</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
            
            while($row = $assessments->fetch_assoc()) {
                echo '<tr>
                        <td>'.htmlspecialchars($row['title']).'</td>
                        <td><span class="badge badge-primary">'.htmlspecialchars($row['type_name']).'</span></td>
                        <td>'.htmlspecialchars($row['subject']).'</td>
                        <td>'.htmlspecialchars($row['class']).'</td>
                        <td>'.$row['total_score'].'</td>
                        <td>'.date('M d, Y', strtotime($row['assessment_date'])).'</td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="enter_scores('.$row['id'].')">
                                <i class="fas fa-edit"></i> Enter Scores
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="delete_assessment('.$row['id'].')">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </td>
                      </tr>';
            }
            
            echo '</tbody></table></div>';
        } else {
            echo '<div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i> No assessments found with the selected criteria.
                    <br><br>
                    <button class="btn btn-primary" onclick="add_new_assessment()">
                        <i class="fas fa-plus"></i> Add Your First Assessment
                    </button>
                  </div>';
        }
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">
                <h5>PHP Error:</h5>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p><strong>File:</strong> ' . $e->getFile() . '</p>
                <p><strong>Line:</strong> ' . $e->getLine() . '</p>
              </div>';
        error_log("Error in load_assessments: " . $e->getMessage());
    }
    exit;
}

if($action == 'save_assessment'){
    try {
        // Check if all required fields are present
        $required_fields = ['title', 'qtype', 'subject_id', 'class_id', 'total_score', 'assessment_date'];
        foreach($required_fields as $field) {
            if(!isset($_POST[$field]) || empty($_POST[$field])) {
                echo "Error: Missing required field - $field";
                exit;
            }
        }
        
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $qtype = mysqli_real_escape_string($conn, $_POST['qtype']);
        $subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
        $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
        $total_score = mysqli_real_escape_string($conn, $_POST['total_score']);
        $assessment_date = mysqli_real_escape_string($conn, $_POST['assessment_date']);
        $description = mysqli_real_escape_string($conn, $_POST['description'] ?? '');
        
        $query = "INSERT INTO quiz_list (title, qtype, subject_id, class_id, total_score, assessment_date, description, date_created) 
                  VALUES ('$title', '$qtype', '$subject_id', '$class_id', '$total_score', '$assessment_date', '$description', NOW())";
        
        error_log("Save assessment query: " . $query);
        
        $save = $conn->query($query);
        
        if($save) {
            echo 1;
        } else {
            echo "Database error: " . $conn->error;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        error_log("Error in save_assessment: " . $e->getMessage());
    }
    exit;
}

if($action == 'load_score_entry'){
    try {
        $assessment_id = mysqli_real_escape_string($conn, $_POST['assessment_id']);
        
        // Determine which tables to use
        $subject_table = 'subjects';
        $subject_field = 'subject';
        $class_table = 'classes';
        $class_field = 'CONCAT(level, " - ", section)';
        
        $check_subjects = $conn->query("SELECT COUNT(*) as count FROM subjects");
        if(!$check_subjects || $check_subjects->fetch_assoc()['count'] == 0) {
            $subject_table = 'subject_list';
        }
        
        $check_classes = $conn->query("SELECT COUNT(*) as count FROM classes");
        if(!$check_classes || $check_classes->fetch_assoc()['count'] == 0) {
            $class_table = 'class_list';
            $class_field = 'class';
        }
        
        // Get assessment details
        $assessment = $conn->query("
            SELECT q.*, s.$subject_field as subject, $class_field as class,
            CASE 
                WHEN q.qtype = 1 THEN 'Exam'
                WHEN q.qtype = 2 THEN 'Quiz' 
                WHEN q.qtype = 3 THEN 'Activity'
                ELSE 'Unknown'
            END as type_name
            FROM quiz_list q
                        LEFT JOIN $subject_table s ON q.subject_id = s.id 
            LEFT JOIN $class_table c ON q.class_id = c.id 
            WHERE q.id = '$assessment_id'
        ")->fetch_assoc();
        
        if(!$assessment) {
            echo '<div class="alert alert-danger">Assessment not found</div>';
            exit;
        }
        
        // Get students in the class
        $students = $conn->query("
            SELECT st.*, 
            COALESCE(qsl.score, '') as current_score,
            COALESCE(qsl.id, 0) as score_id
            FROM students st 
            LEFT JOIN quiz_student_list qsl ON st.id = qsl.student_id AND qsl.quiz_id = '$assessment_id'
            WHERE st.class_id = '".$assessment['class_id']."'
            ORDER BY st.lastname, st.firstname
        ");
        
        if(!$students || $students->num_rows == 0) {
            echo '<div class="alert alert-warning">No students found in this class</div>';
            exit;
        }
        
        echo '<div class="assessment-info mb-3">
                <div class="row">
                    <div class="col-md-3"><strong>Assessment:</strong> '.htmlspecialchars($assessment['title']).'</div>
                    <div class="col-md-2"><strong>Type:</strong> '.htmlspecialchars($assessment['type_name']).'</div>
                    <div class="col-md-3"><strong>Subject:</strong> '.htmlspecialchars($assessment['subject']).'</div>
                    <div class="col-md-2"><strong>Class:</strong> '.htmlspecialchars($assessment['class']).'</div>
                    <div class="col-md-2"><strong>Total Score:</strong> '.$assessment['total_score'].'</div>
                </div>
              </div>';
        
        echo '<form id="scores-form">
                <input type="hidden" name="assessment_id" value="'.$assessment_id.'">
                <input type="hidden" name="total_score" value="'.$assessment['total_score'].'">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Student ID</th>
                                <th>Student Name</th>
                                <th>Score (out of '.$assessment['total_score'].')</th>
                                <th>Percentage</th>
                                <th>Grade</th>
                            </tr>
                        </thead>
                        <tbody>';
        
        while($student = $students->fetch_assoc()) {
            $percentage = '';
            $grade = '';
            if($student['current_score'] !== '') {
                $percentage = round(($student['current_score'] / $assessment['total_score']) * 100, 2);
                if($percentage >= 90) $grade = 'A';
                elseif($percentage >= 80) $grade = 'B';
                elseif($percentage >= 70) $grade = 'C';
                elseif($percentage >= 60) $grade = 'D';
                else $grade = 'F';
            }
            
            echo '<tr>
                    <td>'.htmlspecialchars($student['student_code']).'</td>
                    <td>'.htmlspecialchars($student['lastname']).', '.htmlspecialchars($student['firstname']).'</td>
                    <td>
                        <input type="number" class="form-control score-input" 
                               name="scores['.$student['id'].']" 
                               value="'.$student['current_score'].'" 
                               min="0" max="'.$assessment['total_score'].'" 
                               step="0.5"
                               data-student-id="'.$student['id'].'"
                               data-total="'.$assessment['total_score'].'">
                        <input type="hidden" name="score_ids['.$student['id'].']" value="'.$student['score_id'].'">
                    </td>
                    <td class="percentage-cell">'.$percentage.'%</td>
                    <td class="grade-cell">'.$grade.'</td>
                  </tr>';
        }
        
        echo '</tbody>
                    </table>
                </div>
                <div class="text-right mt-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save All Scores
                    </button>
                </div>
              </form>';
        
        echo '<script>
                // Auto-calculate percentage and grade when score is entered
                $(".score-input").on("input", function() {
                    var score = parseFloat($(this).val()) || 0;
                    var total = parseFloat($(this).data("total"));
                    var percentage = Math.round((score / total) * 100 * 100) / 100;
                    var grade = "";
                    
                    if(percentage >= 90) grade = "A";
                    else if(percentage >= 80) grade = "B";
                    else if(percentage >= 70) grade = "C";
                    else if(percentage >= 60) grade = "D";
                    else grade = "F";
                    
                    $(this).closest("tr").find(".percentage-cell").text(percentage + "%");
                    $(this).closest("tr").find(".grade-cell").text(grade);
                });
                
                // Handle form submission
                $("#scores-form").submit(function(e) {
                    e.preventDefault();
                    $.ajax({
                        url: "ajax_fixed.php?action=save_scores",
                        method: "POST",
                        data: $(this).serialize(),
                        success: function(resp) {
                            if(resp == 1) {
                                alert("Scores saved successfully!");
                                $("#score_modal").modal("hide");
                            } else {
                                alert("Error saving scores: " + resp);
                            }
                        },
                        error: function(xhr, status, error) {
                            alert("Error saving scores: " + error);
                        }
                    });
                });
              </script>';
        
    } catch (Exception $e) {
        echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        error_log("Error in load_score_entry: " . $e->getMessage());
    }
    exit;
}

if($action == 'save_scores'){
    try {
        $assessment_id = mysqli_real_escape_string($conn, $_POST['assessment_id']);
        $total_score = mysqli_real_escape_string($conn, $_POST['total_score']);
        $scores = $_POST['scores'];
        $score_ids = $_POST['score_ids'];
        
        $success_count = 0;
        $error_count = 0;
        
        foreach($scores as $student_id => $score) {
            if($score !== '') {
                $student_id = mysqli_real_escape_string($conn, $student_id);
                $score = mysqli_real_escape_string($conn, $score);
                $score_id = mysqli_real_escape_string($conn, $score_ids[$student_id]);
                $percentage = round(($score / $total_score) * 100, 2);
                
                if($score_id > 0) {
                    // Update existing score
                    $update = $conn->query("
                        UPDATE quiz_student_list 
                        SET score = '$score', 
                            total_score = '$total_score',
                            percentage = '$percentage',
                            date_updated = NOW() 
                        WHERE id = '$score_id'
                    ");
                    if($update) $success_count++;
                    else $error_count++;
                } else {
                    // Insert new score
                    $insert = $conn->query("
                        INSERT INTO quiz_student_list 
                        (quiz_id, student_id, score, total_score, percentage, date_updated) 
                        VALUES 
                        ('$assessment_id', '$student_id', '$score', '$total_score', '$percentage', NOW())
                    ");
                    if($insert) $success_count++;
                    else $error_count++;
                }
            }
        }
        
        if($error_count == 0) {
            echo 1;
        } else {
            echo "Saved: $success_count, Errors: $error_count";
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

if($action == 'delete_assessment'){
    try {
        $id = mysqli_real_escape_string($conn, $_POST['id']);
        
        // Delete related scores first
        $conn->query("DELETE FROM quiz_student_list WHERE quiz_id = '$id'");
        
        // Delete the assessment
        $delete = $conn->query("DELETE FROM quiz_list WHERE id = '$id'");
        
        if($delete) {
            echo 1;
        } else {
            echo "Database error: " . $conn->error;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

ob_end_flush();
?>
