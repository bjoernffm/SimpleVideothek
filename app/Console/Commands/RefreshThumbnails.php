<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Media;
use App\Jobs\CreateThumbnail;

class RefreshThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:refresh-thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create new thumbnails for all media';

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
        $medias = Media::all();
        #$medias = [Media::find(321)];
        foreach($medias as $media) {
            CreateThumbnail::dispatch($media);
        }
        $this->info('All media dispatched for thumbnail refresh');
    }
}
