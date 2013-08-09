<?php

$version = exec('git rev-parse --abbrev-ref HEAD');
if ($version == 'HEAD') {
    $version = exec('git describe --abbrev=0 --tags');
}

echo $version;
