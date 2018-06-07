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

class ProcessVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    public $timeout = 14400;
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
        
        echo 'Converting to mp4 ';
        $process = new Process('ffmpeg -y -i '.$inputFile.' -loglevel panic -strict -2 -filter:v scale=640:-2 '.$mediaDir.'/files/'.$uuid.'.mp4');
        $process->setTimeout(14400);
        $process->setIdleTimeout(14400);
        $process->run();

        if ($process->isSuccessful()) {
            echo '[ OKAY ]'.PHP_EOL;
        } else {
            throw new ProcessFailedException($process);
        }

        echo 'Change mode to 777 ';
        $process = new Process('chmod 777 '.$mediaDir.'/files/'.$uuid.'.mp4');
        $process->run();

        if ($process->isSuccessful()) {
            echo '[ OKAY ]'.PHP_EOL;
        } else {
            throw new ProcessFailedException($process);
        }
        
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
        
        echo 'Remove tmp files ';
        exec('rm '.$inputFile);
        echo '[ OKAY ]'.PHP_EOL;
        
        echo 'Save db record ';
        $this->media->file = $uuid.'.mp4';
        $this->media->length = $specs['length'];
        $this->media->status = 'FINISHED';
        $this->media->save();
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Dispatch thumbnail creation ';
        CreateThumbnail::dispatch($this->media)->onQueue('high');
        CreateSeekImage::dispatch($this->media);
        echo '[ OKAY ]'.PHP_EOL;
    }
}
