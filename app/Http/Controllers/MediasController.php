<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;
use Ramsey\Uuid\Uuid;

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

        if ($media->type == 'CATEGORY') {
            return view('medias.index')
                    ->with('media', $media)
                    ->with('children', $media->getChildren());
        } else {
            return view('medias.show_video')
                    ->with('media', $media);
            #return $media->getPath();
        }
    }
}
