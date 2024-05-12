<?php

	include_once("config.php");
	include_once("functions.php");
	include_once("head.php");
	
	if (isset($_POST['filterdate']) && $_POST['filterdate'] != "") {
		$filterdate = $_POST['filterdate'];
		$alltimesel = "";
		$onedaysel = "";
		$oneweeksel = "";
		$onemonthsel = "";
		$threemonthssel = "";
		$sixmonthssel = "";
		$oneyearsel = "";
		$useHours = false;
		switch ($filterdate) {
			case "alltime": 
				$getData_sql = "SELECT DATE(time) AS time, ((MIN(outside) + MAX(outside)) / 2) AS outside, ((MIN(inside) + MAX(inside)) / 2) AS inside FROM temp GROUP BY DATE(time) ORDER BY time ASC;";
				$minMax_sql = "";
				$alltimesel = " selected";
				$useHours = false;
				break;
			case "oneweek": 
				$getData_sql = "SELECT * FROM temp WHERE DATE(time) >= NOW() - INTERVAL 7 DAY ORDER BY time ASC;";
				$minMax_sql = " WHERE time >= NOW() - INTERVAL 1 WEEK";
				$oneweeksel = " selected";
				$useHours = true;
				break;
			case "onemonth": 
				$getData_sql = "SELECT * FROM temp WHERE DATE(time) >= NOW() - INTERVAL 30 DAY ORDER BY time ASC;";
				$minMax_sql = " WHERE time >= NOW() - INTERVAL 1 MONTH";
				$onemonthsel = " selected";
				$useHours = true;
				break;
			case "threemonths": 
				$getData_sql = "SELECT DATE(time) AS time, ((MIN(outside) + MAX(outside)) / 2) AS outside, ((MIN(inside) + MAX(inside)) / 2) AS inside FROM temp WHERE DATE(time) >= DATE(NOW()) - INTERVAL 3 MONTH GROUP BY DATE(time) ORDER BY time ASC;";
				$minMax_sql = " WHERE DATE(time) >= DATE(NOW()) - INTERVAL 3 MONTH";
				$threemonthssel = " selected";
				$useHours = false;
				break;
			case "sixmonths": 
				$getData_sql = "SELECT DATE(time) AS time, ((MIN(outside) + MAX(outside)) / 2) AS outside, ((MIN(inside) + MAX(inside)) / 2) AS inside FROM temp WHERE DATE(time) >= DATE(NOW()) - INTERVAL 6 MONTH GROUP BY DATE(time) ORDER BY time ASC;";
				$minMax_sql = " WHERE DATE(time) >= DATE(NOW()) - INTERVAL 6 MONTH";
				$sixmonthssel = " selected";
				$useHours = false;
				break;
			case "oneyear": 
				$getData_sql = "SELECT DATE(time) AS time, ((MIN(outside) + MAX(outside)) / 2) AS outside, ((MIN(inside) + MAX(inside)) / 2) AS inside FROM temp WHERE DATE(time) >= DATE(NOW()) - INTERVAL 1 YEAR GROUP BY DATE(time) ORDER BY time ASC;";
				$minMax_sql = " WHERE DATE(time) >= DATE(NOW()) - INTERVAL 1 YEAR";
				$oneyearsel = " selected";
				$useHours = false;
				break;
			default: 
				$getData_sql = "SELECT * FROM temp WHERE time >= NOW() - INTERVAL 18 HOUR ORDER BY time ASC;";
				$minMax_sql = " WHERE time >= NOW() - INTERVAL 18 HOUR";
				$onedaysel = " selected";
				$useHours = true;
				break;
		}
	} else {
		$getData_sql = "SELECT * FROM temp WHERE time >= NOW() - INTERVAL 18 HOUR ORDER BY time ASC;";
		$minMax_sql = " WHERE time >= NOW() - INTERVAL 18 HOUR";
		$filterdate = "";
		$filterdate_sql = "";
		$alltimesel = "";
		$onedaysel = " selected";
		$oneweeksel = "";
		$onemonthsel = "";
		$threemonthssel = "";
		$sixmonthssel = "";
		$oneyearsel = "";
		$useHours = true;
	}
	if (isset($_POST['showResults'])) {
		if ($_POST['showResults'] == 1) {
			$showResults = true;
			$showResultsChecked = "checked";
		} else {
			$showResults = false;
			$showResultsChecked = "";
		}
	} else {
		$showResults = false;
		$showResultsChecked = "";
	}

	// Get chart data
	$labelsArr = array();
	$insideArr = array();
	$outsideArr = array();

	$main_graph_sql = $pdo->prepare($getData_sql);
	$main_graph_sql->execute();
	while($row = $main_graph_sql->fetch(PDO::FETCH_ASSOC)){
		if ($useHours) {
			if (substr($row['time'],11,2) === "00") {
				array_push($labelsArr, "['".date_format(date_create($row['time']),"H")."', '".date_format(date_create($row['time']),"M j, Y")."']");
			} else {
				array_push($labelsArr, "'".date_format(date_create($row['time']),"H")."'");
			}
			if ($row['inside'] == -100) {array_push($insideArr, $row['outside']);} else {array_push($insideArr, $row['inside']);}
			array_push($outsideArr, $row['outside']);
		} else {
			if (substr($row['time'],8,2) === "01") {
				array_push($labelsArr, "['".date_format(date_create($row['time']),"j")."', '".date_format(date_create($row['time']),"M Y")."']");
			} else {
				array_push($labelsArr, "'".date_format(date_create($row['time']),"j")."'");
			}
			if ($row['inside'] == -100) {array_push($insideArr, $row['outside']);} else {array_push($insideArr, $row['inside']);}
			array_push($outsideArr, $row['outside']);
		}
	}

	// Get current temp for table
	$currTemp_sql = $pdo->prepare("SELECT * FROM temp ORDER BY time DESC LIMIT 1;");
	$currTemp_sql->execute();
	while($row = $currTemp_sql->fetch(PDO::FETCH_ASSOC)){
		$currTempInside = round($row['inside'],0);
		$currTempOutside = round($row['outside'],0);
	}

	// Get min/max data for table
	$minMaxTemp_sql = $pdo->prepare("SELECT MIN(inside) AS minin, MIN(outside) AS minout, MAX(inside) AS maxin, MAX(outside) AS maxout FROM temp".$minMax_sql.";");
	$minMaxTemp_sql->execute();
	while($row = $minMaxTemp_sql->fetch(PDO::FETCH_ASSOC)){
		$minTempInside = round($row['minin'],0);
		$minTempOutside = round($row['minout'],0);
		$maxTempInside = round($row['maxin'],0);
		$maxTempOutside = round($row['maxout'],0);
	}
	
	// Smooth data
	$smoothedInside = smooth_array($insideArr);
	$smoothedOutside = smooth_array($outsideArr);

	// Min/Max temps for scale
	$minMaxArr = array_merge($smoothedInside[0],$smoothedOutside[0]);
	if (round(min_temp($minMaxArr),0) % 2 == 0) {
		$minTemp = round(min_temp($minMaxArr),0) - 2;
	} else {
		$minTemp = round(min_temp($minMaxArr),0) - 1;
	}
	if (round(max_temp($minMaxArr),0) % 2 == 0) {
		$maxTemp = round(max_temp($minMaxArr),0) + 2;
	} else {
		$maxTemp = round(max_temp($minMaxArr),0) + 1;
	}

	// Scale label
	if ($useHours) {$titleText = "Óra";} else {$titleText = "Dátum";}

	// Graph selector
	echo "
	<div class='section'>
		<center>
		<form action='".$_SERVER["REQUEST_URI"]."' method='POST'>

			<div class='inputleft'>
				Időkeret
			</div>
			<div class='inputright'>
				<select name='filterdate' onchange='this.form.submit()'>
					<option value='oneday'".$onedaysel.">1 Nap</option>
					<option value='oneweek'".$oneweeksel.">1 Hét</option>
					<option value='onemonth'".$onemonthsel.">1 Hónap</option>
					<option value='threemonths'".$threemonthssel.">3 Hónapok</option>
					<option value='sixmonths'".$sixmonthssel.">6 Hónapok</option>
					<option value='oneyear'".$oneyearsel.">1 Év</option>
					<option value='alltime'".$alltimesel.">Mindig</option>
				</select>
			</div>
			<div class='clear'></div>

			<div class='inputleft'>
				Adat
			</div>
			<div class='inputright'>
				<div class='onoffswitch'>
					<input type='hidden' name='showResults' value='0'>
					<input type='checkbox' name='showResults' class='onoffswitch-checkbox' id='showResultsswitch' value='1'  onchange='submit();' ".$showResultsChecked.">
					<label class='onoffswitch-label' for='showResultsswitch'>
						<div class='onoffswitch-inner'></div>
						<div class='onoffswitch-switch'></div>
					</label>
				</div>
			</div>
			<div class='clear'></div>

		</form>
		</center>
	</div>";

	// Temp Chart
	echo "
	<div class='section'>
		<div class='chartcanvas' style='height:55vh;'>
			<canvas id='main_graph' style='width:100%;height:100%!important;'></canvas>
		</div>
	</div>";

	// Statistics
	echo "
	<div class='section'>
		<div class='table'>
			<div class='row bold center'>
				<div class='col'>&nbsp;</div>
				<div class='col'>Padlás</div>
				<div class='col'>Külső</div>
			</div>
			<div class='row'>
				<div class='col borl bort borb yellow bold'>Jelenlegi:</div>
				<div class='col center bort borb red bold'>".$currTempInside." \u{00B0}</div>
				<div class='col center bort borr borb blue bold'>".$currTempOutside." \u{00B0}</div>
			</div>
			<div class='row'>
				<div class='col'>Minimális:</div>
				<div class='col center'>".$minTempInside." \u{00B0}</div>
				<div class='col center'>".$minTempOutside." \u{00B0}</div>
			</div>
			<div class='row'>
				<div class='col'>Maximális:</div>
				<div class='col center'>".$maxTempInside." \u{00B0}</div>
				<div class='col center'>".$maxTempOutside." \u{00B0}</div>
			</div>
		</div>
	</div>";

	// Show results 
	echo "
	<div class='section'>
		<!--form action='".$_SERVER["REQUEST_URI"]."' method='POST'>
			<div class='onoffswitch'>
				<input type='hidden' name='showResults' value='0'>
				<input type='checkbox' name='showResults' class='onoffswitch-checkbox' id='showResultsswitch' value='1'  onchange='submit();' ".$showResultsChecked.">
				<label class='onoffswitch-label' for='showResultsswitch'>
					<div class='onoffswitch-inner'></div>
					<div class='onoffswitch-switch'></div>
				</label>
			</div>
		</form-->";

	if ($showResults) {
		echo "
		<div class='results'>
			<pre>Smoothed Data<pre>
			<pre>Inside</pre>";
		foreach ($smoothedInside[1] as $line) {
			echo "<pre>".$line."</pre>";
		}
		echo "
			<pre>Outside</pre>";
		foreach ($smoothedOutside[1] as $line) {
			echo "<pre>".$line."</pre>";
		}
		echo "
		</div>";
	}
	echo "
	</div>";

	// Start chart script
	echo "
	<script>
		new Chart('main_graph', {
			type: 'line',
			data: {
				labels: [".implode(",",$labelsArr)."],
				datasets: [
					{
						label: 'Padlás',
						data: [".implode(",",$smoothedInside[0])."],
						backgroundColor: 'red',
						borderColor: 'red',
						tension: 0.1,
					},
					{
						label: 'Külső',
						data: [".implode(",",$smoothedOutside[0])."], 
						backgroundColor: 'blue',
						borderColor: 'blue',
						tension: 0.1,
					},
				]
			},
			options: {
				elements: {
					line: {
						skipNull: true,
						drawNull: false
					},
					point:{
						radius: 0
					}
				},
				scales: {
					x: {
						ticks: {

						},
						pointLabels: {
							fontStyle: 'bold',
						},
						title: {
							display: true,
							text: '".$titleText."',
						}
					},
					y: {
						display: true,
						min: ".$minTemp.",
						max: ".$maxTemp.",
						ticks: {
						},
						position: 'left',
						title: {
							display: true,
							text: 'Celsius Fok'
						}
					},
					y1: {
						display: true,
						min: ".$minTemp.",
						max: ".$maxTemp.",
						ticks: {
						},
						position: 'right'
					}
				},
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						display: true,
						position: 'top',
					}
				}
			},
		});
	</script>";

	include_once("foot.php");
?>