<?php include('db_connect.php') ?>
<?php if($_SESSION['login_type'] == 1): ?>
        <div class="row">
        <div class="col-12 col-sm-6 col-md-4">
           <a href="?page=classes">
            <div class="info-box">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-th-list"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="color: black;">Total Classes</span>
                <span class="info-box-number" style="color: black;">
                  <?php echo $conn->query("SELECT * FROM classes")->num_rows; ?>
                </span>
              </div>
            </div>
          </div>
          </a>
          <div class="col-12 col-sm-6 col-md-4">
          <a href="?page=subjects">
            <div class="info-box">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-book"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="color: black;">Total Subjects</span>
                <span class="info-box-number" style="color: black;">
                  <?php echo $conn->query("SELECT * FROM subjects")->num_rows; ?>
                </span>
              </div>
            </div>
          </div>
          </a>
          <div class="col-12 col-sm-6 col-md-4">
          <a href="?page=student_list">
            <div class="info-box">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-users"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text" style="color: black;">Total Students</span>
                    <span class="info-box-number" style="color: black;">
                    <?php echo $conn->query("SELECT * FROM students")->num_rows; ?>
                    </span>
                  </div>
                </div>
              </div>
          </a>
          <div class="col-12 col-sm-6 col-md-4">
          <a href="?page=user_list">
            <div class="info-box">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="color: black;">Total Users</span>
                <span class="info-box-number" style="color: black;">
                  <?php echo $conn->query("SELECT * FROM users")->num_rows; ?>
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>

<?php else: ?>
	 <div class="col-12">
          <div class="card">
          	<div class="card-body">
          		Welcome <?php echo $_SESSION['login_name'] ?>!
          	</div>
          </div>
      </div>
          
<?php endif; ?>

<style>
  .info-box {
    transition: transform 0.3s;
}

.info-box:hover {
    transform: scale(1.1);
}
</style>