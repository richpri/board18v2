<?php
$rmoutput ;
$exrtn ;
$imageback = '/home/rich/board18test/testbed/images/imgback';
$imagedest = '/home/rich/board18test/testbed/images/imgdest';
$rmcommand = 'rm -rf ' . escapeshellarg($imagedest);
exec($rmcommand, $rmoutput, $exrtn);
rename($imageback,$imagedest); // Backout image directory change.
// echo $rmoutput[0];
