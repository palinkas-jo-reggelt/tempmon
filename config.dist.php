<?php

	// Login variables
	$require_login          = false; // Require username/password login to view page
	$cookie_duration        = 90;    // Cookie duration in days
	$logins = array(                 // Array of usernames => passwords
		'admin'  => '123456',
		'arpi'   => '654321',
		'fanni'  => '12foo34'
	);

	// Data smoothing by rolling average - setting very high will flatten the curves. 
	$smoothingIterations    = 10;	 // Number of data points as percentage of total data points to use in rolling average smoothing
	$constrain_n            = 3;     // Maximum number of data points to use in smoothing function - set to 0 for no smoothing
	$showResults            = false; // Show results of smoothing below chart - useful for debugging

/*	Database Variables 

	MySQL only!
	
	For MySQL connection string use 'driver' = 'mysql'
	For ODBC  connection string use 'driver' = 'odbc'
*/
	$Database = array (
		'host'              => 'localhost',
		'username'          => 'tempmonitorDBusername',
		'password'          => 'supersecretpassword',
		'dbname'            => 'tempmonitorDBname',
		'driver'            => 'mysql',
		'port'              => '3306',
		'dsn'               => 'MariaDB ODBC 3.0 Driver'
	);

/*	Create tables SQL

	CREATE TABLE `temp` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `time` datetime NOT NULL,
	  `inside` decimal(3,1) NOT NULL,
	  `outside` decimal(3,1) NOT NULL
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

	ALTER TABLE `temp`
	  ADD PRIMARY KEY (`id`),
	  ADD UNIQUE KEY `time` (`time`);
*/

?>