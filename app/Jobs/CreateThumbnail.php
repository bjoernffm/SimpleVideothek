<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Storage;
use App\Media;

class CreateThumbnail implements ShouldQueue
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
        $uuid = $this->media->uuid;
        echo 'Processing '.$uuid.PHP_EOL;

        if ($this->media->file == null) {
            echo 'No file available'.PHP_EOL;
            return;
        }

        $inputFile = storage_path().'/app/media/files/'.$this->media->file;
        $mediaDir = storage_path().'/app/media';

        Storage::delete($mediaDir.'/thumbnails/'.$uuid.'.png');

        echo 'Generating thumbnail ';
        if ($this->media->type == 'DIRECTORY') {
            exec('convert '.$inputFile.' -resize 320 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        } else if ($this->media->type == 'COMIC ') {
            exec('convert '.$inputFile.' -resize 320 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        } else if ($this->media->type == 'IMAGE') {
            exec('convert '.$inputFile.' -resize 320 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        } else if ($this->media->type == 'VIDEO') {
            exec('ffmpeg -y -loglevel panic -i '.$inputFile.' -filter:v "thumbnail,scale=480:-2" -frames:v 1 '.$mediaDir.'/thumbnails/'.$uuid.'.png');
        }
        echo '[ OKAY ]'.PHP_EOL;

        exec('chmod 777 '.$mediaDir.'/thumbnails/'.$uuid.'.png');

        echo 'Save db record ';
        $this->media->thumbnail = $uuid.'.png';
        $this->media->save();
        echo '[ OKAY ]'.PHP_EOL;
    }
}
