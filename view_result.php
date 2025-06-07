<?php
include 'db_connect.php';
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
    else return "5.00";
}
$qry = $conn->query("SELECT r.*, concat(s.firstname,' ',s.middlename,' ',s.lastname) as name, s.student_code, concat(c.level,'-',c.section) as class, s.gender, s.course FROM results r inner join classes c on c.id = r.class_id inner join students s on s.id = r.student_id where r.id = ".$_GET['id'])->fetch_array();foreach($qry as $k => $v){
	$$k = $v;
}
?>
<div class="container-fluid" id="printable">
	<table width="100%">
		<tr>
			<td width="50%">Student ID #: <b><?php echo $student_code ?></b></td>
			<td width="50%">Class: <b><?php echo $class ?></b></td>
		</tr>
		<tr>
			<td width="50%">Student Name: <b><?php echo ucwords($name) ?></b></td>
			<td width="50%">Gender: <b><?php echo ucwords($gender) ?></b></td>
		</tr>
		<tr>
			<td width="50%">Course: <b><?php echo ucwords($course) ?></b></td>
		</tr>
	</table>
	<hr>
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>Subject Code</th>
				<th>Subject</th>
				<th>Grade</th>
			</tr>
		</thead>
		<tbody>
			<?php 
    			$items=$conn->query("SELECT r.*,s.subject_code,s.subject FROM result_items r inner join subjects s on s.id = r.subject_id where result_id = $id  order by s.subject_code asc");
    			while($row = $items->fetch_assoc()):
    		?>
    		<tr>
    			<td><?php echo $row['subject_code'] ?></td>
    			<td><?php echo ucwords($row['subject']) ?></td>
    			<td class="text-center"><?php echo convertToCollegeGrade($row['mark']) ?></td>

    		</tr>
			<?php endwhile; ?>
		</tbody>
		<tfoot>
			<tr>
				<th colspan="2">GWA</th>
				<th class="text-center"><?php echo convertToCollegeGrade($marks_percentage); ?></th>

			</tr>
		</tfoot>
		<tfoot>
    <tr>
        <th class="text-center">
    <?php
        $g = convertToCollegeGrade($marks_percentage);
        switch($g) {
            case "1.00": echo "With Highest Honors"; break;
            case "1.25": echo "With High Honors"; break;
            case "1.50": echo "Dean's List"; break;
            case "1.75": echo "Excellent"; break;
            case "2.00": echo "Very Good"; break;
            case "2.25": echo "Good"; break;
            case "2.50": echo "Satisfactory"; break;
            case "2.75": echo "Passed"; break;
            case "3.00": echo "Barely Passed"; break;
            case "5.00": echo "Failed"; break;
            default: echo "No Grade";
        }
    ?>
</th>

    </tr>
</tfoot>
	</table>
</div>
<div class="modal-footer display p-0 m-0">
        <button type="button" class="btn btn-success" id="print"><i class="fa fa-print"></i> Print</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
<style>
	#uni_modal .modal-footer{
		display: none
	}
	#uni_modal .modal-footer.display{
		display: flex
	}
</style>
<noscript>
	<style>
		table.table{
			width:100%;
			border-collapse: collapse;
		}
		table.table tr,table.table th, table.table td{
			border:1px solid;
		}
		.text-cnter{
			text-align: center;
		}
	</style>
	<h3 class="text-center"><b>Student Result</b></h3>
</noscript>
<script>
	$('#print').click(function(){
		start_load()
		var ns = $('noscript').clone()
		var content = $('#printable').clone()
		ns.append(content)
		var nw = window.open('','','height=700,width=900')
		nw.document.write(ns.html())
		nw.document.close()
		nw.print()
		setTimeout(function(){
			nw.close()
			end_load()
		},750)

	})
</script>