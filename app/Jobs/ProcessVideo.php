<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Media;

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
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
        $inputFile = storage_path().'/app/pending/'.$this->media->file;
        $mediaDir = storage_path().'/app/media';
        $uuid = $this->media->uuid;
        
        echo 'Converting to mp4 ';
        exec('ffmpeg -y -i '.$inputFile.' -loglevel panic -strict -2 -filter:v scale=640:-2 '.$mediaDir.'/files/'.$uuid.'.mp4');
        exec('chmod 777 '.$mediaDir.'/files/'.$uuid.'.mp4');
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Getting specifications ';
        $output = [];
        exec('ffprobe -loglevel panic -show_streams -show_format -print_format json '.$mediaDir.'/files/'.$uuid.'.mp4', $output);
        $output = implode('', $output);
        $outputJson = json_decode($output, true);
        
        $specs = [
            'file' => $uuid.'.mp4',
            'width' => $outputJson['streams'][0]['width'],
            'height' => $outputJson['streams'][0]['height'],
            'length' => (int) $outputJson['streams'][0]['duration'],
            'size' => (int) $outputJson['format']['size']
        ];
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Generating thumbnail ';
        exec('ffmpeg -y -loglevel panic -i '.$mediaDir.'/files/'.$uuid.'.mp4 -filter:v "thumbnail,scale=320:-2" -frames:v 1 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        exec('chmod 777 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Generating seek thumbnail'.PHP_EOL;
        $seconds = env('VIDEO_SEEK_THUMBNAILS_SECONDS', 15);
        echo "\t".'Generating thumbnails every '.$seconds.' seconds ';
        exec('ffmpeg  -loglevel panic -i '.$mediaDir.'/files/'.$uuid.'.mp4 -vf fps=1/'.$seconds.' /tmp/'.$uuid.'_%03d.png');
        echo '[ OKAY ]'.PHP_EOL;

        echo "\t".'Calculating number of frames ';
        $duration = ceil($outputJson['streams'][0]['duration']);
        $frames = ceil($duration/$seconds);

        if ($duration % $seconds == 0) {
            // in that special case (no rest) add an additional frame to match needs
            $frames++;
        }
        echo '[ OKAY ]'.PHP_EOL;

        if ($frames == 0) {
            die('Fehler'.PHP_EOL);
        }

        // removing additional frames, created by ffmpeg
        echo "\t".'Removing unneeded frames ';
        while(true) {
            $frames++;
            $filename = '/tmp/'.$uuid.'_'.str_pad($frames, 3, "0", STR_PAD_LEFT).'.png';

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
        
        echo 'Remove tmp files ';
        exec('rm '.$inputFile);
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Save db record ';
        $this->media->file = $uuid.'.mp4';
        $this->media->thumbnail = $uuid.'.png';
        $this->media->length = $specs['length'];
        $this->media->status = 'FINISHED';
        $this->media->save();
        echo '[ OKAY ]'.PHP_EOL;
    }
}
