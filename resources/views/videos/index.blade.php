@extends('layouts.app')

@section('content')
@if(count($videos) > 0)
    <p>
        <div class="row">
            @foreach($videos as $video)
            <div class="col-xs-6 col-md-2">
                <a href="{{action('VideosController@show', ['id' => $video->uuid])}}" class="thumbnail">
                    <div
                        style="
                            @if($video->status != 'FINISHED')
                            background-image: url({{ asset('media/thumbnails/processing_video.png') }});
                            @else
                            background-image: url({{ asset('media/thumbnails/'.$video->thumbnail) }});
                            @endif
                            background-position: 50% 50%;
                            background-size: cover;
                            width: 100%;
                            height: 100px;"></div>
                    <div style="margin-top: 5px; width: 100%; height: 27px;" class="truncate" title="{{$video->title}}">{{$video->title}}</div>
                    <div style="margin-top: 5px;">{{$video->formatted_length}}</div>
                </a>
            </div>
            @endforeach
        </div>
    </p>
@else
    <p>
        <div class="alert alert-info" role="alert">Sorry, no videos found.</div>
    </p>
@endif
@endsection
