<?php

class config{
	public $homename="My kennel";
}

class set_file{
	private $gpio;
	private $lines;
	
	function set_file($gpio){
		$this->gpio = './gpio/' . intval($gpio);
		if(is_file($this->gpio)) $this->lines = array_pad(file($this->gpio),4,"\n");
	}
	
	function status($s){
		$this->lines[0] = "$s\n";
		file_put_contents($this->gpio,implode("",$this->lines));
	}
	
	function head($s){
		$this->lines[1] = htmlentities("$s\n", ENT_QUOTES, "UTF-8");;
		file_put_contents($this->gpio,implode("",$this->lines));
	}
	
	function desc($s){
		$this->lines[2] = "$s\n";
		file_put_contents($this->gpio,implode("",$this->lines));
	}

}
$config = new config();

$gpio_dir  = './gpio/';

if(isset($_POST["value"]) && intval($_POST["value"])){
	$gpio_file = new set_file(intval($_POST["value"]));
	if($_POST["action"] == 'on') $gpio_file->status(1);
	if($_POST["action"] == 'off') $gpio_file->status(0);
	if($_POST["action"] == 'head') $gpio_file->head($_POST["value2"]);
	header('Location: ' . $_SERVER["REQUEST_URI"]);
}

if (file_exists($gpio_dir) && $handle = opendir($gpio_dir)) {
    while (false !== ($entry = readdir($handle))) {
        if(is_file($gpio_dir . $entry) && filesize($gpio_dir . $entry)){
		$gpio[$entry] = file($gpio_dir . $entry);
error_log(print_r($gpio_dir . $entry,true));
		$gpio[$entry][3] = json_decode(trim($gpio[$entry][3]), true);
	}
    }
}else{
	die("ERROR " . __LINE__);
}
/*
status
name
desc
conf
*/

?><!DOCTYPE html>
<html lang="en">
<head>
  <title>S-Home panel</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.2/jquery.min.js"></script>
  <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  <style>
    /* Remove the navbar's default margin-bottom and rounded borders */ 
    .navbar {
/*      margin-bottom: 0;
      border-radius: 0; */
    }
    
    /* Add a gray background color and some padding to the footer */
    footer {
	  background-color: #f2f2f2;
      padding: 25px;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>                        
      </button>
      <a class="navbar-brand" href="#">S-Home | <?php echo $config->homename; ?></a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav">
        <li class="active"><a href="#">Home</a></li>
        <!--li><a href="#">About</a></li>
        <li><a href="#">Gallery</a></li>
        <li><a href="#">Contact</a></li-->
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#" onclick="submit('logout')"><span class="glyphicon glyphicon-log-out"></span> LogOut</a></li>
      </ul>
    </div>
  </div>
</nav>

<!--div class="jumbotron">
  <div class="container text-center">
    <h1>My Portfolio</h1>      
    <p>Some text that represents "Me"...</p>
  </div>
</div-->
<div class="container bs-docs-container">
<div class="bs-docs-section">
<?php
foreach($gpio as $gid => $g):
    if(!$g)        continue;
?>
    <div class="col-xs-12 col-sm-4">
      <div class="panel panel-primary" style="background-size: 500px;background-image:url(<?php echo isset($g[3]['img']) && file_exists($g[3]['img']) && is_file($g[3]['img'])?$g[3]['img']:'image/bg.jpg'; ?>)">
        <div class="panel-heading text-center"><?php echo $g[1];?>
        <span role="button" class="pull-right glyphicon glyphicon-pencil" title="Edit" onclick="var person = prompt('Enter name','<?php echo trim($g[1]);?>');
        if(person != null){
        submit('head',<?php echo $gid;?>,person);
        }"></span>
        </div>
        <div class="panel-body">
		<div class="btn-group-vertical col-xs-3 col-sm-3 col-md-3" role="group">
		    <div type="button" class="btn btn-success" onclick="submit('<?php echo $g[3]['mod'] == 'no'?'off':'on'; ?>',<?php echo $gid;?>)">on</div>
		    <div type="button" class="btn btn-danger" onclick="submit('<?php echo $g[3]['mod'] == 'no'?'on':'off'; ?>',<?php echo $gid;?>)">off</div>
		</div>
	    <div class="col-xs-9 col-sm-9 col-md-9">
			<div class="panel panel-<?php echo $g[0]==1?($g[3]['mod'] == 'no'?'danger':'success'):($g[3]['mod'] == 'no'?'success':'danger'); ?>">
				<div class="panel-heading">Status<span data-toggle="modal" data-target="#myModal<?php echo $gid;?>" role="button" class="pull-right glyphicon glyphicon-time" title="Scheduled Tasks" onclick="submit"></span></div>
				<div class="panel-body"><?php echo empty($g[2])?'&sfr;':$g[2]; ?></div>
			</div>
	    </div>
	</div>
      </div>
    </div>
	<!-- Modal -->
	<div class="modal fade" id="myModal<?php echo $gid;?>" role="dialog">
	<div class="modal-dialog">

	  <!-- Modal content-->
	  <div class="modal-content">
		<div class="modal-header">
		  <button type="button" class="close" data-dismiss="modal">&times;</button>
		  <h4 class="modal-title"><?php echo $g[1];?></h4>
		</div>
		<div class="modal-body">
		  <table class="table table-hover">
			  <thead style="text-align: center;">
				<tr>
				  <th>hour</th>
				  <th>:</th>
				  <th>minute</th>
				  <th>day</th>
				  <th>/</th>
				  <th>month</th>
				  <th>/</th>
				  <th>year</th>
				  <th>week</th>
				  <th></th>
				</tr>
			  </thead>
		    <tr>
		      <td><select class="form-control" name="h_<?php echo $gid;?>" id="h_<?php echo $gid;?>"><?php for($i = 0; $i < 24; $i++) echo "<option value=\"$i\">$i</option>";?></select></td>
		      <td>:</td>
			  <td><select class="form-control" name="i_<?php echo $gid;?>" id="i_<?php echo $gid;?>"><?php for($i = 0; $i < 60; $i=$i+5) echo "<option value=\"$i\">$i</option>";?></select></td>
		      <td><select class="form-control" name="d_<?php echo $gid;?>" id="d_<?php echo $gid;?>"><option value="" selected>every</option><?php for($i = 0; $i < 31; $i++) echo "<option value=\"$i\">$i</option>";?></select></td>
		      <td>/</td>
			  <td><select class="form-control" name="m_<?php echo $gid;?>" id="m_<?php echo $gid;?>"><option value="" selected>every</option><?php for($i = 0; $i < 12; $i++) echo "<option value=\"$i\">$i</option>";?></select></td>
		      <td>/</td>
			  <td><select class="form-control" name="y_<?php echo $gid;?>" id="y_<?php echo $gid;?>"><option value="" selected>every</option><?php for($i = date("Y"); $i < date("Y")+12; $i++) echo "<option value=\"$i\">$i</option>";?></select></td>
			  <td><select class="form-control" name="n_<?php echo $gid;?>" id="n_<?php echo $gid;?>"><option value="" selected>every</option><?php for($i = 0; $i < 7; $i++) echo "<option value=\"$i\">$i</option>";?></select></td>
			  <td class="btn-group" data-toggle="buttons"><label class="btn btn-success active" ><input type="checkbox" checked autocomplete="off" data-onstyle="success" data-offstyle="danger">On</label></td>
		    </tr>
		  </table>
		</div>
		<div class="modal-footer">
		  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		</div>
	  </div>
	  
	</div>
	</div>

<?php endforeach; ?>
</div>
</div>

<!--footer class="container-fluid text-center">
  <p>Footer Text</p>
</footer-->
<form action="" method="post" enctype="multipart/form-data" id="fAction">
<input type="hidden" name="action" value="logout" id="iAction" />
<input type="hidden" name="value" value="" id="iValue" />
<input type="hidden" name="value2" value="" id="iValue2" />
</form>
<script>
submit = function(action, value){
	document.getElementById('iAction').value = action;
	document.getElementById('iValue').value = value;
	document.getElementById('iValue2').value = arguments[2];
	document.getElementById('fAction').submit();
}
$(function() {
  var availableTags = [
    "ActionScript", "AppleScript", "Asp", "BASIC", "C", "C++",
    "Clojure", "COBOL", "ColdFusion", "Erlang", "Fortran",
    "Groovy", "Haskell", "Java", "JavaScript", "Lisp", "Perl",
    "PHP", "Python", "Ruby", "Scala", "Scheme"
  ];
  
  $(".autocomplete").autocomplete({
    source: availableTags
  });
});
</script>
</body>
</html>
