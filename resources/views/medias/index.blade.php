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

@if(count($children) > 0)
<div class="row">
    <div class="col-xs-12">
        @foreach($tags as $tag)
            @if($tag->selected)
                <a class="btn btn-xs btn-success" href="#">{{$tag->name}}</a>
            @else
                <a class="btn btn-xs btn-default" href="{{action('MediasController@show', ['id' => $item->uuid, 'tags' => $tag->urlQuery])}}">{{$tag->name}}</a>
            @endif
        @endforeach
        <a class="btn btn-xs btn-primary" href="{{action('MediasController@show', ['id' => $item->uuid])}}">Reset</a>
    </div>
</div>
@endif

@if(count($children) > 0)
    <p>
        <div class="row">
            @foreach($children as $child)
            <div class="col-xs-6 col-md-2">
                <div class="mediaItem">
                    <a href="{{action('MediasController@show', ['id' => $child->uuid])}}">
                        <div
                            class="lazy"
                            @if($child->status != 'FINISHED')
                                data-src="{{ asset('assets/thumbnails/processing_video.png') }}"
                            @else
                                data-src="{{ asset('assets/thumbnails/'.$child->thumbnail) }}"
                            @endif
                            style="
                                background-position: 50% 50%;
                                background-size: cover;
                                width: 100%;
                                height: 100px;"></div>
                        <div class="title truncate" title="{{$child->title}}">{{$child->title}}</div>
                        <div class="subtitle">
                            @if($child->formatted_length != "")
                            {{$child->formatted_length}} <i class="far fa-clock"></i>
                            @endif
                        </div>
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
