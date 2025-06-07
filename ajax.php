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

// Add test action for debugging
if($action == 'test'){
	echo "AJAX connection working - " . date('Y-m-d H:i:s');
	exit;
}

// Include database connection first
include 'db_connect.php';

// Check database connection
if(!$conn) {
	echo "Error: Database connection failed";
	exit;
}

if($action == 'load_assessments'){
	try {
		// Debug: Show what data we received
		error_log("Load assessments called with: " . print_r($_POST, true));
		
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
		
		// Check if tables exist first
		$table_check = $conn->query("SHOW TABLES LIKE 'quiz_list'");
		if($table_check->num_rows == 0) {
			echo '<div class="alert alert-warning">
					<h5>Database Setup Required</h5>
					<p>The quiz_list table does not exist. Please create it first:</p>
					<pre>CREATE TABLE quiz_list (
	id int(11) NOT NULL AUTO_INCREMENT,
	title varchar(255) NOT NULL,
	qtype tinyint(4) NOT NULL,
	subject_id int(11) NOT NULL,
	class_id int(11) NOT NULL,
	total_score int(11) NOT NULL DEFAULT 100,
	assessment_date date NOT NULL,
	description text,
	date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (id)
);</pre>
				  </div>';
			exit;
		}
		
		$query = "
			SELECT q.*, 
				   COALESCE(s.subject, 'Unknown Subject') as subject, 
				   COALESCE(c.class, 'Unknown Class') as class,
				   CASE 
					   WHEN q.qtype = 1 THEN 'Exam'
					   WHEN q.qtype = 2 THEN 'Quiz' 
					   WHEN q.qtype = 3 THEN 'Activity'
					   ELSE 'Unknown'
				   END as type_name
			FROM quiz_list q 
			LEFT JOIN subject_list s ON q.subject_id = s.id 
			LEFT JOIN class_list c ON q.class_id = c.id 
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

// Include admin_class.php for other functions
include 'admin_class.php';
$crud = new Action();

if($action == 'login'){
	$login = $crud->login();
	if($login)
		echo $login;
}
if($action == 'login2'){
	$login = $crud->login2();
	if($login)
		echo $login;
}
if($action == 'logout'){
	$logout = $crud->logout();
	if($logout)
		echo $logout;
}
if($action == 'logout2'){
	$logout = $crud->logout2();
	if($logout)
		echo $logout;
}

if($action == 'signup'){
	$save = $crud->signup();
	if($save)
		echo $save;
}
if($action == 'save_user'){
	$save = $crud->save_user();
	if($save)
		echo $save;
}
if($action == 'update_user'){
	$save = $crud->update_user();
	if($save)
		echo $save;
}
if($action == 'delete_user'){
	$save = $crud->delete_user();
	if($save)
		echo $save;
}
if($action == 'save_class'){
	$save = $crud->save_class();
	if($save)
		echo $save;
}
if($action == 'delete_class'){
	$save = $crud->delete_class();
	if($save)
		echo $save;
}
if($action == 'save_subject'){
	$save = $crud->save_subject();
	if($save)
		echo $save;
}
if($action == 'delete_subject'){
	$save = $crud->delete_subject();
	if($save)
		echo $save;
}
if($action == 'save_student'){
	$save = $crud->save_student();
	if($save)
		echo $save;
}
if($action == 'delete_student'){
	$save = $crud->delete_student();
	if($save)
		echo $save;
}
if($action == 'save_result'){
	$save = $crud->save_result();
	if($save)
		echo $save;
}
if($action == 'delete_result'){
	$save = $crud->delete_result();
	if($save)
		echo $save;
}

// New function to load students for assignment
if($action == 'load_students_for_assignment'){
	$class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
	$assignment_type = $_POST['assignment_type'];
	
	// Query students from the classes table relationship
	$students_query = "
		SELECT s.id, s.student_code, s.firstname, s.lastname, s.gender 
		FROM students s
		WHERE s.class_id = '$class_id' 
		ORDER BY s.lastname, s.firstname
	";
	
	$students = $conn->query($students_query);
	
	// Debug information
	if(!$students) {
		echo '<div class="alert alert-danger">
				<strong>Database Error:</strong> ' . $conn->error . '
				<br><strong>Query:</strong> ' . htmlspecialchars($students_query) . '
			  </div>';
		exit;
	}
	
	if($students->num_rows > 0) {
		echo '<div class="row">';
		$count = 0;
		while($student = $students->fetch_assoc()) {
			$count++;
			$checked = ($assignment_type === 'all') ? 'checked' : '';
			$disabled = ($assignment_type === 'all') ? 'disabled' : '';
			
			// Handle potential null values
			$student_code = $student['student_code'] ?: 'STU' . str_pad($student['id'], 3, '0', STR_PAD_LEFT);
			$firstname = $student['firstname'] ?: 'Unknown';
			$lastname = $student['lastname'] ?: 'Student';
			$gender = $student['gender'] ?: 'N/A';
			
			echo '<div class="col-md-6 mb-2">
					<div class="custom-control custom-checkbox">
						<input type="checkbox" class="custom-control-input" 
							   id="student_'.$student['id'].'" 
							   name="assigned_students[]" 
							   value="'.$student['id'].'" 
							   '.$checked.' '.$disabled.'>
						<label class="custom-control-label" for="student_'.$student['id'].'">
							<strong>'.htmlspecialchars($student_code).'</strong> - 
							'.htmlspecialchars($lastname).', '.htmlspecialchars($firstname).' 
							<small class="text-muted">('.htmlspecialchars($gender).')</small>
						</label>
					</div>
				  </div>';
		}
		echo '</div>';
		
		if($assignment_type === 'all') {
			echo '<div class="alert alert-info mt-2">
					<i class="fas fa-info-circle"></i> 
					All '.$count.' students in this class will be automatically assigned to this assessment.
				  </div>';
		} else {
			echo '<div class="alert alert-warning mt-2">
					<i class="fas fa-exclamation-triangle"></i> 
					Please select the students you want to assign to this assessment. ('.$count.' students available)
				  </div>';
		}
	} else {
		// Show debug information when no students found
		$class_check = $conn->query("SELECT COUNT(*) as count FROM students");
		$total_students = $class_check ? $class_check->fetch_assoc()['count'] : 0;
		
		// Get class information
		$class_info = $conn->query("
		SELECT CONCAT(level, ' - ', section) as class_name 
		FROM classes 
		WHERE id = '$class_id'
	");
		$class_name = $class_info && $class_info->num_rows > 0 ? $class_info->fetch_assoc()['class_name'] : 'Unknown Class';
		
		echo '<div class="alert alert-warning">
				<i class="fas fa-exclamation-triangle"></i> 
				<strong>No students found in the selected class.</strong>
				<br><br>
				<strong>Debug Information:</strong>
				<br>• Class ID: ' . $class_id . '
				<br>• Class Name: ' . htmlspecialchars($class_name) . '
				<br>• Total students in database: ' . $total_students . '
				<br>• Query used: ' . htmlspecialchars($students_query) . '
			  </div>';
		
		// Show available classes and their student counts for debugging
		$class_debug = $conn->query("
			SELECT s.class_id, CONCAT(c.level, ' - ', c.section) as class_name, COUNT(s.id) as student_count 
			FROM students s
			LEFT JOIN classes c ON s.class_id = c.id
			GROUP BY s.class_id, c.level, c.section
			ORDER BY c.level, c.section
		");
		
		if($class_debug && $class_debug->num_rows > 0) {
			echo '<div class="alert alert-info">
					<strong>Available Classes with Students:</strong><br>';
			while($class_info = $class_debug->fetch_assoc()) {
				echo '• ' . htmlspecialchars($class_info['class_name']) . ' (ID: ' . $class_info['class_id'] . '): ' . $class_info['student_count'] . ' students<br>';
			}
			echo '</div>';
		}
	}
	exit;
}

// Update the load_score_entry function to use classes table
if($action == 'load_score_entry') {
	$assessment_id = $_POST['assessment_id'];
	
	// Get assessment details
	$assessment = $conn->query("
		SELECT q.*, s.subject, CONCAT(c.level, ' - ', c.section) as class_display,
		CASE 
			WHEN q.qtype = 1 THEN 'Exam'
			WHEN q.qtype = 2 THEN 'Quiz' 
			WHEN q.qtype = 3 THEN 'Activity'
			ELSE 'Unknown'
		END as type_name
		FROM quiz_list q 
		LEFT JOIN subject_list s ON q.subject_id = s.id 
		LEFT JOIN classes c ON q.class_id = c.id 
		WHERE q.id = '$assessment_id'
	")->fetch_assoc();
	
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
	
	echo '<div class="assessment-info mb-3">
			<div class="row">
				<div class="col-md-3"><strong>Assessment:</strong> '.htmlspecialchars($assessment['title']).'</div>
				<div class="col-md-2"><strong>Type:</strong> '.htmlspecialchars($assessment['type_name']).'</div>
				<div class="col-md-3"><strong>Subject:</strong> '.htmlspecialchars($assessment['subject']).'</div>
				<div class="col-md-2"><strong>Class:</strong> '.htmlspecialchars($assessment['class_display']).'</div>
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
		
		$student_code = $student['student_code'] ?: 'STU' . str_pad($student['id'], 3, '0', STR_PAD_LEFT);
		
		echo '<tr>
				<td>'.htmlspecialchars($student_code).'</td>
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
					url: "ajax.php?action=save_scores",
					method: "POST",
					data: $(this).serialize(),
					success: function(resp) {
						if(resp == 1) {
							alert("Scores saved successfully!");
							$("#score_modal").modal("hide");
						} else {
							alert("Error saving scores: " + resp);
						}
					}
				});
			});
		  </script>';
	exit;
}

// Add new function to save assessment with students
if($action == 'save_assessment_with_students'){
	$title = mysqli_real_escape_string($conn, $_POST['title']);
	$qtype = mysqli_real_escape_string($conn, $_POST['qtype']);
	$subject_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
	$class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
	$total_score = mysqli_real_escape_string($conn, $_POST['total_score']);
	$assessment_date = mysqli_real_escape_string($conn, $_POST['assessment_date']);
	$description = mysqli_real_escape_string($conn, $_POST['description']);
	$assignment_type = $_POST['assignment_type'];
	
	// Start transaction
	$conn->autocommit(FALSE);
	
	try {
		// Insert assessment
		$save = $conn->query("
			INSERT INTO quiz_list (title, qtype, subject_id, class_id, total_score, assessment_date, description, date_created) 
			VALUES ('$title', '$qtype', '$subject_id', '$class_id', '$total_score', '$assessment_date', '$description', NOW())
		");
		
		if(!$save) {
			throw new Exception("Failed to save assessment: " . $conn->error);
		}
		
		$assessment_id = $conn->insert_id;
		
		// Assign students
		if($assignment_type === 'all') {
			// Assign all students in the class
			$students = $conn->query("SELECT id FROM students WHERE class_id = '$class_id'");
			while($student = $students->fetch_assoc()) {
				$conn->query("
					INSERT INTO quiz_student_list (quiz_id, student_id, date_updated) 
					VALUES ('$assessment_id', '".$student['id']."', NOW())
				");
			}
		} else if($assignment_type === 'specific' && isset($_POST['selected_students'])) {
			// Assign specific students
			$selected_students = explode(',', $_POST['selected_students']);
			foreach($selected_students as $student_id) {
				$student_id = mysqli_real_escape_string($conn, trim($student_id));
				if($student_id) {
					$conn->query("
						INSERT INTO quiz_student_list (quiz_id, student_id, date_updated) 
						VALUES ('$assessment_id', '$student_id', NOW())
					");
				}
			}
		}
		
		// Commit transaction
		$conn->commit();
		echo 1;
		
	} catch (Exception $e) {
		// Rollback transaction
		$conn->rollback();
		echo "Error: " . $e->getMessage();
	}
	
	$conn->autocommit(TRUE);
	exit;
}

// Add function to load student assignment interface for existing assessments
if($action == 'load_student_assignment'){
	$assessment_id = $_POST['assessment_id'];
	
	// Get assessment details
	$assessment = $conn->query("
		SELECT q.*, s.subject, CONCAT(c.level, ' - ', c.section) as class_display,
		CASE 
			WHEN q.qtype = 1 THEN 'Exam'
			WHEN q.qtype = 2 THEN 'Quiz' 
			WHEN q.qtype = 3 THEN 'Activity'
			ELSE 'Unknown'
		END as type_name
		FROM quiz_list q 
		LEFT JOIN subject_list s ON q.subject_id = s.id 
		LEFT JOIN classes c ON q.class_id = c.id 
		WHERE q.id = '$assessment_id'
	")->fetch_assoc();
	
	// Get all students in the class and their assignment status
	$students = $conn->query("
		SELECT s.*, 
		CASE WHEN qsl.id IS NOT NULL THEN 1 ELSE 0 END as is_assigned,
		qsl.id as assignment_id
		FROM students s
		LEFT JOIN quiz_student_list qsl ON s.id = qsl.student_id AND qsl.quiz_id = '$assessment_id'
		WHERE s.class_id = '".$assessment['class_id']."'
		ORDER BY s.lastname, s.firstname
	");
	
	echo '<div class="assessment-info mb-3">
			<div class="row">
				<div class="col-md-4"><strong>Assessment:</strong> '.htmlspecialchars($assessment['title']).'</div>
				<div class="col-md-2"><strong>Type:</strong> '.htmlspecialchars($assessment['type_name']).'</div>
				<div class="col-md-3"><strong>Subject:</strong> '.htmlspecialchars($assessment['subject']).'</div>
				<div class="col-md-3"><strong>Class:</strong> '.htmlspecialchars($assessment['class_display']).'</div>
			</div>
		  </div>';
	
	echo '<form id="assign-students-form">
			<input type="hidden" name="assessment_id" value="'.$assessment_id.'">
			<div class="d-flex justify-content-between mb-3">
				<h5>Assign Students to Assessment</h5>
				<div>
					<button type="button" class="btn btn-sm btn-success" id="select-all-assign">Select All</button>
					<button type="button" class="btn btn-sm btn-warning" id="deselect-all-assign">Deselect All</button>
				</div>
			</div>
			<div class="row">';
	
	$assigned_count = 0;
	$total_count = 0;
	
	while($student = $students->fetch_assoc()) {
		$total_count++;
		$checked = $student['is_assigned'] ? 'checked' : '';
		if($student['is_assigned']) $assigned_count++;
		
		$student_code = $student['student_code'] ?: 'STU' . str_pad($student['id'], 3, '0', STR_PAD_LEFT);
		
		echo '<div class="col-md-6 mb-2">
				<div class="custom-control custom-checkbox">
					<input type="checkbox" class="custom-control-input" 
						   id="assign_student_'.$student['id'].'" 
						   name="assigned_students[]" 
						   value="'.$student['id'].'" 
						   '.$checked.'>
					<label class="custom-control-label" for="assign_student_'.$student['id'].'">
						<strong>'.htmlspecialchars($student_code).'</strong> - 
						'.htmlspecialchars($student['lastname']).', '.htmlspecialchars($student['firstname']).'
						'.($student['is_assigned'] ? '<span class="badge badge-success ml-2">Assigned</span>' : '').'
					</label>
				</div>
			  </div>';
	}
	
	echo '</div>
		  <div class="alert alert-info mt-3">
			<i class="fas fa-info-circle"></i> 
			Currently '.$assigned_count.' out of '.$total_count.' students are assigned to this assessment</div>
		  <div class="text-right mt-3">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
			<button type="submit" class="btn btn-primary">
				<i class="fas fa-save"></i> Update Student Assignments
			</button>
		  </div>
		  </form>';
	
	echo '<script>
			// Select/Deselect all students for assignment
			$("#select-all-assign").click(function() {
				$("#assign-students-form input[type=\"checkbox\"]").prop("checked", true);
			});
			
			$("#deselect-all-assign").click(function() {
				$("#assign-students-form input[type=\"checkbox\"]").prop("checked", false);
			});
			
			// Handle assignment form submission
			$("#assign-students-form").submit(function(e) {
				e.preventDefault();
				
				var selected_students = [];
				$(this).find("input[type=\"checkbox\"]:checked").each(function() {
					selected_students.push($(this).val());
				});
				
				$.ajax({
					url: "ajax.php?action=update_student_assignments",
					method: "POST",
					data: {
						assessment_id: $("input[name=\"assessment_id\"]").val(),
						selected_students: selected_students.join(",")
					},
					success: function(resp) {
						if(resp == 1) {
							alert("Student assignments updated successfully!");
							$("#assign_students_modal").modal("hide");
							load_assessments(); // Refresh the assessments list
						} else {
							alert("Error updating assignments: " + resp);
						}
					},
					error: function(xhr, status, error) {
						alert("Error updating assignments: " + error);
					}
				});
			});
		  </script>';
	exit;
}

// Add function to update student assignments for existing assessments
if($action == 'update_student_assignments'){
	$assessment_id = mysqli_real_escape_string($conn, $_POST['assessment_id']);
	$selected_students = isset($_POST['selected_students']) ? $_POST['selected_students'] : '';
	
	// Start transaction
	$conn->autocommit(FALSE);
	
	try {
		// First, remove all existing assignments for this assessment
		$conn->query("DELETE FROM quiz_student_list WHERE quiz_id = '$assessment_id'");
		
		// Then add new assignments
		if($selected_students) {
			$student_ids = explode(',', $selected_students);
			foreach($student_ids as $student_id) {
				$student_id = mysqli_real_escape_string($conn, trim($student_id));
				if($student_id) {
					$conn->query("
						INSERT INTO quiz_student_list (quiz_id, student_id, date_updated) 
						VALUES ('$assessment_id', '$student_id', NOW())
					");
				}
			}
		}
		
		// Commit transaction
		$conn->commit();
		echo 1;
		
	} catch (Exception $e) {
		// Rollback transaction
		$conn->rollback();
		echo "Error: " . $e->getMessage();
	}
	
	$conn->autocommit(TRUE);
	exit;
}

// Update the delete_assessment function to also clean up assignments
if($action == 'delete_assessment') {
	$id = mysqli_real_escape_string($conn, $_POST['id']);
	
	// Start transaction
	$conn->autocommit(FALSE);
	
	try {
		// Delete related scores and assignments
		$conn->query("DELETE FROM quiz_student_list WHERE quiz_id = '$id'");
		
		// Delete the assessment
		$delete = $conn->query("DELETE FROM quiz_list WHERE id = '$id'");
		
		if(!$delete) {
			throw new Exception("Failed to delete assessment: " . $conn->error);
		}
		
		// Commit transaction
		$conn->commit();
		echo 1;
		
	} catch (Exception $e) {
		// Rollback transaction
		$conn->rollback();
		echo "Error: " . $e->getMessage();
	}
	
	$conn->autocommit(TRUE);
	exit;
}

if($action == 'save_scores') {
    error_log("Save scores called with data: " . print_r($_POST, true));
    
    try {
        // Check if we have the required data
        if(!isset($_POST['assessment_id']) || !isset($_POST['scores'])) {
            echo "Error: Missing required data (assessment_id or scores)";
            exit;
        }
        
        $assessment_id = mysqli_real_escape_string($conn, $_POST['assessment_id']);
        $total_score = mysqli_real_escape_string($conn, $_POST['total_score']);
        $scores = $_POST['scores'];
        $score_ids = isset($_POST['score_ids']) ? $_POST['score_ids'] : array();
        
        // Start transaction for bulk operations
        $conn->autocommit(FALSE);
        
        $success_count = 0;
        $error_count = 0;
        $errors = array();
        
        foreach($scores as $student_id => $score) {
            $student_id = mysqli_real_escape_string($conn, $student_id);
            $score = mysqli_real_escape_string($conn, $score);
            
            // Skip empty scores
            if($score === '' || $score === null) {
                continue;
            }
            
            // Validate score is numeric and within range
            if(!is_numeric($score) || $score < 0 || $score > $total_score) {
                $errors[] = "Invalid score for student ID $student_id: $score";
                $error_count++;
                continue;
            }
            
            $score_id = isset($score_ids[$student_id]) ? $score_ids[$student_id] : 0;
            
            if($score_id > 0) {
                // Update existing record
                $update = $conn->query("
                    UPDATE quiz_student_list 
                    SET score = '$score', total_score = '$total_score', date_updated = NOW() 
                    WHERE id = '$score_id' AND student_id = '$student_id' AND quiz_id = '$assessment_id'
                ");
                
                if($update) {
                    $success_count++;
                } else {
                    $errors[] = "Failed to update score for student ID $student_id: " . $conn->error;
                    $error_count++;
                }
            } else {
                // Insert new record
                $insert = $conn->query("
                    INSERT INTO quiz_student_list (student_id, quiz_id, score, total_score, date_updated) 
                    VALUES ('$student_id', '$assessment_id', '$score', '$total_score', NOW())
                ");
                
                if($insert) {
                    $success_count++;
                } else {
                    $errors[] = "Failed to insert score for student ID $student_id: " . $conn->error;
                    $error_count++;
                }
            }
        }
        
        if($error_count > 0) {
            // Rollback if there were errors
            $conn->rollback();
            echo "Error: " . implode("; ", $errors);
        } else {
            // Commit if all successful
            $conn->commit();
            echo 1; // Success response expected by frontend
        }
        
        $conn->autocommit(TRUE);
        
    } catch(Exception $e) {
        $conn->rollback();
        $conn->autocommit(TRUE);
        error_log("Exception in save_scores: " . $e->getMessage());
        echo "Error: " . $e->getMessage();
    }
    exit;
}