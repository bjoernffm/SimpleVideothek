<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Media;
use Carbon\Carbon;

class UpdateImdbDetails implements ShouldQueue
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
        echo 'IMDB Id and api credentials ';
        $api_key = env('IMDB_API_KEY', '');

        if ($this->media->imdb_id != '' and $api_key != '') {
            echo '[ AVAILABLE ]'.PHP_EOL;
        } else {
            echo '[ UNAVAILABLE ]'.PHP_EOL;
            return false;
        }
        
        echo 'Getting Details from IMDB ';
        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'http://www.omdbapi.com/?i='.$this->media->imdb_id.'&apikey='.$api_key);
        echo '[ OKAY ]'.PHP_EOL;

        echo 'Save db record ';
        $this->media->imdb_details = (string) $res->getBody();
        $this->media->imdb_details_updated_at = Carbon::now();
        $this->media->save();
        echo '[ OKAY ]'.PHP_EOL;
    }
}
