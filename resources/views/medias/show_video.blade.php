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
    <div class="col-sm-8" style="overflow: hidden;">
        <video id="videoPlayer" class="video-js" controls preload="auto" style="width: 100%;" height="350" poster="{{ asset('assets/thumbnails/'.$media->thumbnail) }}" data-setup="{}">
            <source src="{{ asset('assets/files/'.$media->file) }}" type='video/mp4'>
            <p class="vjs-no-js">
                To view this video please enable JavaScript, and consider upgrading to a web browser that
                <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
            </p>
        </video>
        <div style="padding: 0 90px 0 85px;">
        <table style="width: 100%;">
            <tr>
                @foreach($chunks as $chunk)
                <td style="background-color: rgb(0,{{($chunk*200)}},0); width: 1%; height: 10px;"></td>
                @endforeach
            </tr>
        </table>
        </div>
    </div>
    <div class="col-sm-4">
        @if ($imdb_details != null)
            <h3>Details</h3>
            <p>{{$imdb_details->Year}} | {{$imdb_details->Genre}}</p>
            <p>{{$imdb_details->Plot}}</p>
            <h4>Cast:</h4>
            <p>{{$imdb_details->Actors}}</p>
        @endif
        <h3>Tags</h3>
        @foreach($media->tags as $tag)
            <a class="btn btn-sm btn-info" href="#">{{$tag->name}}</a>
        @endforeach

        <h3>Recommended</h3>
        <div class="row">
        @foreach($media->recommendedMedia() as $item)
            @if ($loop->index > 2)
                @break
            @endif
            <div class="col-sm-4">
                <a href="{{action('MediasController@show', ['id' => $item->uuid])}}">
                    <div
                        class="lazy"
                        @if($item->status != 'FINISHED')
                            data-src="{{ asset('assets/thumbnails/processing_video.png') }}"
                        @else
                            data-src="{{ asset('assets/thumbnails/'.$item->thumbnail) }}"
                        @endif
                        style="
                            background-position: 50% 50%;
                            background-size: cover;
                            height: 100px;"></div>
                    <div class="title truncate" title="{{$item->title}}">{{$item->title}}</div>
                </a>
            </div>
        @endforeach
        </div>

        <h3>Additional</h3>
        <a class="btn btn-sm btn-primary" href="{{action('MediasController@edit', ['id' => $media->uuid])}}">edit</a>
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

    var loop, start, end;

    player.on('play', function() {
        start = player.currentTime();

        loop = setInterval(function(){
            end = player.currentTime();

            $.ajax({
                url: "{{action('VideoStatisticRecordController@store', ['media' => $media->uuid])}}",
                type: "post",
                data: {user_id: {{ Auth::user()->id }}, from: start, to: end},
                headers: {
                    "accept": "application/json",
                    "X-CSRF-Token": "{{ csrf_token() }}"
                }
            });

            start = player.currentTime();
        }, 10000);
    });

    player.on('pause', function() {
        clearInterval(loop);
    });

    $(".lazy").lazy({
        effect: "fadeIn",
        effectTime: 300
    });
@endsection

