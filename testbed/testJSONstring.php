#!/usr/bin/php
<?php
$newgame = '{"gname": "game name","boxid": "7","players": ["rich", "allen", "sharon"]}';
$game = json_decode($newgame, true);
$textgame = json_encode($game);
$errorgame = json_last_error();
var_dump($newgame);
var_dump($textgame);
var_dump($errorgame);
?>
