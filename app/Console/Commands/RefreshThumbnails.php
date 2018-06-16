<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Media;
use App\Jobs\CreateThumbnail;
use App\Jobs\CreateSeekImage;

class RefreshThumbnails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:refresh-thumbnails
                            {id? : The ID of the media e.g. da7fa978-148c-11e8-946f-00012e3bc7c}
                            {--no-seekables : Wether seekable images should be not created}';

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
        $id = $this->argument('id');
        $seekables = $this->option('no-seekables');

        if ($id == null) {
            $medias = Media::all();
        } else {
            $medias = [Media::where('uuid', $id)->first()];
        }

        foreach($medias as $media) {
            CreateThumbnail::dispatch($media)->onQueue('high');

            if ($media->type == 'VIDEO' and $seekables == false) {
                CreateSeekImage::dispatch($media);
            }
        }

        $this->info('All media dispatched for thumbnail refresh');
    }
}
