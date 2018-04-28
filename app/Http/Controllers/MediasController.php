<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;
use Ramsey\Uuid\Uuid;
use App\Jobs\UpdateImdbDetails;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessImage;

class MediasController extends Controller
{

    public function index()
    {
        return $this->show('da7fa978-148c-11e8-946f-00012e3bc7c6');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $media = Media::where('uuid', $id)->firstOrFail();

        if ($media->type == 'DIRECTORY') {
            return view('medias.index')
                    ->with('media', $media)
                    ->with('children', $media->getChildren());
        } else if ($media->type == 'COMIC') {
            return view('medias.show_comic')
                    ->with('media', $media)
                    ->with('children', $media->getChildren());
        } else {
            #UpdateImdbDetails::dispatch($media);
            return view('medias.show_video')
                    ->with('media', $media)
                    ->with('imdb_details', json_decode($media->imdb_details));
        }
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $root_media = Media::find(1);
        return view('medias.create')->with('directories', $root_media->getDirectoriesForSelect());
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
            'type' => 'required',
            'root' => 'required',
            'media' => 'required|mimetypes:video/*,image/*'
        ]);

        $uuid = Uuid::uuid4()->toString();
        var_dump($uuid);

        if ($request->file('media')->isValid()) {
            $path = $request->media->storeAs('pending', $uuid.'.'.$request->media->extension());
            $path = storage_path('app/'.$path);
            exec('chmod 777 '.$path);
        }

        $root = Media::where('uuid', $request->input('root'))->firstOrFail();

        DB::table('media')
            ->where('right', '>=', $root->right)
            ->increment('right', 2);

        $media = new Media();
        $media->uuid = $uuid;
        $media->title = $request->input('title');
        $media->left = $root->right;
        $media->right = $root->right+1;
        $media->status = 'PENDING';
        $media->type = $request->input('type');
        $media->file = $uuid.'.'.$request->media->extension();
        $media->save();

        ProcessImage::dispatch($media);

        return redirect()->action('MediasController@create');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $video = Media::where('uuid', $id)->firstOrFail();

        /*Storage::delete([
            'media/videos/' . $video->uuid . '.mp4',
            'media/thumbnails/' .  $video->uuid . '.png'
        ]);

        $video->delete();
        */
        #return redirect()->action('VideosController@index');
        return 'okay';
    }
}
