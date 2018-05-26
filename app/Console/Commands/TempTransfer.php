<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Video;
use App\Media;
use \Exception;

class TempTransfer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tmp:transfer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $root = Media::where('uuid', 'b5b50809-9ff5-4468-bb11-2898c7785134')->firstOrFail();
        $videos = Video::all();

        foreach($videos as $video) {
            try {
                $this->info('Processing '.$video->uuid);
                $media = new Media();
                $media->uuid = $video->uuid;
                $media->title = $video->title;
                $media->status = $video->status;
                $media->type = 'VIDEO';
                $media->file = $video->video;
                $media->length = $video->length;
                $root->appendChild($media);
            } catch(Exception $e) {}
        }
    }
}
