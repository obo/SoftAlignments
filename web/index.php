<?php 
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);
header('Content-Type: text/html; charset=utf-8');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
include('functions.php');

// Get a list of directories in ./data
// And remove first two (. and ..)
foreach(glob('./data/*', GLOB_ONLYDIR) as $dir) {
    $dataDirs[] = basename($dir);
}
// What do we want to see?
if (!isset($_GET['s'])){
	$sentence = 1;
}else{
	$sentence = $_GET['s'];
}
if (!isset($_GET['directory'])){
	$dataDir = $dataDirs[0];
}else{
	$dataDir = $_GET['directory'];
}
$compare = false;
if(file_exists("./data/".$dataDir."/NMT1") && file_exists("./data/".$dataDir."/NMT2")) $compare = true;

//Get a list of all confidences for browsing
$dataFiles = cleanDirArray(scandir("./data/".$dataDir.($compare?"/NMT1":"")));
$confidences = "./data/".$dataDir.($compare?"/NMT1/":"/").array_pop(preg_grep("/\.con\.js/", $dataFiles));
$f4 = gotoLine($confidences, $sentence);
$count = getLineCount($confidences)-2;
$allConfidences = getAllConfidences($f4, $count);
if($sentence > $count) $sentence = $count;

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta name="description" content="NMT Attention Alignments">
	<meta name="author" content="Matīss Rikters">
	<title><?php echo ($compare?"Compare ":""); ?>NMT Attention Alignments</title>
	<!--[if lt IE 9]>
		<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
	<link rel="stylesheet" href="scripts/css/main.css">
	<link rel="stylesheet" href="scripts/css/bootstrap.min.css">
	<link rel="stylesheet" href="scripts/select/bootstrap-select.min.css">
	<link rel='stylesheet' href='scripts/css/perfect-scrollbar.min.css' />
	<script src="scripts/js/jquery-3.2.1.min.js"></script>
	<script src="scripts/js/bootstrap.min.js"></script>
	<script src="scripts/js/perfect-scrollbar.jquery.min.js"></script>
	<script src="scripts/select/bootstrap-select.min.js"></script>
    <script src="scripts/attentionMR.js"></script>
    <script src="scripts/d3.v3.min.js"></script>
    <script src="scripts/saveSvgAsPng.js"></script>
    <script src="scripts/js/html2canvas.js"></script>
    <script type="text/javascript">
    var sentenceNum = <?php echo $sentence; ?>;
    var dataDir = "<?php echo $dataDir; ?>";
	var compare = <?php echo ($compare?"true":"false"); ?>;
    </script>
    <script src="scripts/index.js"></script>
</head>
<body>
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#"><?php echo ($compare?"Compare ":""); ?>NMT Attention Alignments </a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav navbar-center">
		<li>
			<a href="<?php echo ($compare?"?directory=".$dataDir."&s=".($sentence-1):"#"); ?>" <?php echo (!$compare?'onclick="getPrev(dataDir, sentenceNum);"':''); ?>>
				<span class="glyphicon glyphicon-arrow-left" aria-hidden="true"></span>
			</a>
		</li>
        <li>
			<form class="navbar-form" action="?" method="GET" <?php echo (!$compare?'onsubmit="return jumpForm()"':''); ?>>
                <div class="btn-group" data-toggle="buttons">
					<?php echo (!$compare?'
					  <label class="btn btn-default active" id="svgBut">
						<input type="radio" name="type" value="svg" autocomplete="off" checked><span class="glyphicon glyphicon-random" aria-hidden="true"></span>
					  </label>
					  <label class="btn btn-default" id="matBut">
						<input type="radio" name="type" value="matrix" autocomplete="off"><span class="glyphicon glyphicon-th" aria-hidden="true"></span>
					  </label>
				  ':'
                    <button type="reset" style="display:inline;" class="btn btn-default" onclick="toggle(\'#svg\')">
                        <span id="togglesvg" class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                    </button>
                    <a type="reset" id="save" style="display:inline;" class="btn btn-default">
                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span>
                    </a>
					'); ?>
                </div>
				<input class="form-control" style="width:75px; display:inline;" name="s" id="sentenceNum" value="<?php echo $sentence; ?>" type="text" /> 
                <div class="btn-group" role="group">
                    <button style="display:inline;" class="btn btn-default" type="submit">
                        <span class="glyphicon glyphicon-refresh" aria-hidden="true"></span>
                    </button>
					<?php echo (!$compare?'
                    <a type="reset" id="save" style="display:inline;" class="btn btn-default">
                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span>
                    </a>
				  ':'
                    <button type="reset" style="display:inline;" class="btn btn-default" onclick="toggle(\'#other\')">
                        <span id="toggleother" class="glyphicon glyphicon-eye-open" aria-hidden="true"></span>
                    </button>'); ?>
                </div>
				<input type="hidden" name="directory" value="<?php echo $dataDir; ?>" />
				<input type="hidden" name="changeNum" value="True" />
			</form>
		</li>
		<li>
			<a href="<?php echo ($compare?"?directory=".$dataDir."&s=".($sentence+1):"#"); ?>" <?php echo (!$compare?'onclick="getNext(dataDir, sentenceNum);"':''); ?>>
				<span class="glyphicon glyphicon-arrow-right" aria-hidden="true"></span>
			</a>
		</li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li style="padding-top:8px; padding-right:5px;">
			<form action="?">
				<select class="selectpicker" data-live-search="true" name="directory" onchange="setCookie('sortBy', '', 1);setCookie('sortOrder', '', 1);this.form.submit()">
				<?php 
				foreach($dataDirs as $directory){
					$selected = $dataDir==$directory?" SELECTED":"";
					echo "<option value='$directory'$selected>$directory</option>";
				}
				?>
				</select>
			</form>
        </li>
      </ul>
    </div>
  </div>
</nav>
<div style="height:55px;display:block;"></div>
    <div id="loadCont">
		<img id="load" src="scripts/load.svg">
	</div>
<div class="row" style="margin-left:5px;" id="topRow">
</div>
<div class="row">
	<div id="svg"></div>
	<div id="other"></div>
    <div id="matrix"></div>
</div>
<div class="row<?php echo ($compare?" bottomRow":""); ?>" style="margin-left:5px;" id="bottomRow">
</div>
<div class="row<?php echo ($compare?" bottomRow2":""); ?>" style="margin-left:5px;" id="bottomRow2">
</div>
<div id="c5" class="row collapse">
	<span class="glyphicon glyphicon-sort sort" style="margin-top:10px;" onclick="sortAll(6)"></span>
	<span class="glyphicon glyphicon-repeat sort" style="margin-top:40px;" onclick="sortAll(1)"></span>
	<div id="length" style="margin-left:20px;width:<?php echo count($allConfidences)*7;?>px;">
		<?php
			foreach($allConfidences as $key => $scfd){
				echo '<a id="le-'.($key+1).'-'.$scfd[0].'-'.$scfd[1].'-'.$scfd[2].'-'.$scfd[3].'-'.$scfd[4].'" href="?directory='.$dataDir.'&s='.($key+1).'" title="Sentence '.($key+1).' - Length '.$scfd[5].' symbols">
						<div class="progress progress-bar-vertical">
							<div id="translation-'.($key+1).'" class="progress-bar progress-bar-danger" role="progressbar" aria-valuenow="'.$scfd[4].'" aria-valuemin="0" aria-valuemax="100" style="height: '.$scfd[4].'%;">
								<span class="sr-only">'.$scfd[4].'% Complete</span>
							</div>
						</div>
					 </a>';
			}
		?>
	</div>
</div>
<div id="c1" class="row collapse">
	<span class="glyphicon glyphicon-sort sort" style="margin-top:10px;" onclick="sortAll(5)"></span>
	<span class="glyphicon glyphicon-repeat sort" style="margin-top:40px;" onclick="sortAll(1)"></span>
	<div id="confidence" style="margin-left:20px;width:<?php echo count($allConfidences)*7;?>px;">
		<?php
			foreach($allConfidences as $key => $scfd){
				echo '<a id="co-'.($key+1).'-'.$scfd[0].'-'.$scfd[1].'-'.$scfd[2].'-'.$scfd[3].'-'.$scfd[4].'" href="?directory='.$dataDir.'&s='.($key+1).'" title="Sentence '.($key+1).' - Confidence '.$scfd[3].'%">
						<div class="progress progress-bar-vertical">
							<div id="confidence-'.($key+1).'" class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="'.$scfd[3].'" aria-valuemin="0" aria-valuemax="100" style="height: '.$scfd[3].'%;">
								<span class="sr-only">'.$scfd[3].'% Complete</span>
							</div>
						</div>
					 </a>';
			}
		?>
	</div>
</div>
<div id="c2" class="row collapse">
	<span class="glyphicon glyphicon-sort sort" style="margin-top:10px;" onclick="sortAll(2)"></span>
	<span class="glyphicon glyphicon-repeat sort" style="margin-top:40px;" onclick="sortAll(1)"></span>
	<div id="cdp" style="margin-left:20px;width:<?php echo count($allConfidences)*7;?>px;">
		<?php
			foreach($allConfidences as $key => $scfd){
				echo '<a id="cd-'.($key+1).'-'.$scfd[0].'-'.$scfd[1].'-'.$scfd[2].'-'.$scfd[3].'-'.$scfd[4].'" href="?directory='.$dataDir.'&s='.($key+1).'" title="Sentence '.($key+1).' - CDP '.$scfd[0].'%">
						<div class="progress progress-bar-vertical">
							<div id="deviation-'.($key+1).'" class="progress-bar progress-bar-warning" role="progressbar" aria-valuenow="'.$scfd[0].'" aria-valuemin="0" aria-valuemax="100" style="height: '.$scfd[0].'%;">
								<span class="sr-only">'.$scfd[0].'% Complete</span>
							</div>
						</div>
					 </a>';
			}
		?>
	</div>
</div>
<div id="c3" class="row collapse">
	<span class="glyphicon glyphicon-sort sort" style="margin-top:10px;" onclick="sortAll(3)"></span>
	<span class="glyphicon glyphicon-repeat sort" style="margin-top:40px;" onclick="sortAll(1)"></span>
	<div id="apout" style="margin-left:20px;width:<?php echo count($allConfidences)*7;?>px;">
		<?php
			foreach($allConfidences as $key => $scfd){
				echo '<a id="ao-'.($key+1).'-'.$scfd[0].'-'.$scfd[1].'-'.$scfd[2].'-'.$scfd[3].'-'.$scfd[4].'" href="?directory='.$dataDir.'&s='.($key+1).'" title="Sentence '.($key+1).' - APout '.$scfd[1].'%">
						<div class="progress progress-bar-vertical">
							<div id="apout-'.($key+1).'" class="progress-bar" role="progressbar" aria-valuenow="'.$scfd[1].'" aria-valuemin="0" aria-valuemax="100" style="height: '.$scfd[1].'%;">
								<span class="sr-only">'.$scfd[1].'% Complete</span>
							</div>
						</div>
					 </a>';
			}
		?>
	</div>
</div>
<div id="c4" class="row collapse">
	<span class="glyphicon glyphicon-sort sort" style="margin-top:10px;" onclick="sortAll(4)"></span>
	<span class="glyphicon glyphicon-repeat sort" style="margin-top:40px;" onclick="sortAll(1)"></span>
	<div id="apin" style="margin-left:20px;width:<?php echo count($allConfidences)*7;?>px;">
		<?php
			foreach($allConfidences as $key => $scfd){
				echo '<a id="ai-'.($key+1).'-'.$scfd[0].'-'.$scfd[1].'-'.$scfd[2].'-'.$scfd[3].'-'.$scfd[4].'" href="?directory='.$dataDir.'&s='.($key+1).'" title="Sentence '.($key+1).' - APin '.$scfd[2].'%">
						<div class="progress progress-bar-vertical">
							<div id="apin-'.($key+1).'" class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="'.$scfd[2].'" aria-valuemin="0" aria-valuemax="100" style="height: '.$scfd[2].'%;">
								<span class="sr-only">'.$scfd[2].'% Complete</span>
							</div>
						</div>
					 </a>';
			}
		?>
	</div>
</div>
</body>
</html>