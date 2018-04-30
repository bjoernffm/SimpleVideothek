<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Video;

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $video;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $inputFile = storage_path().'/app/pending_videos/'.$this->video->video;
        $mediaDir = storage_path().'/app/media';
        $uuid = $this->video->uuid;
        
        echo 'Converting to mp4 ';
        exec('ffmpeg -y -i '.$inputFile.' -loglevel panic -strict -2 -filter:v scale=640:-2 '.$mediaDir.'/videos/'.$uuid.'.mp4');
        exec('chmod 777 '.$mediaDir.'/videos/'.$uuid.'.mp4');
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Getting specifications ';
        $output = [];
        exec('ffprobe -loglevel panic -show_streams -show_format -print_format json '.$mediaDir.'/videos/'.$uuid.'.mp4', $output);
        $output = implode('', $output);
        $output = json_decode($output, true);
        
        $specs = [
            'file' => $uuid.'.mp4',
            'width' => $output['streams'][0]['width'],
            'height' => $output['streams'][0]['height'],
            'length' => (int) $output['streams'][0]['duration'],
            'size' => (int) $output['format']['size']
        ];
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Generating thumbnail ';
        exec('ffmpeg -y -loglevel panic -i '.$mediaDir.'/videos/'.$uuid.'.mp4 -filter:v "thumbnail,scale=320:-2" -frames:v 1 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        exec('chmod 777 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        echo '[ OKAY ]'.PHP_EOL;

        /*ffmpeg -i b2e5008f-0d15-4b02-8aac-e76e9ccd169a.mp4 -vf fps=1/30 out%03d.png
montage *.png -geometry 100x+0+0 -tile x1 overview.png
rm out*

ffmpeg -i b2e5008f-0d15-4b02-8aac-e76e9ccd169a.mp4 -vf fps=1/30 out%03d.png; montage *.png -geometry 100x+0+0 -tile x1 overview.png; rm out**/
        
        echo 'Remove tmp files ';
        exec('rm '.$inputFile);
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Save db record ';
        $this->video->video = $uuid.'.mp4';
        $this->video->thumbnail = $uuid.'.png';
        $this->video->length = $specs['length'];
        $this->video->status = 'FINISHED';
        $this->video->save();
        echo '[ OKAY ]'.PHP_EOL;
    }
}
