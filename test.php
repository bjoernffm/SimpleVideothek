<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

require 'vendor/autoload.php';

exec('ffmpeg -y -i /var/www/videothek/storage/app/pending/043e901c-309f-4917-ab4d-b73a0b7cf6b8.mp4 -loglevel panic -strict -2 -filter:v scale=640:-2 /var/www/videothek/storage/app/media/files/043e901c-309f-4917-ab4d-b73a0b7cf6b8.mp4');
die('bjoern');

$process = new Process('ffmpeg -y -i /var/www/videothek/storage/app/pending/043e901c-309f-4917-ab4d-b73a0b7cf6b8.mp4 -loglevel panic -strict -2 -filter:v scale=640:-2 /var/www/videothek/storage/app/media/files/043e901c-309f-4917-ab4d-b73a0b7cf6b8.mp4');
$process->run();

if (!$process->isSuccessful()) {
    throw new ProcessFailedException($process);
}

echo $process->getOutput();