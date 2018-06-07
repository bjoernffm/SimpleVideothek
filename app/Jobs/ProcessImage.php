<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Media;
use App\Jobs\CreateThumbnail;

class ProcessImage implements ShouldQueue
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

        echo 'Converting to png ';
        exec('convert '.$inputFile.' '.$mediaDir.'/files/'.$uuid.'.png');
        exec('chmod 777 '.$mediaDir.'/files/'.$uuid.'.png');
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Remove tmp files ';
        exec('rm '.$inputFile);
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Save db record ';
        $this->media->file = $uuid.'.png';
        $this->media->status = 'FINISHED';
        $this->media->save();
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Dispatch thumbnail creation ';
        CreateThumbnail::dispatch($this->media)->onQueue('high');
        echo '[ OKAY ]'.PHP_EOL;
    }
}
