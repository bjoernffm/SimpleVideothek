<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;
use Ramsey\Uuid\Uuid;
use App\Jobs\UpdateImdbDetails;

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
}
