<?php

/**
 * Recursively copy files from one directory to another.
 * Us this function to circumvent this rename() bug:
 * https://bugs.launchpad.net/ubuntu/+source/php5/+bug/723330
 * 
 * @param String $src - Source of files being moved
 * @param String $dest - Destination of files being moved
 * 
 * @return bool true on success; false on failure
 * 
 * Found at: 
 * https://ben.lobaugh.net/blog/864/php-5-recursively-move-or-copy-files
 */
function rcopy($src, $dest){

    // If source is not a directory stop processing
    if(!is_dir($src)) {return false;}

    // If the destination directory does not exist create it
    if(!is_dir($dest)) { 
        if(!mkdir($dest)) {
            // If the destination directory could not be created stop processing
            return false;
        }    
    }

    // Open the source directory to read in files
    $i = new DirectoryIterator($src);
    foreach($i as $f) {
        if($f->isFile()) {
            copy($f->getRealPath(), "$dest/" . $f->getFilename());
        } else if(!$f->isDot() && $f->isDir()) {
            rcopy($f->getRealPath(), "$dest/$f");
        }
    }
    return true;
}