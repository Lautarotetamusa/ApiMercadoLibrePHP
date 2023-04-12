<?php
#$env = file_get_contents('.env');
#$lines = explode('\n', $env);
$handle = fopen(".env", "r");

if ($handle) {
    while (($line = fgets($handle)) !== false) {
        $values = explode(' ', $line);
        putenv($values[0]."=".$values[1]);
        #echo ($values[0]."=".$values[1]);
    }

    fclose($handle);
}
?>
