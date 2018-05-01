@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-xs-12">
        <ol class="breadcrumb">
            @foreach($media->getPath() as $item)
                @if ($loop->last)
                    <li class="active">{{$item->title}}</li>
                @else
                    <li><a href="{{action('MediasController@show', ['id' => $item->uuid])}}">{{$item->title}}</a></li>
                @endif
            @endforeach
        </ol>
    </div>
</div>
<h1>{{$media->title}}</h1>
<hr />
<div class="row">
    <div class="col-sm-8">
        <video id="videoPlayer" class="video-js" controls preload="auto" width="700" height="350" poster="{{ asset('assets/thumbnails/'.$media->thumbnail) }}" data-setup="{}">
            <source src="{{ asset('assets/files/'.$media->file) }}" type='video/mp4'>
            <p class="vjs-no-js">
                To view this video please enable JavaScript, and consider upgrading to a web browser that
                <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
            </p>
        </video>   
    </div>
    <div class="col-sm-4">
        <h3>Details</h3>
        @if ($imdb_details == null)
            <p>No additional information available</p>
        @else
            <p>{{$imdb_details->Year}} | {{$imdb_details->Genre}}</p>
            <p>{{$imdb_details->Plot}}</p>
            <h4>Cast:</h4>
            <p>{{$imdb_details->Actors}}</p>
        @endif
    </div>
</div>    
@endsection

@section('javascript')
    var player = videojs('videoPlayer');
    player.thumbnails({
        width: 100,
        spriteUrl: "{{ asset('assets/seek_thumbnails/'.$media->thumbnail) }}",
        stepTime: 15
    });
@endsection

