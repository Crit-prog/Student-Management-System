<!DOCTYPE html>
<html lang="en">
<?php 
session_start();
include 'db_connect.php';

// Check if admin is logged in
if(!isset($_SESSION['login_id']))
    header('location:login.php');

include 'header.php';
?>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
<div class="wrapper">
  <?php include 'topbar.php' ?>
  <?php include 'sidebar.php' ?>

  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Manage Student Scores</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="./">Home</a></li>
              <li class="breadcrumb-item active">Manage Scores</li>
            </ol>
          </div>
        </div>
      </div>
    </div>

    <section class="content">
      <div class="container-fluid">

        <!-- Filter Section -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Filter Options</h3>
              </div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Subject</label>
                      <select class="form-control" id="filter_subject">
                        <option value="">All Subjects</option>
                        <?php 
                        $subjects = $conn->query("SELECT * FROM subjects ORDER BY subject");
                        if($subjects && $subjects->num_rows > 0):
                          while($row = $subjects->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>"><?php echo htmlspecialchars($row['subject']) ?></option>
                        <?php 
                          endwhile;
                        else:
                        ?>
                        <option disabled>No subjects found</option>
                        <?php endif; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Class</label>
                      <select class="form-control" id="filter_class">
                        <option value="">All Classes</option>
                        <?php 
                        // Use classes table instead of class_list
                        $classes = $conn->query("SELECT id, CONCAT(level, ' - ', section) as class_display, level, section FROM classes ORDER BY level, section");
                        if($classes && $classes->num_rows > 0):
                          while($row = $classes->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>"><?php echo htmlspecialchars($row['class_display']) ?></option>
                        <?php 
                          endwhile;
                        else:
                        ?>
                        <option disabled>No classes found</option>
                        <?php endif; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Assessment Type</label>
                      <select class="form-control" id="filter_type">
                        <option value="">All Types</option>
                        <option value="1">Exam</option>
                        <option value="2">Quiz</option>
                        <option value="3">Activity</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>&nbsp;</label>
                      <button type="button" class="btn btn-primary btn-block" id="filter-btn">
                        <i class="fas fa-search"></i> Filter
                      </button>
                    </div>
                  </div>

                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Assessments List -->
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Assessments</h3>
                <div class="card-tools">
                  <button class="btn btn-success btn-sm" id="add-assessment-btn">
                    <i class="fas fa-plus"></i> Add New Assessment
                  </button>
                </div>
              </div>
              <div class="card-body">
                <div id="assessments-list">
                  <div class="text-center">
                    <p>Click the Filter button to load assessments</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</div>

<!-- Add Assessment Modal -->
<div class="modal fade" id="assessment_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add New Assessment</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <form id="assessment-form">
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Assessment Title</label>
                <input type="text" class="form-control" name="title" required>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Type</label>
                <select class="form-control" name="qtype" required>
                  <option value="">Select Type</option>
                  <option value="1">Exam</option>
                  <option value="2">Quiz</option>
                  <option value="3">Activity</option>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Subject</label>
                <select class="form-control" name="subject_id" required>
                  <option value="">Select Subject</option>
                  <?php 
                  $subjects = $conn->query("SELECT * FROM subjects ORDER BY subject");
                  if($subjects && $subjects->num_rows > 0):
                    while($row = $subjects->fetch_assoc()):
                  ?>
                  <option value="<?php echo $row['id'] ?>"><?php echo htmlspecialchars($row['subject']) ?></option>
                  <?php 
                    endwhile;
                  endif;
                  ?>
                </select>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Class</label>
                <select class="form-control" name="class_id" id="assessment_class" required>
                  <option value="">Select Class</option>
                  <?php 
                  // Use classes table instead of class_list
                  $classes = $conn->query("SELECT id, CONCAT(level, ' - ', section) as class_display, level, section FROM classes ORDER BY level, section");
                  if($classes && $classes->num_rows > 0):
                    while($row = $classes->fetch_assoc()):
                  ?>
                  <option value="<?php echo $row['id'] ?>"><?php echo htmlspecialchars($row['class_display']) ?></option>
                  <?php 
                    endwhile;
                  endif;
                  ?>
                </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Total Score</label>
                <input type="number" class="form-control" name="total_score" required min="1">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Date</label>
                <input type="date" class="form-control" name="assessment_date" required>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="3"></textarea>
          </div>
          
          <!-- Student Assignment Section -->
          <div class="form-group">
            <label>Select Section</label>
            <select class="form-control" name="assignment_type" id="assignment_type" required>
              <option value="">Select Section</option>
              <option value="all">All students in class</option>
              <option value="specific">Specific students</option>
            </select>
          </div>
          
          <div class="form-group">
            <button type="button" class="btn btn-info" id="load-students-btn" disabled>
              <i class="fas fa-users"></i> Load Students
            </button>
          </div>
          
          <div id="students-assignment-section" style="display: none;">
            <div class="form-group">
              <label>Select Students:</label>
              <div class="d-flex justify-content-between mb-2">
                <small class="text-muted">Choose which students should take this assessment</small>
                <div>
                  <button type="button" class="btn btn-sm btn-success" id="select-all-students">Select All</button>
                  <button type="button" class="btn btn-sm btn-warning" id="deselect-all-students">Deselect All</button>
                </div>
              </div>
              <div class="border p-3" style="max-height: 300px; overflow-y: auto;" id="students-list">
                <!-- Students will be loaded here -->
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Assessment</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Assign Students Modal (for existing assessments) -->
<div class="modal fade" id="assign_students_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Assign Students to Assessment</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="assign-students-content">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Score Entry Modal -->
<div class="modal fade" id="score_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Enter Student Scores</h4>
        <button type="button" class="close" data-dismiss="modal">
          <span>&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="score-entry-content">
          <!-- Content will be loaded here -->
        </div>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
    console.log('Document ready');
    
    // Set today's date as default
    var today = new Date().toISOString().split('T')[0];
    $('input[name="assessment_date"]').val(today);
    
    // Load assessments on page load
    load_assessments();
    
    // Enable/disable load students button based on class selection
    $('#assessment_class').change(function() {
        console.log('Class changed to:', $(this).val());
        if($(this).val()) {
            $('#load-students-btn').prop('disabled', false);
            // Auto-load students if assignment type is already selected
            if($('#assignment_type').val()) {
                load_students_for_assignment($(this).val(), $('#assignment_type').val());
            }
        } else {
            $('#load-students-btn').prop('disabled', true);
            $('#students-assignment-section').hide();
        }
    });
    
    // Load students when button is clicked
    $('#load-students-btn').click(function() {
        var class_id = $('#assessment_class').val();
        var assignment_type = $('#assignment_type').val();
        
        console.log('Load students clicked - Class:', class_id, 'Type:', assignment_type);
        
        if(!class_id) {
            alert('Please select a class first');
            return;
        }
        
        if(!assignment_type) {
            alert('Please select assignment type first');
            return;
        }
        
        load_students_for_assignment(class_id, assignment_type);
    });
    
    // Assignment type change handler
    $('#assignment_type').change(function() {
        console.log('Assignment type changed to:', $(this).val());
        var class_id = $('#assessment_class').val();
        if(class_id && $(this).val()) {
            load_students_for_assignment(class_id, $(this).val());
        }
    });
    
    // Select/Deselect all students
    $(document).on('click', '#select-all-students', function() {
        $('#students-list input[type="checkbox"]').prop('checked', true);
    });
    
    $(document).on('click', '#deselect-all-students', function() {
        $('#students-list input[type="checkbox"]').prop('checked', false);
    });
});

// Filter button click event
$('#filter-btn').click(function() {
    console.log('Filter button clicked');
    load_assessments();
});

// Add assessment button click event
$('#add-assessment-btn').click(function() {
    console.log('Add assessment button clicked');
    add_new_assessment();
});

function load_assessments() {
    var subject = $('#filter_subject').val();
    var class_id = $('#filter_class').val();
    var type = $('#filter_type').val();
    
    console.log('Loading assessments with filters:', {
        subject_id: subject,
        class_id: class_id,
        qtype: type
    });
    
    // Show loading message
    $('#assessments-list').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading assessments...</div>');
    
    $.ajax({
        url: 'ajax.php?action=load_assessments',
        method: 'POST',
        data: {
            subject_id: subject,
            class_id: class_id,
            qtype: type
        },
        success: function(resp) {
            console.log('Assessments loaded successfully');
            $('#assessments-list').html(resp);
        },
        error: function(xhr, status, error) {
            console.log('Error loading assessments:', error);
            $('#assessments-list').html('<div class="alert alert-danger">Error loading assessments: ' + error + '<br>Status: ' + status + '<br>Response: ' + xhr.responseText + '</div>');
        }
    });
}

function add_new_assessment() {
    $('#assessment_modal').modal('show');
    // Set today's date as default
    var today = new Date().toISOString().split('T')[0];
    $('input[name="assessment_date"]').val(today);
    
    // Reset form
    $('#assessment-form')[0].reset();
    $('#students-assignment-section').hide();
    $('#load-students-btn').prop('disabled', true);
    $('input[name="assessment_date"]').val(today); // Set date again after reset
}

function load_students_for_assignment(class_id, assignment_type) {
    console.log('Loading students for class:', class_id, 'Type:', assignment_type);
    
    $('#students-list').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading students...</div>');
    $('#students-assignment-section').show();
    
    $.ajax({
        url: 'ajax.php?action=load_students_for_assignment',
        method: 'POST',
        data: {
            class_id: class_id,
            assignment_type: assignment_type
        },
        success: function(resp) {
            console.log('Students loaded successfully');
            console.log('Response:', resp);
            $('#students-list').html(resp);
            
            // Auto-select all if assignment type is "all"
            if(assignment_type === 'all') {
                $('#students-list input[type="checkbox"]').prop('checked', true);
                $('#students-list input[type="checkbox"]').prop('disabled', true);
            } else {
                $('#students-list input[type="checkbox"]').prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error loading students:', error);
            console.log('XHR Response:', xhr.responseText);
            $('#students-list').html('<div class="alert alert-danger">Error loading students: ' + error + '<br>Response: ' + xhr.responseText + '</div>');
        }
    });
}

function enter_scores(assessment_id) {
    console.log('Entering scores for assessment:', assessment_id);
    $('#score_modal').modal('show');
    $('#score-entry-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading students...</div>');
    
    $.ajax({
        url: 'ajax.php?action=load_score_entry',
        method: 'POST',
        data: { assessment_id: assessment_id },
        success: function(resp) {
            $('#score-entry-content').html(resp);
        },
        error: function(xhr, status, error) {
            console.log('Error loading score entry:', error);
            $('#score-entry-content').html('<div class="alert alert-danger">Error loading score entry: ' + error + '</div>');
        }
    });
}

function assign_students(assessment_id) {
    console.log('Assigning students to assessment:', assessment_id);
    $('#assign_students_modal').modal('show');
    $('#assign-students-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading assignment interface...</div>');
    
    $.ajax({
        url: 'ajax.php?action=load_student_assignment',
        method: 'POST',
        data: { assessment_id: assessment_id },
        success: function(resp) {
            $('#assign-students-content').html(resp);
        },
        error: function(xhr, status, error) {
            console.log('Error loading student assignment:', error);
            $('#assign-students-content').html('<div class="alert alert-danger">Error loading student assignment: ' + error + '</div>');
        }
    });
}

function delete_assessment(id) {
    if(confirm('Are you sure you want to delete this assessment? This will also delete all related scores and assignments.')) {
        $.ajax({
            url: 'ajax.php?action=delete_assessment',
            method: 'POST',
            data: { id: id },
            success: function(resp) {
                if(resp == 1) {
                    alert('Assessment deleted successfully');
                    load_assessments();
                } else {
                    alert('Error deleting assessment: ' + resp);
                }
            },
            error: function(xhr, status, error) {
                console.log('Error deleting assessment:', error);
                alert('Error deleting assessment: ' + error);
            }
        });
    }
}

// Handle assessment form submission
$(document).on('submit', '#assessment-form', function(e) {
    e.preventDefault();
    
    console.log('Assessment form submitted');
    
    // Basic validation
    var title = $('input[name="title"]').val().trim();
    var qtype = $('select[name="qtype"]').val();
    var subject_id = $('select[name="subject_id"]').val();
    var class_id = $('select[name="class_id"]').val();
    var total_score = $('input[name="total_score"]').val();
    var assessment_date = $('input[name="assessment_date"]').val();
    var assignment_type = $('#assignment_type').val();
    
    if(!title || !qtype || !subject_id || !class_id || !total_score || !assessment_date) {
        alert('Please fill in all required fields');
        return;
    }
    
    if(total_score < 1) {
        alert('Total score must be at least 1');
        return;
    }
    
    // Get selected students
    var selected_students = [];
    if(assignment_type === 'specific') {
        $('#students-list input[type="checkbox"]:checked').each(function() {
            selected_students.push($(this).val());
        });
        
        if(selected_students.length === 0) {
            alert('Please select at least one student or change assignment type to "All students"');
            return;
        }
    }
    
    // Prepare form data
    var formData = $(this).serialize();
    if(selected_students.length > 0) {
        formData += '&selected_students=' + selected_students.join(',');
    }
    
    console.log('Submitting form data:', formData);
    
    $.ajax({
        url: 'ajax.php?action=save_assessment_with_students',
        method: 'POST',
        data: formData,
        success: function(resp) {
            console.log('Assessment save response:', resp);
            if(resp == 1) {
                alert('Assessment saved and students assigned successfully!');
                $('#assessment_modal').modal('hide');
                $('#assessment-form')[0].reset();
                $('#students-assignment-section').hide();
                load_assessments();
            } else {
                alert('Error saving assessment: ' + resp);
            }
        },
        error: function(xhr, status, error) {
            console.log('Error saving assessment:', error);
            alert('Error saving assessment: ' + error);
        }
    });
});

// Reset form when modal is closed
$('#assessment_modal').on('hidden.bs.modal', function () {
    $('#assessment-form')[0].reset();
    $('#students-assignment-section').hide();
    $('#load-students-btn').prop('disabled', true);
    // Set today's date again
    var today = new Date().toISOString().split('T')[0];
    $('input[name="assessment_date"]').val(today);
});
</script>

<?php include 'footer.php' ?>
</body>
</html>