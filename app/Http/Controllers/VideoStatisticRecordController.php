<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;

class VideoStatisticRecordController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $media_id)
    {
        $request->request->add(['media_id' => $media_id]);
        $this->validate($request, [
            'user_id' => 'required|exists:users,id',
            'media_id' => 'required|exists:media,uuid',
            'from' => 'required|numeric',
            'to' => 'required|numeric'
        ]);

        $media = Media::where('uuid', $request->input('media_id'))->firstOrFail();

        $stat = new \App\VideoStatisticRecord();
        $stat->user_id = $request->input('user_id');
        $stat->media_id = $media->id;
        $stat->from = $request->input('from');
        $stat->to = $request->input('to');
        $stat->save();

        return $stat;
    }
}
