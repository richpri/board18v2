<?php
/*
 * This is the server side code for the AJAX tokenCheck call.
 * 
 * It deletes from the auth_tokens table any expired rows with
 * the given selector. It also checks each such row for a matching
 * validator. If it finds one it will return "tokenfound" and it
 * will also delete the matching row from the table.
 * 
 * Input is: selector and validator.
 * 
 * Output is the echo return status: 
 *   "tokenfound", "notoken" or "fail"
 *
 * Note that this script does not check for a signed on user.
 * This is because it is called before any signon has taken place.
 *
 * Copyright (c) 2017 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 * 
 */

require_once('config.php');

$link = mysqli_connect(DB_HOST, DB_USER, 
        DB_PASSWORD, DB_DATABASE);
if (mysqli_connect_error()) {
  $logMessage = 'tokenCheck.php: failed to connect to server.';
  error_log($logMessage);
  echo "fail";
  exit;
}
mysqli_set_charset($link, "utf8mb4");

//Function to sanitize values received from the form. 
//Prevents SQL injection
function clean($link, $str1) {
  $str = trim($str1);
  return mysqli_real_escape_string($link, $str);
}

//Sanitize the POST value
$selector = clean($link, $_REQUEST['selector']);
$validator = $_REQUEST['validator']; // don't need to sanitize this.

//Get record from the auth_tokens table.
$qry1 = "SELECT * FROM auth_tokens WHERE selector='$selector'";
$result1 = mysqli_query($link, $qry1);
if (!$result1) {
  error_log("tokenCheck.php: SELECT FROM auth_tokens - Query failed");
  echo "fail";
  exit;
} else {
  if (mysqli_num_rows($result1) === 0) {
    echo "notoken";
    exit;
  } else {
    while ($tokenrow = mysqli_fetch_assoc($result1)) {
      if ($tokenrow['expires'] < time()) { 
        deleteAuth($link, $tokenrow['token_id']);
        continue;
      }
      if (hash_equals($tokenrow['hashedValidator'], $validator)) {
        deleteAuth($link, $tokenrow['token_id']);
        // Log player in. 
        // The selector-validator match obviates the need to check the password.
        $playerid = $tokenrow['player_id'];
        $qry = "SELECT * FROM players WHERE player_id='$playerid'";
        $result = mysqli_query($link, $qry);

        // Check whether the query was successful or not
        if ($result && mysqli_num_rows($result) == 1) {
          // Query Successful
          session_start();
          $playerrow = mysqli_fetch_assoc($result);
          $_SESSION['SESS_PLAYER_ID'] = $playerrow['player_id'];
          if ($playerrow['firstname'] == '') {
            $firstname = $playerrow['login'];
          } else {
            $firstname = $playerrow['firstname'];
          }
          $_SESSION['SESS_LOGIN'] = $playerrow['login'];
          $_SESSION['SESS_FIRST_NAME'] = $firstname;
          $_SESSION['SESS_LAST_NAME'] = $playerrow['lastname'];
          $_SESSION['SESS_EMAIL_ADDR'] = $playerrow['email'];
          $_SESSION['SESS_PLAYER_LEVEL'] = $playerrow['level'];
          $_SESSION['SESS_HEADER_MESSAGE'] = 'Login Session Created.';
          session_write_close();
          echo "tokenfound";
          exit; 
        } else {
          error_log("tokenCheck.php: players query failed");
          echo "fail";
          exit;
        }
      }
    }
    echo "notoken";
  }
}

// This function will delete a row from the auth_tokens table. 
// The deleted row will have the `token_id` field equal to $id.
function deleteAuth($link, $id) {
  $qry2 = "DELETE FROM auth_tokens WHERE token_id=$id";
  $result2 = mysqli_query($link, $qry2);
  if (!$result2) {
    error_log("tokenCheck.php: DELETE FROM auth_tokens - Query failed");
    echo "fail";
    exit;
  }
}