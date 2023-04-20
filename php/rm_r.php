<?php
/**
 * Recursively delete a directory and all of it's contents - 
 * e.g.the equivalent of `rm -r` on the command-line.
 * Consistent with `rmdir()` and `unlink()`, 
 * an E_WARNING level error will be generated on failure.
 *
 * @param string $dir absolute path to directory to delete
 *
 * @return bool true on success; false on failure
 *
 * Found at: 
 * https://gist.github.com/mindplay-dk/a4aad91f5a4f1283a5e2
 */
function rm_r($dir)
{
    if (false === file_exists($dir)) {
        return false;
    }
    
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        if ($fileinfo->isDir()) {
            if (false === rmdir($fileinfo->getRealPath())) {
                return false;
            }
        } else {
            if (false === unlink($fileinfo->getRealPath())) {
                return false;
            }
        }
    }
    return rmdir($dir);
}
?>