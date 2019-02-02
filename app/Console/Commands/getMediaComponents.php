<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Tag;
use Illuminate\Support\Facades\DB;

class getMediaComponents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:calculateComponents';

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
        $this->info('Retrieve tags');

        $tags = Tag::all();
        $tagMap = [];
        foreach($tags as $tag) {
            $tagMap[$tag->id] = $tag;
        }

        $this->info('Go through data');
        $records = DB::table('media_tag')->get();

        $buffer = [];
        $videos = [];
        foreach($records as $record) {
            if(!isset($videos[$record->media_id])) {
                $videos[$record->media_id] = true;
            }
            if(!isset($buffer[$record->tag_id])) {
                $buffer[$record->tag_id] = 0;
            }
            $buffer[$record->tag_id]++;
        }

        asort($buffer);
        $videos = array_keys($videos);

        foreach($buffer as $key => $value) {
            $percentage = ($value/count($videos));
            echo str_pad($tagMap[$key]->name, 10)."\t";
            echo str_pad(round($percentage*100), 4);
            echo str_repeat("#",(round($percentage*50))).PHP_EOL;
        }
    }
}
