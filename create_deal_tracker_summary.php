<?php

session_start();

//Restrict access to admin only
include "partials/admin_only.php";

$_SESSION["x"]=1;
unset($_SESSION['adviser_id']);
if(!isset($_SESSION["myusername"])){
session_destroy();
header("Refresh:0; url=index.php");
}

else{
?>
<html>
<head>
	<!--nav bar-->
<?php include "partials/nav_bar.html";?>

<!--nav bar end-->
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">


<link rel="stylesheet" href="styles.css">
<link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
<title>INDET</title>
<style>

.bootstrap-select .dropdown-toggle .filter-option {
    position: relative;

}
.bootstrap-select li a {
    color: #333 !important;
}
div#myModal {
    padding-top: 0px !important;
}

.modal-dialog {
  min-width: 100%;
  min-height: 100%;
   height: auto;
   width: auto;
  margin: 0;
  padding: 0;
}

.modal-content {

	width: 100% !important;
  height: auto;
  min-height: 100%;
  border-radius: 0;
}


</style>

<script>

$(document).ready(function(){

    
	var sp=$('.adviser_selectpicker').selectpicker();
    var ip=$('.information_selectpicker').selectpicker();
	  
    sp.on("changed.bs.select", function(e, clickedIndex, newValue, oldValue) {
        var selectedD = $(this).find('option').eq(clickedIndex).val();
        // console.log('selectedD: ' + selectedD + '  newValue: ' + newValue + ' oldValue: ' + oldValue);
        var arr = sp.val();

        if(arr.length==0){
            $('.adviser_selectpicker').parent().find('.filter-option-inner-inner').html('No adviser selected');
        }else{
            $('.adviser_selectpicker').parent().find('.filter-option-inner-inner').html('Selected '+arr.length + ' advisers');
        }
        console.log(arr);
    });

    
    ip.on("changed.bs.select", function(e, clickedIndex, newValue, oldValue) {
		    var selectedD = $(this).find('option').eq(clickedIndex).val();
		   	// console.log('selectedD: ' + selectedD + '  newValue: ' + newValue + ' oldValue: ' + oldValue);
			var arr = ip.val();

			if(arr.length==0){
				$('.information_selectpicker').parent().find('.filter-option-inner-inner').html('No information selected');
			}else{
				$('.information_selectpicker').parent().find('.filter-option-inner-inner').html('Selected '+arr.length + ' information');
			}
			console.log(arr);
        });
        
        
$(".datepicker").datepicker(
    {dateFormat: 'dd/mm/yy'
});
/*
$("#datefrom").datepicker(
    {dateFormat: 'dd/mm/yy',
    beforeShowDay: function (date) {
	    if (date.getDate() == 16 || date.getDate() == 1) {
	        return [true, '',"Available"];
	    }
	    else{
	    	return [false, '',"Unavailable"];
	    }
   }
});


$("#datefrom").on('change',function(){
	var $this=$(this).val();
	var m=parseInt($this.substr(3,2));
	var month=m-1;	
	var year=parseInt($this.substr(6,4));
	var ifday=parseInt($this.substr(0,2));
	var pay_day = 0;

	var lastday = function(y,m){
		return new Date(y, m +1, 0).getDate();
	}

	console.log(ifday);

	if(ifday==16){
		//First Half
		var ld=lastday(year,month)+'/'+n(m)+'/'+year;
		var next_month = m + 1;
		var next_year = year;
		
		if(next_month>12){
			console.log("Next Month:" + next_month);
			next_month=1;
			next_year = year+1;
		}

		var pd = "07"+'/'+n(next_month)+'/'+next_year;
		$('#dateuntil').val(ld);
		$('#pay_date').val(pd);
	}
	else{
		//Second Half
		var ld='15'+'/'+n(m)+'/'+year;
		
		
		var pd = '21'+'/'+n(m)+'/'+year;
		$('#dateuntil').val(ld);
		$('#pay_date').val(pd);
	}


}); 

function n(n){
    return n > 9 ? "" + n: "0" + n;
}
*/


$('#create').prop('disabled',false);

$('#create').on('click',function(e){
    e.preventDefault();

    var advisers = JSON.stringify($("#advisers").val());
    var information = JSON.stringify($("#information").val());
    var date_from=$("#datefrom").val();
    var until=$("#dateuntil").val();

    console.log(advisers);

    if(advisers=="[]"||advisers==null){
        alert("Please select an adviser");
        return false;
    }

    console.log(date_from + ":" + until);
	$.ajax({
		dataType:'json',
		type:'POST',
		data:{advisers:advisers,
			information:information,
			date_from:date_from,
			until:until},
		url:"deal_tracker_summary_preview.php",
		success:function(e){
			var mydata=JSON.stringify(e);
			var link=e['link'];
			var htm= '<iframe src="'+link+'" style="width: 100%;height: 75%;"></iframe>';
			$('#myModal').modal('show');
			$('.modal-body').html(htm);
			$('#save_pdf').unbind( "click");
			$('#save_pdf').on('click',function(){
				$.ajax({
					//dataType:'JSON',
					data:{mydata:mydata},
					type:'POST',
					url:"save_deal_tracker_summary.php",
				beforeSend:function(){

				},
				success:function(x){
					console.log(x);
					$.confirm({
					    title: 'Success!',
					    content: 'You have successfully created a deal report.',
					    buttons: {
					        Ok: function () {
						        	console.log(x);
						   			window.location='create_deal_tracker_summary.php';
						        },	 
					    	}
						});
				}
				});
			});
		},
		error: function (x) {
			x=JSON.stringify(x);
		        console.log("Data:" + x);
		      }
	});

});

});


</script>

</head>
<body>

<!--header-->
<div align="center">



<!--header end-->

<!--nav bar-->

<!--nav bar end-->

<!--label-->
  <div class="jumbotron">
    <h2 class="slide">Create Deal Tracker Summary</h2>
</div>
<!--label end-->

<?php require "database.php";
 $con=mysqli_connect($host,$username,$password,$db)or die("could not connect to sql");
 if (!$con) {
        echo "<div>";
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
	    echo "</div>";	
}

?>

<form method="POST" action="deal_tracker_summary_preview.php" autocomplete="off" class="margined">
	<div class='row'>

   <div class='col-sm-2'></div>
   <div class='col-sm-2'>
        <label style="width: 100%";>Adviser(s): 
            <input class="form-control" value="<?=$now_;?>" readonly='' autocomplete="off" type="hidden" name="date_now" id="date_now" />

            <select name="advisers" class="form-control adviser_selectpicker" id="advisers" data-actions-box="true" multiple="multiple">
                <optgroup label="Advisers">
                    <?php
                        $query = "SELECT * FROM adviser_tbl";
                        $displayquery=mysqli_query($con,$query) or die('Could not look up user information; ' . mysqli_error($con));

                        WHILE($rows = mysqli_fetch_array($displayquery)){
                            $id=$rows["id"];
                            $name=$rows["name"];
                            echo "<option value=$id>$name</option>";
                        }
                    ?>
                </optgroup>
            </select>
        </label>
    </div>
 	<div class='col-sm-2'>
        <label style="width: 100% !important;">Date From
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar" aria-hidden="divue"></i>
                </span>
                    <input class="form-control datepicker" autocomplete="off" type="text" id="datefrom" name="date_from" />
            </div>
        </label>
    </div>
    
    <div class='col-sm-2'>
        <label>Until
            <div class="input-group">
                <span class="input-group-addon">
                    <i class="fa fa-calendar" aria-hidden="divue"></i>
                </span>
                <input class="form-control datepicker" autocomplete="off" type="text" id="dateuntil" name="until"/>
            </div>
        </label>
    </div>

    <div class='col-sm-2'>
        <label style="width: 100%";>Information: 
            
            <select name="information" class="form-control information_selectpicker" id="information" data-actions-box="true" multiple="multiple">
                <optgroup label="Information">
                    <option>Production</option>
                    <option>Clawback</option>
                </optgroup>
            </select>
        </label>
    </div>
</div>

<div class="row">
          <div class="col-sm-2 center" >
	<input name="enter" type="submit" id='create' value="Create Report" style='margin-top: 30px;width: 100%;' class="btn btn-danger center" />
</div>
</div>
</form>


<div class="container">

  <!-- Modal -->
  <div class="modal fade" id="myModal" role="dialog" style="z-index:10000;width: 100%;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h2 class="modal-title" style="float: left;">Deals Report Preview</h2>
        </div>
        <div class="modal-body">

        </div>
        <div class="modal-footer">
        	<button type="button" class="btn btn-info" id='save_pdf'>Save</button>
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</div>



</body>


</html>

<?php

}

?>