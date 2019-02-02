<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class detectSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:detectSessions';

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

        foreach($sessions as $session) {
            echo $session['start']->format('d.m.Y H:i')."\t".$session['duration'].PHP_EOL;
            #echo $session['duration'].PHP_EOL;
        }

        #var_dump($sessions);
    }
}
