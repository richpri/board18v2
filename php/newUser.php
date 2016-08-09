<?php
/*
 * newUser.php is the server side code for the 
 * AJAX newUser call. It inserts a row in the 
 * players table to define a new player.
 * 
 * Input consists the following parameters:
 *   fname
 *   lname
 *   newuser
 *   passwrd
 *   email
 * 
 * Output is the echo return status: 
 *   "success", "fail", "bademail", 
 *   "email xxxx" or "duplicate". 
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */
	session_start();
	require_once('config.php');

	//Function to sanitize values received from the form. 
  //Prevents SQL injection
	function clean( $conn, $str ) {
		$str = @trim($str);
		return mysqli_real_escape_string( $conn, $str);
	}

	$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
	if ( !$link ) {
		error_log('Failed to connect to server: ' . mysqli_connect_error());
		echo 'fail';
		exit; 
	}
	
	//Sanitize the POST values
	$fname = clean( $link, $_REQUEST['fname'] );
	$lname = clean( $link, $_REQUEST['lname'] );
	$login = clean( $link, $_REQUEST['newuser'] );
	$passwrd = clean( $link, $_REQUEST['passwrd'] );
	$email = clean( $link, $_REQUEST['email'] );

	//Check for duplicate login ID
  $qry1 = "SELECT * FROM players WHERE login='$login'";
	$result1 = mysqli_query( $link, $qry1 );
	if($result1) {
		if(mysqli_num_rows($result1) > 0) { // duplicate name!
      echo 'duplicate';
      exit;
		}
	}
	else {
		error_log("Check duplicate name: Query failed");
    echo 'fail';
    exit;
	}
  
  //Check for ill formed email address
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // The email address is ill formed
    echo 'bademail';
    exit;
  }
  
  //Check for duplicate email address
  $qry2 = "SELECT login FROM players WHERE email='$email'";
	$result2 = mysqli_query( $link, $qry2 );
	if($result2) {
		if(mysqli_num_rows($result2) > 0) { // duplicate email!
      $playerrow = mysqli_fetch_assoc($result2);
      $duperr = 'email' . $playerrow['login'];
      echo $duperr;
      exit;
		}
	}
	else {
		error_log("Check duplicate email: Query failed");
		echo 'fail';
    exit;
	}

  //Check for empty players table. IE: Is this first player?
  $qry3 = "SELECT COUNT(*) FROM players";
	$result3 = mysqli_query( $link, $qry3 );
	if($result3) {
    $array3 = mysqli_fetch_array($result3);
    $playerscount = $array3[0];
	} else {
		error_log("Check first player: Query failed");
		echo 'fail';
    exit;
	}
	
	//Create INSERT query
	$qry = "INSERT INTO players SET firstname='$fname', lastname='$lname',
          email='$email', login='$login', passwd='$passwrd'";
  if ($playerscount == 0) { // This is the first player.
    $qry .= ", level='admin'"; // First player is admin.
	}        
	$result = @mysqli_query( $link, $qry );
	if($result) {   // Was the query successful
		echo 'success';
	}else {
		error_log("Insert new player: Query failed");
    echo 'fail';
	}
?>