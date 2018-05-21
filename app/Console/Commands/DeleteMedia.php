<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Media;

class DeleteMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:delete
                            {id* : The ID of the media e.g. da7fa978-148c-11e8-946f-00012e3bc7c}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete one or multiple media records';

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
        $ids = $this->argument('id');

        $counter = 0;
        foreach($ids as $id) {
            $media = Media::where('uuid', $id)->first();

            if ($media == null) {
                $this->error('No media found for '.$id);
            } else {
                if (($media->left+1) < $media->right) {
                    $this->error('Media '.$id.' is a container');
                    $this->comment('Use `php artisan media:delete-container '.$id.'` instead');
                } else {
                    $media->delete();
                    $this->comment('Deleted '.$media->uuid);
                    $counter++;
                }
            }
        }

        $this->info('Media deleted: '.$counter);
    }
}
