<?php

	// PDO
	if ($Database['driver'] == 'mysql') {
		try {
			$pdo = new PDO("mysql:host=".$Database['host'].";port=".$Database['port'].";dbname=".$Database['dbname'], $Database['username'], $Database['password']);
		    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$pdo->exec("set names utf8"); //Support utf8
		} catch (PDOException $e) {
			echo 'Connection failed: '.$e->getMessage();
		}
	} elseif ($Database['driver'] == 'odbc') {
		try {
		$pdo = new PDO("odbc:Driver={".$Database['dsn']."};Server=".$Database['host'].";Port=".$Database['port'].";Database=".$Database['dbname'].";User=".$Database['username'].";Password=".$Database['password']);
		    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
			echo 'Connection failed: '.$e->getMessage();
		}
	} else {
		echo "Configuration Error - No database driver specified";
	}

	function smooth_array($dataArr) {
		global $showResults;
		global $smoothingIterations;
		global $constrain_n;
		if (round($smoothingIterations/200*count($dataArr),0) >= $constrain_n)  {$n = $constrain_n;} else {$n = round($smoothingIterations/200*count($dataArr),0);}
		$showResultsArr = array();
		array_push($showResultsArr,"n= ".$n);
		$returnArr = array();
		for ($i = 0; $i < count($dataArr); $i++) {
			if (($i == 0) || ($i == count($dataArr)-1)) {
				array_push($returnArr, $dataArr[$i]);
				array_push($showResultsArr,"i = ".str_pad($i,3," ", STR_PAD_LEFT)." : count = 1 ---> ".$dataArr[$i]);
			} else if ($i < $n) {
				$sumArr = array();
				for ($j = 1; $j < $i+1; $j++) {
					array_push($sumArr, $dataArr[$i-$j]);
					array_push($sumArr, $dataArr[$i+$j]);
				}
				array_push($sumArr, $dataArr[$i]);
				$avg = array_sum($sumArr) / count($sumArr);
				array_push($returnArr, $avg);
				array_push($showResultsArr,"i = ".str_pad($i,3," ", STR_PAD_LEFT)." : count = ".count($sumArr)." : avg = ".number_format($avg,1)." ---> ".implode(", ",$sumArr));
			} else if ($i > count($dataArr)-1-$n) {
				$sumArr = array();
				for ($j = 1; $j < count($dataArr)-$i; $j++) {
					array_push($sumArr, $dataArr[$i-$j]);
					array_push($sumArr, $dataArr[$i+$j]);
				}
				array_push($sumArr, $dataArr[$i]);
				$avg = array_sum($sumArr) / count($sumArr);
				array_push($returnArr, $avg);
				array_push($showResultsArr,"i = ".str_pad($i,3," ", STR_PAD_LEFT)." : count = ".count($sumArr)." : avg = ".number_format($avg,1)." ---> ".implode(", ",$sumArr));
			} else {
				$sumArr = array();
				for ($j = 1; $j < $n+1; $j++) {
					array_push($sumArr, $dataArr[$i-$j]);
					array_push($sumArr, $dataArr[$i+$j]);
				}
				array_push($sumArr, $dataArr[$i]);
				$avg = array_sum($sumArr) / count($sumArr);
				array_push($returnArr, $avg);
				array_push($showResultsArr,"i = ".str_pad($i,3," ", STR_PAD_LEFT)." : count = ".count($sumArr)." : avg = ".number_format($avg,1)." ---> ".implode(", ",$sumArr));
			}
		}
		return array($returnArr,$showResultsArr);
	}

	function min_temp($Arr) {
		$temp = 100;
		foreach ($Arr as $val) {
			if ($val < $temp) {
				$temp = $val;
			}
		}
		return $temp;
	}

	function max_temp($Arr) {
		$temp = -100;
		foreach ($Arr as $val) {
			if ($val > $temp) {
				$temp = $val;
			}
		}
		return $temp;
	}

	// https://halfelf.org/2017/linear-regressions-php/
	function linear_regression( $x, $y ) {
		$n     = count($x);     // number of items in the array
		$x_sum = array_sum($x); // sum of all X values
		$y_sum = array_sum($y); // sum of all Y values
		$xx_sum = 0;
		$xy_sum = 0;
		for($i = 0; $i < $n; $i++) {
			$xy_sum += ($x[$i] * $y[$i]);
			$xx_sum += ($x[$i] * $x[$i]);
		}
		$slope = ( ( $n * $xy_sum ) - ( $x_sum * $y_sum ) ) / ( ( $n * $xx_sum ) - ( $x_sum * $x_sum ) );
		$intercept = ( $y_sum - ( $slope * $x_sum ) ) / $n;
		return array( 
			'slope'     => $slope,
			'intercept' => $intercept,
		);
	}

	function redirect($url) {
		if (!headers_sent()) {    
			header('Location: '.$url);
			exit;
		} else {
			echo '<script type="text/javascript">';
			echo 'window.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
			echo '</noscript>'; exit;
		}
	}

?>
