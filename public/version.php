<?php

if (file_exists(__DIR__ . '/../application/configs/version')) {
    $version = file_get_contents(__DIR__ . '/../application/configs/version');
} else {
    $version = 'unknown';
}

echo $version;
