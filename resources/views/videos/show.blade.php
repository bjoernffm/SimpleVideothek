@extends('layouts.app')

@section('content')
<a href="{{action('VideosController@index')}}">back to overview</a>
<h1>{{$video->title}}</h1>
<div class="col-xs-8" style="overflow: hidden;">
    <video id="videoPlayer" class="video-js" controls preload="auto" width="700" height="350" poster="{{ asset('assets/thumbnails/'.$video->thumbnail) }}" data-setup="{}">
        <source src="{{ asset('assets/videos/'.$video->video) }}" type='video/mp4'>
        <p class="vjs-no-js">
        To view this video please enable JavaScript, and consider upgrading to a web browser that
        <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
        </p>
    </video>
    <div class="row">
        <div class="col-sm-4">
            <a href="{{action('VideosController@edit', [$video->uuid])}}" class="btn btn-default btn-sm btn-block">edit</a>
        </div>
    </div>
</div>
@endsection

@section('javascript')
    var player = videojs('videoPlayer');
    player.thumbnails({
        width: 100,
        spriteUrl: "{{ asset('assets/seek_thumbnails/'.$video->thumbnail) }}",
        stepTime: 15
    });
@endsection
