@extends('layouts.app')

@section('content')
@if(count($videos) > 0)
    <p>
        <div class="row">
            @foreach($videos as $video)
            <div class="col-xs-6 col-md-2">
                <div class="mediaItem">
                    <a href="{{action('VideosController@show', ['id' => $video->uuid])}}">
                        <div
                            class="lazy"
                            @if($video->status != 'FINISHED')
                                data-src="{{ asset('assets/thumbnails/processing_video.png') }}"
                            @else
                                data-src="{{ asset('assets/thumbnails/'.$video->thumbnail) }}"
                            @endif
                            style="
                                background-position: 50% 50%;
                                background-size: cover;
                                width: 100%;
                                height: 100px;"></div>
                        <div class="title truncate" title="{{$video->title}}">{{$video->title}}</div>
                        <div class="subtitle"><i class="far fa-clock"></i> {{$video->formatted_length}}</div>
                    </a>
                </div>
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
