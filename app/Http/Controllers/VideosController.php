<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Video;
use Ramsey\Uuid\Uuid;
use App\Jobs\ProcessVideo;
use Storage;

class VideosController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('videos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'video' => 'required|mimetypes:video/*'
        ]);
        
        $uuid = Uuid::uuid4()->toString();
        
        if ($request->file('video')->isValid()) {
            $path = $request->video->storeAs('pending_videos', $uuid.'.'.$request->video->extension());
            $path = storage_path('app/'.$path);
            exec('chmod 777 '.$path);
        }
        
        $video = new Video();
        $video->uuid = $uuid; 
        $video->title = $request->input('title');
        $video->video = $uuid.'.'.$request->video->extension();
        $video->save();
        
        ProcessVideo::dispatch($video); 
        
        return redirect()->action('VideosController@index');
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
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
