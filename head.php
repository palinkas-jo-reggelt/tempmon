<?php

	include_once("config.php");
	include_once("functions.php");

	if ($require_login) {
		if (isset($_COOKIE['username']) && isset($_COOKIE['password'])) {
			if (!($_COOKIE['password'] === md5($logins[$_COOKIE['username']]))) {
				redirect("login.php?ruri=".urlencode($_SERVER["REQUEST_URI"]));
			}
		} else {
			redirect("login.php?ruri=".urlencode($_SERVER["REQUEST_URI"]));
		}
	}

	echo "
<!DOCTYPE html> 
<html>
<head>
<title>Hőmérséklet Monitor</title>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>
<meta http-equiv='Content-Style-Type' content='text/css'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet'>
<link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet'>
<script src='https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js'></script>
<script src='https://cdn.jsdelivr.net/gh/jquery/jquery@3/dist/jquery.min.js'></script>
<style type='text/css'>
	body {background: #fefefe; font-family: 'Roboto';}

	a:link, a:active, a:visited {color: blue; text-transform: underline;}
	a:hover {color: #FF0000; text-transform: none;}

	.header {position: fixed; top: 0; left: 0; width: 100%; height: 35px; color: #000; background: #fefefe; z-index: 1; overflow: hidden; text-align:center;}
	.header h1 {font-size: 20px; text-align:center; margin-top:10px;}
	.wrapper {max-width: 1020px; position: relative; margin: 50px auto 30px auto;}
	.section {padding: 5px 5px 15px 5px;}
	.section h1 {font-size: 30px; text-align: center; font-family: 'Oswald', sans-serif;}
	.section h2 {font-size: 20px; text-align: center; margin:10px,0,0,0;}
	.section h3 {font-size: 15px; font-weight: bold;}
	.section h4 {font-size: 20px; font-weight: bold;}
	.secleft {float: left; width: 150px; text-align: right;}
	.secright {text-align: left; padding-left: 15px; margin-left: 150px;}
	.footer {width: 100%; text-align: center;}
	.results {overflow-x:auto; padding-top: 15px;}
	.inputleft {float:left; display:block; width:calc(48% - 20px); text-align:right; padding:5px 10px;}
	.inputright {float:right; display:block; width:calc(52% - 20px); text-align:left; padding:5px 10px;;}

	.table {display: table; margin: auto;}
	.row {display: table-row;}
	.col {display: table-cell; min-width: 70px;}

	.clear {clear: both;}
	.bold {font-weight: bold;}
	.outline {border: 1px solid black; border-radius: 5px; background: #F8F8F8;}
	.left {text-align: left;}
	.right {text-align: right;}
	.center {text-align: center;}
	.bort {border-top: 1px solid black;}
	.borr {border-right: 1px solid black;}
	.borb {border-bottom: 1px solid black;}
	.borl {border-left: 1px solid black;}
	.blue {background:#b3b3ff;}
	.red {background:#ffb3b3;}
	.yellow {background:#ffffb3;}

	ul {list-style-type: none; padding: 0;}
	li {padding: 0; display: inline;}
	input[type=button], input[type=submit], input[type=date], .button {min-width:65px;}
	input[type=text], textarea, input[type=button], input[type=submit], input[type=date], .button, select {border: 1px solid #ccc; border-radius:5px;}
	select {min-height:28px;}
	textarea {font-family: 'Roboto Mono', monospace; font-size:0.8em; min-height:50px;}
	pre {font-family: Consolas, monospace; margin: 0; padding: 0; font-size: 14px;}

	.onoffswitch {position: relative; width: 40px; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; top: 0px; left: 0px; margin: 10px; }
	.onoffswitch-checkbox {display: none;}
	.onoffswitch-label {display: block; overflow: hidden; cursor: pointer; border: 2px solid #999999; border-radius: 20px;}
	.onoffswitch-inner {width: 200%; margin-left: -100%; -moz-transition: margin 0.2s ease-in 0s; -webkit-transition: margin 0.2s ease-in 0s; -o-transition: margin 0.2s ease-in 0s; transition: margin 0.2s ease-in 0s;}
	.onoffswitch-inner:before, .onoffswitch-inner:after {float: left; width: 50%; height: 5px; padding: 0; line-height: 5px; font-size: 14px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box;}
	.onoffswitch-inner:before {content: ''; padding-left: 10px; background-color: blue; color: #FFFFFF;}
	.onoffswitch-inner:after {content: ''; padding-right: 10px; background-color: #EEEEEE; color: #999999; text-align: right;}
	.onoffswitch-switch {width: 15px; margin: -5px; background: #FFFFFF; border: 2px solid #999999; border-radius: 20px; position: absolute; top: 0; bottom: 0; right: 31px; -moz-transition: all 0.2s ease-in 0s; -webkit-transition: all 0.2s ease-in 0s; -o-transition: all 0.2s ease-in 0s; transition: all 0.2s ease-in 0s;}
	.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {margin-left: 0;}
	.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {right: 0px;}

	@media only screen and (max-width: 629px) {
		.wrapper {margin: 35px auto 30px auto;}
		.secleft {float: none; width: 100%; text-align: left;	}
		.secright {float: none; margin: 0; padding-left: 15px; width: 100%; text-align: left;}
	}
</style>
</head>
<body>

<div class='header'>
	<h1>Hőmérséklet Monitor</h1>
</div>

<div class='wrapper'>";

?>