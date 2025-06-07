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
                        // Try subjects table first, then subject_list
                        $subjects_query = "SELECT id, subject FROM subjects ORDER BY subject";
                        $subjects = $conn->query($subjects_query);
                        if(!$subjects || $subjects->num_rows == 0) {
                            $subjects_query = "SELECT id, subject FROM subject_list ORDER BY subject";
                            $subjects = $conn->query($subjects_query);
                        }
                        
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
                        // Try classes table first (with level and section), then class_list
                        $classes_query = "SELECT id, CONCAT(level, ' - ', section) as class_name FROM classes ORDER BY level, section";
                        $classes = $conn->query($classes_query);
                        if(!$classes || $classes->num_rows == 0) {
                            $classes_query = "SELECT id, class as class_name FROM class_list ORDER BY class";
                            $classes = $conn->query($classes_query);
                        }
                        
                        if($classes && $classes->num_rows > 0):
                          while($row = $classes->fetch_assoc()):
                        ?>
                        <option value="<?php echo $row['id'] ?>"><?php echo htmlspecialchars($row['class_name']) ?></option>
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
                  // Reset the query for modal
                  $subjects_query = "SELECT id, subject FROM subjects ORDER BY subject";
                  $subjects = $conn->query($subjects_query);
                  if(!$subjects || $subjects->num_rows == 0) {
                      $subjects_query = "SELECT id, subject FROM subject_list ORDER BY subject";
                      $subjects = $conn->query($subjects_query);
                  }
                  
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
                <select class="form-control" name="class_id" required>
                  <option value="">Select Class</option>
                  <?php 
                  // Reset the query for modal
                  $classes_query = "SELECT id, CONCAT(level, ' - ', section) as class_name FROM classes ORDER BY level, section";
                  $classes = $conn->query($classes_query);
                  if(!$classes || $classes->num_rows == 0) {
                      $classes_query = "SELECT id, class as class_name FROM class_list ORDER BY class";
                      $classes = $conn->query($classes_query);
                  }
                  
                  if($classes && $classes->num_rows > 0):
                    while($row = $classes->fetch_assoc()):
                  ?>
                  <option value="<?php echo $row['id'] ?>"><?php echo htmlspecialchars($row['class_name']) ?></option>
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
                <input type="number" class="form-control" name="total_score" required min="1" value="100">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Assessment</button>
        </div>
      </form>
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
$(document).ready(function(){
    console.log('Document ready');
    
    // Set today's date as default
    var today = new Date().toISOString().split('T')[0];
    $('input[name="assessment_date"]').val(today);
    
    // Load assessments on page load
    load_assessments();
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
        url: 'ajax_fixed.php?action=load_assessments',
        method: 'POST',
        data: {
            subject_id: subject,
            class_id: class_id,
            qtype: type
        },
        success: function(resp) {
            console.log('Assessments loaded successfully');
            console.log('Response:', resp);
            $('#assessments-list').html(resp);
        },
        error: function(xhr, status, error) {
            console.log('Error loading assessments:', error);
            console.log('XHR:', xhr);
            $('#assessments-list').html('<div class="alert alert-danger">Error loading assessments: ' + error + '<br>Status: ' + status + '<br>Response: ' + xhr.responseText + '</div>');
        }
    });
}

function add_new_assessment() {
    $('#assessment_modal').modal('show');
    // Set today's date as default
    var today = new Date().toISOString().split('T')[0];
    $('input[name="assessment_date"]').val(today);
}

function enter_scores(assessment_id) {
    console.log('Entering scores for assessment:', assessment_id);
    $('#score_modal').modal('show');
    $('#score-entry-content').html('<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading students...</div>');
    
    $.ajax({
        url: 'ajax_fixed.php?action=load_score_entry',
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

function delete_assessment(id) {
    if(confirm('Are you sure you want to delete this assessment? This will also delete all related scores.')) {
        $.ajax({
            url: 'ajax_fixed.php?action=delete_assessment',
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
    
    if(!title || !qtype || !subject_id || !class_id || !total_score || !assessment_date) {
        alert('Please fill in all required fields');
        return;
    }
    
    if(total_score < 1) {
        alert('Total score must be at least 1');
        return;
    }
    
    $.ajax({
        url: 'ajax_fixed.php?action=save_assessment',
        method: 'POST',
        data: $(this).serialize(),
        success: function(resp) {
            console.log('Assessment save response:', resp);
            if(resp == 1) {
                alert('Assessment saved successfully');
                $('#assessment_modal').modal('hide');
                $('#assessment-form')[0].reset();
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
});
</script>

<?php include 'footer.php' ?>
</body>
</html>