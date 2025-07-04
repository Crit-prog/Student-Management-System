<?php include'db_connect.php' ?>
<div class="col-lg-12">
	<div class="card card-outline card-success">
		<?php	if(!isset($_SESSION['rs_id'])): ?>
		<div class="card-header">
			<div class="card-tools">
				<a class="btn btn-block btn-sm btn-default btn-flat border-success" href="./index.php?page=new_result"><i class="fa fa-plus"></i> Add New</a>
			</div>
		</div>
	<?php endif; ?>
		<div class="card-body">
			<table class="table tabe-hover table-bordered" id="list">
				<colgroup>
					<col width="5%">
					<col width="20%">
					<col width="25%">
					<col width="15%">
					<col width="10%">
					<col width="8%">
					<col width="15%">
				</colgroup>
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">Student Code</th>
						<th class="text-center">Student Name</th>
						<th class="text-center">Class</th>
						<th class="text-center">Subjects</th>
						<th class="text-center">Average</th>
						<th class="text-center">Remarks</th>
						<th class="text-center">Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					$where = "";
					if(isset($_SESSION['rs_id'])){
						$where = " where r.student_id = {$_SESSION['rs_id']} ";
					}
						function convertToCollegeGrade($percentage) {
						if ($percentage >= 96) return "1.00";
						elseif ($percentage >= 93) return "1.25";
						elseif ($percentage >= 90) return "1.50";
						elseif ($percentage >= 87) return "1.75";
						elseif ($percentage >= 84) return "2.00";
						elseif ($percentage >= 81) return "2.25";
						elseif ($percentage >= 78) return "2.50";
						elseif ($percentage >= 75) return "2.75";
						elseif ($percentage >= 72) return "3.00";
						else return "5.00"; // Fail
					}

					$qry = $conn->query("SELECT r.*,concat(s.firstname,' ',s.middlename,' ',s.lastname) as name,s.student_code,concat(c.level,'-',c.section) as class FROM results r inner join classes c on c.id = r.class_id inner join students s on s.id = r.student_id $where order by unix_timestamp(r.date_created) desc ");
					while($row= $qry->fetch_assoc()):
						$subjects = $conn->query("SELECT * FROM result_items where result_id =".$row['id'])->num_rows;
					?>
					<tr>
						<th class="text-center"><?php echo $i++ ?></th>
						<td><b><?php echo $row['student_code'] ?></b></td>
						<td><b><?php echo ucwords($row['name']) ?></b></td>
						<td><b><?php echo ucwords($row['class']) ?></b></td>
						<td class="text-center"><b><?php echo $subjects ?></b></td>
						<td class="text-center"><b><?php echo convertToCollegeGrade($row['marks_percentage']) ?></b></td>
						
						<td class="text-center">
							<?php
							if ($row['marks_percentage'] >= 1.00) {
								echo "With Highest Remarks";
							} elseif ($row['marks_percentage'] >= 1.20) {
								echo "With High Remarks";
							} elseif ($row['marks_percentage'] >= 1.50) {
								echo "Excellent";
							} elseif ($row['marks_percentage'] >= 2.00) {
								echo "Average";
							} elseif ($row['marks_percentage'] >= 2.25) {
								echo "Better";
							} elseif ($row['marks_percentage'] >= 2.50) {
								echo "Good";
							} elseif ($row['marks_percentage'] >= 2.75) {
								echo "Well done";
							} elseif ($row['marks_percentage'] < 3.0) {
								echo "Failed";
							} else {
								echo "No Honor";
							}
							?>
							</td>

						</td>
						<td class="text-center">
							<?php if(isset($_SESSION['login_id'])): ?>
		                    <div class="btn-group">
		                        <a href="./index.php?page=edit_result&id=<?php echo $row['id'] ?>" class="btn btn-primary btn-flat">
		                          <i class="fas fa-edit"></i>
		                        </a>
		                         <button data-id="<?php echo $row['id'] ?>" type="button" class="btn btn-info btn-flat view_result">
		                          <i class="fas fa-eye"></i>
		                        </button>
		                        <button type="button" class="btn btn-danger btn-flat delete_result" data-id="<?php echo $row['id'] ?>">
		                          <i class="fas fa-trash"></i>
		                        </button>
	                      </div>
	                      <?php elseif(isset($_SESSION['rs_id'])): ?>
	                      	<button data-id="<?php echo $row['id'] ?>" type="button" class="btn btn-info btn-flat view_result">
		                          <i class="fas fa-eye"></i>
		                          View Result
		                        </button>
	                      <?php endif; ?>
						</td>
					</tr>	
				<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>


	$(document).ready(function(){
		$('#list').dataTable()
	$('.delete_result').click(function(){
	_conf("Are you sure to delete this result?","delete_result",[$(this).attr('data-id')])
	})

	$('.view_result').click(function(){
		uni_modal("Result","view_result.php?id="+$(this).attr('data-id'),'mid-large')
	})
	$('.status_chk').change(function(){
		var status = $(this).prop('checked') == true ? 1 : 2;
		if($(this).attr('data-state-stats') !== undefined && $(this).attr('data-state-stats') == 'error'){
			$(this).removeAttr('data-state-stats')
			return false;
		}
		var id = $(this).attr('data-id');
		start_load()
		$.ajax({
			url:'ajax.php?action=update_result_stats',
			method:'POST',
			data:{id:id,status:status},
			error:function(err){
				console.log(err)
				alert_toast("Something went wrong while updating the result's status.",'error')
					$('#status_chk').attr('data-state-stats','error').bootstrapToggle('toggle')
					end_load()
			},
			success:function(resp){
				if(resp == 1){
					alert_toast("result status successfully updated.",'success')
					end_load()
				}else{
					alert_toast("Something went wrong while updating the result's status.",'error')
					$('#status_chk').attr('data-state-stats','error').bootstrapToggle('toggle')
					end_load()
				}
			}
		})
	})
	})
	function delete_result($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_result',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}
	
</script>