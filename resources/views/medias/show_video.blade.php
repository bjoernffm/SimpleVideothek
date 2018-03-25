@extends('layouts.app')

@section('content')

<h1>{{$media->title}}</h1>
<ol class="breadcrumb">
@foreach($media->getPath() as $item)
    @if ($loop->last)
        <li class="active">{{$item->title}}</li>
    @else
        <li><a href="{{action('MediasController@show', ['id' => $item->uuid])}}">{{$item->title}}</a></li>
    @endif
@endforeach
</ol>
<div class="col-xs-8">
    <video id="my-video" class="video-js" controls preload="auto" width="700" height="350" poster="{{ asset('assets/thumbnails/'.$media->thumbnail) }}" data-setup="{}">
        <source src="{{ asset('assets/videos/'.$media->file) }}" type='video/mp4'>
        <p class="vjs-no-js">
        To view this video please enable JavaScript, and consider upgrading to a web browser that
        <a href="http://videojs.com/html5-video-support/" target="_blank">supports HTML5 video</a>
        </p>
    </video>   
</div>
@endsection
