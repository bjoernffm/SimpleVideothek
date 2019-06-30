<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Video;
use Ramsey\Uuid\Uuid;
use App\Jobs\ProcessVideo;
use Storage;

class VideosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $videos = Video::all();
        return view('videos.index')->with('videos', $videos);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $video = Video::where('uuid', $id)->firstOrFail();
        return view('videos.show')
                ->with('video', $video)
                ->with('title', $video->title);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $video = Video::where('uuid', $id)->firstOrFail();
        return view('videos.edit')->with('video', $video);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $video = Video::where('uuid', $id)->firstOrFail();
        
        Storage::delete([
            'media/videos/' . $video->uuid . '.mp4',
            'media/thumbnails/' .  $video->uuid . '.png'
        ]);

        $video->delete();
        return redirect()->action('VideosController@index');
    }
}
