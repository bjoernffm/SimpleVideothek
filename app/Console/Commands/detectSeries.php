<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class detectSeries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:detectSeries';

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
        $sessions = [];

        $records = DB::table('video_statistic_records')->orderBy('created_at')->get();

        $tmpSession = null;

        foreach($records as $record) {

            $duration = round($record->to - $record->from);

            if ($tmpSession != null) {
                $start = new Carbon($record->created_at);
                if ($start->diffInMinutes($tmpSession['end']) > 20) {
                    $tmpSession['media'] = array_keys($tmpSession['media']);
                    $tmpSession['duration'] = $tmpSession['start']->diffInMinutes($tmpSession['end']);
                    $sessions[] = $tmpSession;
                    $tmpSession = null;
                } else {
                    $tmpSession['end'] = new Carbon($record->created_at);
                    $tmpSession['end']->addSecond($duration);
                    $tmpSession['media'][$record->media_id] = true;
                }
            }

            if ($tmpSession == null) {
                $tmpSession = [
                    'start' => new Carbon($record->created_at),
                    'end' => new Carbon($record->created_at),
                    'media' => [$record->media_id => true]
                ];

                $tmpSession['end']->addSecond($duration);
            }
        }

        $tmpSession['media'] = array_keys($tmpSession['media']);
        $tmpSession['duration'] = $tmpSession['start']->diffInMinutes($tmpSession['end']);
        $sessions[] = $tmpSession;

        $media = [];
        foreach($sessions as $session) {
            if (count($session['media']) == 1) {
                if (!isset($media[$session['media'][0]])) {
                    $media[$session['media'][0]] = ['none' => 0];
                }
                $media[$session['media'][0]]['none']++;
            } else {
                foreach($session['media'] as $item) {
                    if (!isset($media[$item])) {
                        $media[$item] = ['none' => 0];
                    }

                    foreach($session['media'] as $item2) {
                        if ($item == $item2) {
                            continue;
                        }

                        if (!isset($media[$item][$item2])) {
                            $media[$item][$item2] = 0;
                        }

                        $media[$item][$item2]++;
                    }
                }
            }
        }

        $mediaRelative = [];
        foreach($media as $key1 => $value1) {
            $total = array_sum($value1);

            foreach($value1 as $key2 => $value2) {
                $mediaRelative[$key1][$key2] = $value2/$total;
            }

            arsort($mediaRelative[$key1]);

            Cache::forever('recommendated_media_'.$key1, $mediaRelative[$key1]);
        }
    }
}
