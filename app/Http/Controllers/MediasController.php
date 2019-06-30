<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;
use App\Tag;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\DB;
use App\Jobs\ProcessImage;
use App\Jobs\ProcessVideo;
use App\Jobs\UpdateImdbDetails;
use ZipArchive;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Exception\ParseException;
use finfo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;

class MediasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return $this->show('da7fa978-148c-11e8-946f-00012e3bc7c6', new Request());
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $media = Media::where('uuid', $id)->firstOrFail();

        if ($media->type == 'DIRECTORY') {
            if ($request->input('tags') != null) {
                $filterTags = explode(',', $request->input('tags'));

                $cacheKey = $media->uuid.'_'.implode(',', $filterTags);
                $children = Cache::remember('children_'.$cacheKey, 60, function () use ($media, $filterTags) {
                    return $media->getChildren()->filter(function ($value, $key) use ($filterTags) {
                        $tags = $value->tags->map(function ($item, $key) {
                            return $item->id;
                        })->toArray();

                        foreach($filterTags as $filterTag) {
                            if (!in_array($filterTag, $tags)) {
                                return false;
                            }
                        }

                        return true;
                    });
                });
            } else {
                $filterTags = [];
                $children = Cache::remember('children_'.$media->uuid, 60, function () use ($media) {
                    return $media->getChildren();
                });
            }

            $cacheKey = hash('sha256', json_encode($children));
            $tags = Cache::remember('tags_'.$cacheKey, 60, function () use ($children) {
                $tags = [];

                foreach($children as $child) {
                    foreach($child->tags as $tag) {
                        $tags[$tag->id] = $tag;
                    }
                }

                return array_values($tags);
            });

            /**
             * Highlighting the tags when selected and generate url queries
             */
            for($i = 0; $i < count($tags); $i++) {
                if (in_array($tags[$i]->id, $filterTags)) {
                    $tags[$i]->selected = true;
                } else {
                    $tags[$i]->selected = false;
                }

                $futureSelectedTags = $filterTags;
                $futureSelectedTags[] = $tags[$i]->id;
                $tags[$i]->urlQuery = implode(',', $futureSelectedTags);
            }

            return view('medias.index')
                    ->with('media', $media)
                    ->with('children', $children)
                    ->with('tags', $tags);
        } else if ($media->type == 'COMIC') {
            return view('medias.show_comic')
                    ->with('media', $media)
                    ->with('children', $media->getChildren());
        } else {
            $records = DB::table('video_statistic_records')
                ->where('media_id', $media->id)
                ->get();

            $chunks = [];
            for($i = 0; $i < 100; $i++) {
                $chunks[$i] = 0;
            }

            foreach($records as $record) {
                $from = round((($record->from/$media->length)*100));
                $to = round((($record->to/$media->length)*100));

                for($i = $from; $i < $to; $i++) {
                    $chunks[$i]++;
                }
            }

            $max = max($chunks);

            if($max > 0) {
                for($i = 0; $i < 100; $i++) {
                    $chunks[$i] = $chunks[$i]/$max;
                }
            }

            return view('medias.show_video')
                    ->with('media', $media)
                    ->with('chunks', $chunks)
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $media = Media::where('uuid', $id)->firstOrFail();

        if ($media->type == 'DIRECTORY') {
            return view('medias.edit.directory')->with('media', $media);
        } else {
            $tags = Tag::all();

            $selectedTags = $media->tags->map(function ($tag) {
                return $tag->id;
            })->toArray();

            return view('medias.edit.video')
                    ->with('media', $media)
                    ->with('selectedTags', $selectedTags)
                    ->with('tags', $tags);
        }
    }

    /**
     * Update the given user.
     *
     * @param  Request  $request
     * @param  string  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $media = Media::where('uuid', $id)->firstOrFail();

        if ($media->type == 'DIRECTORY') {
            $this->validate($request, [
                'title' => 'required',
                'thumbnail' => 'required'
            ]);

            $original = $request->input('thumbnail');
            $media->thumbnail = $media->uuid.'.png';

            Storage::delete('media/thumbnails/'.$media->thumbnail);
            Storage::copy('media/thumbnails/'.$original, 'media/thumbnails/'.$media->thumbnail);
            $media->save();
        } else if ($media->type == 'VIDEO') {
            $this->validate($request, [
                'title' => 'required'
            ]);

            $media->tags()->sync($request->input('tags'));

            $media->title = $request->input('title');
            $media->save();
        }

        Cache::flush();

        return redirect()->action('MediasController@show', [$media->uuid]);
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
            'media' => 'mimetypes:video/*,image/*,application/zip'
        ]);

        $uuid = Uuid::uuid4()->toString();

        $root = Media::where('uuid', $request->input('root'))->firstOrFail();

        $media = new Media();
        $media->uuid = $uuid;
        $media->title = $request->input('title');
        $media->status = 'PENDING';
        $media->type = $request->input('type');

        if ($media->type == 'DIRECTORY') {
            $media->status = 'FINISHED';
            $root->appendChild($media);
        } else if ($media->type == 'COMIC') {
            if (!$request->file('media')->isValid()) {
                abort(500, 'There was a problem uploading your file');
            }

            if ($request->file('media')->getMimeType() != 'application/zip') {
                abort(400, 'Only zip files are allowed');
            }

            $za = new ZipArchive();
            $za->open($request->media->path());

            $images = [];
            $yamlConfig = null;

            $finfo = new finfo(FILEINFO_MIME);

            for($i = 0; $i < $za->numFiles; $i++) {
                $name = $za->getNameIndex($i);
                $content = $za->getFromIndex($i);

                if ($name == 'content.yaml') {
                    try {
                        $yamlConfig = Yaml::parse($content);
                    } catch (ParseException $exception) {
                        abort(400, 'Unable to parse content.yaml file: '.$exception->getMessage());
                    }
                } else {
                    $mimetype = $finfo->buffer($content);
                    $mimetype = explode('/', $mimetype)[0];

                    if ($mimetype != 'image') {
                        abort(400, 'Only images are allowed: '.$name);
                    }

                    $images[] = [
                        'name' => $name,
                        'content' => $content
                    ];
                }
            }

            // sorting the contents
            usort($images, function($a, $b)
            {
                if ($a['name'] == $b['name']) {
                    return 0;
                }
                return ($a['name'] < $b['name']) ? -1 : 1;
            });

            if ($yamlConfig == null) {
                abort(400, 'File content.yaml is missing');
            }

            if ($yamlConfig['image_count'] != count($images)) {
                abort(400, 'Image count is not equal in zip and content.yaml');
            }

            $root->appendChild($media);

            for($i = 0; $i < count($images); $i++) {
                $extension = explode('.', $images[$i]['name']);
                $extension = $extension[count($extension)-1];

                $image = new Media();
                $image->uuid = Uuid::uuid4()->toString();
                $image->title = sprintf($yamlConfig['image_title'], $i+1);
                $image->status = 'PENDING';
                $image->type = 'IMAGE';
                $image->file = $image->uuid.'.'.$extension;

                $media->appendChild($image);
                Storage::put('pending/'.$image->uuid.'.'.$extension, $images[$i]['content']);
                exec('chmod 777 '.storage_path().'/app/pending/'.$image->uuid.'.'.$extension);
                ProcessImage::dispatch($image);

                // for previewing the first page
                if ($i == 0) {
                    $media->file = $media->uuid.'.'.$extension;
                    Storage::put('pending/'.$media->uuid.'.'.$extension, $images[$i]['content']);
                    exec('chmod 777 '.storage_path().'/app/pending/'.$media->uuid.'.'.$extension);
                    $media->save();
                    ProcessImage::dispatch($media);
                }
            }
        } else if ($media->type == 'VIDEO') {
            if (!$request->file('media')->isValid()) {
                abort(500, 'There was a problem uploading your file');
            }

            $fileType = $request->file('media')->getMimeType();
            $fileType = explode('/', $fileType)[0];

            if ($fileType != 'video') {
                abort(400, 'Only video files are allowed');
            }

            $path = $request->media->storeAs('pending', $uuid.'.'.$request->media->extension());
            $path = storage_path('app/'.$path);
            exec('chmod 777 '.$path);

            $media->file = $uuid.'.'.$request->media->extension();
            if (trim($request->input('imdb_id')) != '') {
                $media->imdb_id = trim($request->input('imdb_id'));
            }

            $root->appendChild($media);

            ProcessVideo::dispatch($media);
            if (trim($request->input('imdb_id')) != '') {
                UpdateImdbDetails::dispatch($media)->onQueue('high');
            }
        } else if ($media->type == 'IMAGE') {
            if (!$request->file('media')->isValid()) {
                abort(500, 'There was a problem uploading your file');
            }

            $fileType = $request->file('media')->getMimeType();
            $fileType = explode('/', $fileType)[0];

            if ($fileType != 'image') {
                abort(400, 'Only image files are allowed');
            }

            $path = $request->media->storeAs('pending', $uuid.'.'.$request->media->extension());
            $path = storage_path('app/'.$path);
            exec('chmod 777 '.$path);

            $media->file = $uuid.'.'.$request->media->extension();
            $root->appendChild($media);

            ProcessImage::dispatch($media);
        }

        Cache::flush();

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
