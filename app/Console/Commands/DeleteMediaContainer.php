<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Media;

class DeleteMediaContainer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:delete-container
                            {id : The ID of the media container e.g. da7fa978-148c-11e8-946f-00012e3bc7c}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a media container';

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
        $media = Media::where('uuid', $this->argument('id'))->first();

        if ($media == null) {
            $this->error('No media found for '.$id);
            return;
        }

        if ($this->confirm('Do you wish to delete all children? (default: keeping the children and rise the level')) {

            $counter = 0;
            $children = $media->getChildren(true)->reverse();

            foreach($children as $child) {
                $media = Media::find($child->id);
                $media->delete();

                $this->comment('Deleted '.$child->uuid.' ('.$child->title.')');
                $counter++;
            }

            $this->info('Media deleted: '.$counter);
        } else {
            $media->delete();
            $this->info('Deleted '.$media->uuid);
            $this->comment('Children kept and rised to higher level');
        }
    }
}
