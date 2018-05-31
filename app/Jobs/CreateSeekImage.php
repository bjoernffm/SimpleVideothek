<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use App\Media;

class CreateSeekImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600;
    public $tries = 1;

    protected $media;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->media->file == null) {
            echo 'No file available'.PHP_EOL;
            return;
        }

        if ($this->media->type != 'VIDEO') {
            echo 'Only videos files are allowed'.PHP_EOL;
            return;
        }

        $inputFile = storage_path().'/app/pending/'.$this->media->file;
        $mediaDir = storage_path().'/app/media';
        $uuid = $this->media->uuid;
        $seconds = env('VIDEO_SEEK_THUMBNAILS_SECONDS', 15);

        echo 'Getting specifications ';
        $output = [];
        exec('ffprobe -loglevel panic -show_streams -show_format -print_format json '.$mediaDir.'/files/'.$uuid.'.mp4', $output);
        $output = implode('', $output);
        $outputJson = json_decode($output, true);
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Generating seek thumbnail for '.$uuid.PHP_EOL;

        echo "\t".'Generating thumbnails every '.$seconds.' seconds ';
        $process = new Process('ffmpeg  -loglevel panic -i '.$mediaDir.'/files/'.$uuid.'.mp4 -vf fps=1/'.$seconds.' /tmp/'.$uuid.'_%04d.png');
        $process->setTimeout(7200);
        $process->setIdleTimeout(7200);
        $process->run();

        if ($process->isSuccessful()) {
            echo '[ OKAY ]'.PHP_EOL;
        } else {
            throw new ProcessFailedException($process);
        }

        echo "\t".'Calculating number of frames ';
        $duration = ceil($outputJson['streams'][0]['duration']);
        $frames = ceil($duration/$seconds);

        if ($duration % $seconds == 0) {
            // in that special case (no rest) add an additional frame to match needs
            $frames++;
        }
        echo '[ OKAY ]'.PHP_EOL;

        echo "\t".'Removing unneeded frames ';
        while(true) {
            $frames++;
            $filename = '/tmp/'.$uuid.'_'.str_pad($frames, 4, "0", STR_PAD_LEFT).'.png';

            if(file_exists($filename) == false) {
                break;
            }

            unlink($filename);
        }
        echo '[ OKAY ]'.PHP_EOL;

        echo "\t".'Combining frames to sprite ';
        exec('montage /tmp/'.$uuid.'_*.png -geometry 100x+0+0 -tile x1 '.$mediaDir.'/seek_thumbnails/'.$uuid.'.png');
        exec('rm /tmp/'.$uuid.'_*.png');
        echo '[ OKAY ]'.PHP_EOL;
    }
}
