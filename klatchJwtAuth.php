<?php
/* 
 * Add on code for using Slack JWTs for user creation and validation
 */

//Start session
session_start();
require_once('php/config.php');

// require_once 'jwt/BeforeValidException.php';
// require_once 'jwt/ExpiredException.php';
// require_once 'jwt/SignatureInvalidException.php';
// require_once 'jwt/JWT.php';

// use \Firebase\JWT\JWT;

//Function to sanitize values received from the form. 
//Prevents SQL injection.
function clean($conn, $str) {
  $str1 = trim($str);
  return mysqli_real_escape_string($conn, $str1);
}

// echo $_COOKIE["jwt_token"]

// foreach (getallheaders() as $name => $value) {
//     echo "$name: $value\n";
// }

// foreach($_SERVER as $key => $val) {
//     echo "$key: $val\n";
// }

// $auth = $_SERVER["HTTP_AUTHORIZATION_"];

// echo $auth;

// $token = array(
//    "iss" => "http://example.org",
//    "aud" => "http://example.com",
//    "iat" => 1356999524,
//    "nbf" => 1357000000
// );

// $test_jwt = JWT::encode($token, $key_priv, 'RS256');

// $test_decoded = JWT::decode($test_jwt, $key_pub, array('RS256'));

// print_r($test_decoded);

// $jwt = explode(" ", $auth)[1];

// $decoded = JWT::decode($jwt, $key_pub, array('RS256'));

// print_r($decoded);

$token = array(
    "iat" => $_SERVER['HTTP_TOKEN_CLAIM_IAT'],
    "iss" => $_SERVER['HTTP_TOKEN_CLAIM_ISS'], //"18xxdepot.com"
    "slack.email" => $_SERVER['HTTP_TOKEN_CLAIM_SLACK_EMAIL'],
    "slack.id" => $_SERVER['HTTP_TOKEN_CLAIM_SLACK_ID'],
    "slack.name" => $_SERVER['HTTP_TOKEN_CLAIM_SLACK_NAME']
);

// check if iss is 18xxdepot.com

// check for slack.id, slack.email

// check if user exists

// if user exists, set session and forward

// if user doesn't exist, create user, set session, and forward

if (trim($token["iss"]) != "18xxdepot.com") {
    header("location: access-denied.html");
    exit;
}

if (trim($token["slack.id"]) == "" || trim($token["slack.email"]) == "") {
    header("location: access-denied.html");
    exit;
}

// setup JSON failure object.
$farray = array("stat" => "fail");
$fail = rtrim(ltrim(json_encode($farray), "["), "]");

$link = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
if (!$link) {
  error_log('Failed to connect to server: ' . mysqli_connect_error());
  echo $fail;
  exit;
}

$slack_id = $token["slack.id"];

$qry_check_members = "SELECT * FROM members WHERE member_id='$slack_id'";
$result_check_members = mysqli_query($link, $qry_check_members);

if ($result_check_members) {
    if (mysqli_num_rows($result_check_members) >= 1) {
        $memberrow = mysqli_fetch_assoc($result_check_members);
        $member_player_id = $memberrow['player_id'];

        $qry_get_player = "SELECT * FROM players WHERE player_id='$member_player_id'";
        $result_get_player = mysqli_query($link, $qry_get_player);

        //Check whether the query was successful or not
        if ($result_get_player) {
            if (mysqli_num_rows($result_get_player) == 1) {
                //Login Successful
                session_regenerate_id();
                $playerrow = mysqli_fetch_assoc($result_get_player);
                $_SESSION['SESS_PLAYER_ID'] = $playerrow['player_id'];
                $login = $playerrow['login'];
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
            error_log("Log In query failed");
            echo $fail;
        }
    } else {
        $login = $token["slack.id"];  // should be slack handle, change once data are there
        $passwd = uniqid();  // password will never be used, set randomly
        $fname = trim(strstr($token["slack.name"], ' ', true));
        $lname = trim(strstr($token["slack.name"], ' ', false));
        $email = $token["slack.email"];

        //Create INSERT query
        $qry_create_player = "INSERT INTO players SET firstname='$fname', lastname='$lname',
              email='$email', login='$login', passwd='$passwrd'";
        // if ($playerscount == 0) { // This is the first player.
        //     $qry .= ", level='admin'"; // First player is admin.
        // }        
        $result_create_player = @mysqli_query( $link, $qry_create_player );
        if($result_create_player) {   // Was the query successful
            // echo 'success';
            $qry_get_player_id = "SELECT player_id FROM players WHERE email='$email' and login='$login'";
            $result_get_player_id = mysqli_query($link, $qry_get_player_id);
            $playerrow = mysqli_fetch_assoc($result_get_player_id);
            $player_id = $playerrow["player_id"];

            $qry_create_member = "INSERT INTO members SET player_id = $player_id, member_id = '$slack_id'";
            $result_create_member = @mysqli_query( $link, $qry_create_member );

            if ($result_create_member) {
                //Login Successful
                session_regenerate_id();
                // $playerrow = mysqli_fetch_assoc($result_get_player);
                $_SESSION['SESS_PLAYER_ID'] = $playerrow['player_id'];
                if ($fname == '') {
                  $firstname = $login;
                } else {
                  $firstname = $fname;
                }
                $_SESSION['SESS_LOGIN'] = $login;
                $_SESSION['SESS_FIRST_NAME'] = $firstname;
                $_SESSION['SESS_LAST_NAME'] = $lastname;
                $_SESSION['SESS_EMAIL_ADDR'] = $email;
                $_SESSION['SESS_PLAYER_LEVEL'] = 'player';
                $_SESSION['SESS_HEADER_MESSAGE'] = 'Login Successful.';
                session_write_close();
                $response = array(
                    "stat" => "success",
                    "id" => $player_id,
                    "login" => $login,
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "level" => 'player',
                    "changeit" => 0
                );

                header("location: board18Main.php");
            } else {
                error_log("Insert new memberL Query failed");
                echo 'fail';
            }
        } else {
            error_log("Insert new player: Query failed");
            echo 'fail';
        }
    } // else {
    //     header("location: access-denied.html");
    // }
} else {
  error_log("Log In query failed");
  echo mysqli_error($link);
  echo $fail;
}

?>
