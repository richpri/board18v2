<?php
/*
 * validateUser.php is the server side code for the 
 * AJAX validateUser call. 
 * 
 * It checks the current players login and password and, 
 * if they are valid, it logs the current player in to 
 * BOARD18.
 * 
 * Input consists the following parameters:
 *   login
 *   password
 * 
 * Output will be an array containing at a minimum the named
 * value "stat" which will be set to "success", "fail" or an 
 * edit failure code. If "stat" = "success" then the array
 * will also contain values for the following key names:
 *   "id" 
 *   "login" 
 *   "firstname" 
 *   "lastname" 
 *   "level" 
 *   "changeit" 
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

//Start session
session_start();
require_once('config.php');

//Function to sanitize values received from the form. 
//Prevents SQL injection.
function clean($conn, $str) {
  $str1 = trim($str);
  return mysqli_real_escape_string($conn, $str1);
}

// setup JSON failure object.
$farray = array("stat" => "fail");
$fail = rtrim(ltrim(json_encode($farray), "["), "]");

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  $errmsg1 = 'validateUser.php: failed to connect to server: ';
  error_log($errmsg1 . mysqli_connect_error());
  echo $fail;
  exit;
}

//Sanitize the parameter values
$login = clean($link, $_REQUEST['login']);
$password = clean($link, $_REQUEST['password']);

//Create query
$qry = "SELECT * FROM players WHERE login='$login' AND passwd='$password'";
$result = mysqli_query($link, $qry);

//Check whether the query was successful or not
if ($result) {
  if (mysqli_num_rows($result) == 1) {
    //Login Successful
    session_regenerate_id();
    $playerrow = mysqli_fetch_assoc($result);
    $_SESSION['SESS_PLAYER_ID'] = $playerrow['player_id'];
    if ($playerrow['firstname'] == '') {
      $firstname = $login;
    } else {
      $firstname = $playerrow['firstname'];
    }
    $_SESSION['SESS_LOGIN'] = $login;
    $_SESSION['SESS_FIRST_NAME'] = $firstname;
    $_SESSION['SESS_LAST_NAME'] = $playerrow['lastname'];
    $_SESSION['SESS_EMAIL_ADDR'] = $playerrow['email'];
    $_SESSION['SESS_PLAYER_LEVEL'] = $playerrow['level'];
    $_SESSION['SESS_HEADER_MESSAGE'] = 'Login Successful.';
    session_write_close();
    $response = array(
        "stat" => "success",
        "id" => $playerrow['player_id'],
        "login" => $login,
        "firstname" => $firstname,
        "lastname" => $playerrow['lastname'],
        "level" => $playerrow['level'],
        "changeit" => $playerrow['changeit']
    );
  } else {
    //Login failed
    $response = array(
        "stat" => "no"
    );
  }
  $res = rtrim(ltrim(json_encode($response), "["), "]");
  echo $res;
} else {
  error_log("validateUser.php: Log In query failed");
  echo $fail;
}
