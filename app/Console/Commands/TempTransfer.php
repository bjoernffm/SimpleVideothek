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
    protected $signature = 'tmp:size';

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
        $medias = Media::all();

        foreach($medias as $media) {
            if ($media->file != null) {
                $inputFile = storage_path().'/app/media/files/'.$media->file;

                if (file_exists($inputFile)) {
                    $media->size = filesize($inputFile);
                    $media->save();
                } else {
                    $this->error('No file found for '.$media->id);
                    exit();
                }
            }
        }
    }
}
