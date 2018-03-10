<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Storage;

class MediaController extends Controller
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
     * Returns the media for a given path.
     *
     * @param  string  $type
     * @param  string  $file
     * @return Response
     */
    public function returnMedia($type, $file)
    {
        $path = storage_path().'/app/media/'.$type.'/'.$file;

        if (!is_file($path)) {
            abort(404);
        }

        return response()->file($path);
    }
}
