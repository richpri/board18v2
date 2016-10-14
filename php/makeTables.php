<?php
/* 
 * The makeTables.php file is included via "require_once()" into
 * various board18 PHP pages.  It supplies a number of functions
 * for creating the HTML to display various tables on the page.
 * These tables must be displayed on initial creation of these 
 * pages. They cannot be updated later by these functions without
 * reloading the entire page. This file contains these functions:
 * 
 * showBoxes($conn) - create a table of all game boxes in database.
 * showPlayers($conn) - create a table of all players in database.
 * gamePlayers($gameid, $conn) - create a table of players in game.
 * 
 * The makeTables.php file initializes these variables:
 *
 * $theLink - value returned by mysqli_connect function.
 * $open - set to '' if the database connect succeeded.
 *       - set to 'fail' if the database connect failed.
 * 
 * Copyright (c) 2013 Richard E. Price under the The MIT License.
 * A copy of this license can be found in the LICENSE.text file.
 */

require_once('php/auth.php');
require_once('php/config.php');

$theLink = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_DATABASE);
$open = '';
if (!$theLink) {
  error_log('Failed to connect to server: ' . mysqli_connect_error());
  $open = 'fail';
  exit;
}

function showBoxes($conn) {
  global $open;
  $qry = "SELECT box_id, bname, version, author, create_date 
          FROM box WHERE status = 'Active' ORDER BY bname, version";
  $result = mysqli_query($conn, $qry);
  if ($result) {
    if (mysqli_num_rows($result) !== 0) {
      echo "<table border='1'> <tr>
        <th>ID</th> <th>Box Name</th> <th>Version</th>
        <th>Author</th> <th>Date</th> </tr>";
      while ($row = mysqli_fetch_array($result)) {
        echo "<tr class='gbrow'> <td class='gbid'>$row[0]</td> 
          <td>$row[1]</td> <td>$row[2]</td>
          <td>$row[3]</td> <td>$row[4]</td> </tr>";
      }
      echo "</table>";
    } else {
      echo "<p style='color: red'>";
      echo "There are no game boxes in the database</p>";
    }
  } else {
    error_log('Show boxes: select call failed.');
    $open = 'fail';
    exit;
  }
}

function showPlayers($conn) {
  global $open;
  $qry = "SELECT login, firstname, lastname FROM players ORDER BY login";
  $result = mysqli_query($conn, $qry);
  if ($result) {
    if (mysqli_num_rows($result) !== 0) {
      echo "<h3 style='text-indent: 15px'>Players<br></h3>";
      while ($row = mysqli_fetch_array($result)) {
        $pline  = "<p class=\"plid\" ";
        $pline .= "onmouseover=\"this.innerHTML='$row[1] $row[2]';\" ";
        $pline .= "onmouseout=\"this.innerHTML='$row[0]';\" ";
        $pline .= "id='$row[0]'>$row[0]</p>";
        echo $pline;
      }
    } else {
      echo "<p style='color: red'>";
      echo "There are no players in the database.</p>";
    }
  } else {
    error_log('Show players: select call failed.');
    $open = 'fail';
    exit;
  }
}

function gamePlayers($gameid, $conn) {
  $qry0 = "SELECT gname FROM game WHERE game_id='$gameid'";
  $result0 = mysqli_query($conn,$qry0);
  if (!$result0 || (mysqli_num_rows($result0) !== 1)) { 
    $logMessage = 'Failed to find name for game' . mysqli_error($conn);
    error_log($logMessage);
    $open = 'fail';
    exit;
  } else {
    $rowg = mysqli_fetch_array($result0);
    $gname =  $rowg[0];
  }

  $qry1 = "SELECT a.player_id, b.login, b.firstname, b.lastname
            FROM game_player AS a 
              JOIN (players AS b)
                ON (a.game_id = $gameid
                    AND a.player_id = b.player_id)
            ORDER BY a.player_id";
  $result1 = mysqli_query($conn,$qry1);
  if ($result1) {
    if (mysqli_num_rows($result1) === 0) { // no players.
      error_log('Failed to find any players for game ' . $gname);
      $open = 'fail';
    } else {
      echo "<caption>List of players in<br>$gname</caption> <tr>
      <tr><th>Login</th><th>First Name</th><th>Last Name</th></tr>";
      while ($row = mysqli_fetch_array($result1)) {
        echo "<tr class='playerrow'> <td class='login'>$row[1]</td> 
          <td>$row[2]</td> <td>$row[3]</td> </tr>";
      }
    }
  } else {
    $logMessage = 'Error on SELECT query: ' . mysqli_error($conn);
    error_log($logMessage);
    $open = 'fail';
  }
}
?>
