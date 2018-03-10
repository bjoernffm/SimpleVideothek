@extends('layouts.app')

@section('content')
@if(count($children) > 0)
    <p>
        <div class="row">
            @foreach($children as $child)
            <div class="col-xs-6 col-md-2">
                <a href="{{action('MediasController@show', ['id' => $child->uuid])}}" class="thumbnail">
                    <div
                        style="
                            @if($child->status != 'FINISHED')
                            background-image: url({{ asset('assets/thumbnails/processing_video.png') }});
                            @else
                            background-image: url({{ asset('assets/thumbnails/'.$child->thumbnail) }});
                            @endif
                            background-position: 50% 50%;
                            background-size: cover;
                            width: 100%;
                            height: 100px;"></div>
                    <div style="margin-top: 5px; width: 100%; height: 27px;" class="truncate"
                    title="{{$child->title}}">{{$child->title}}</div>
                    <div style="margin-top: 5px;">{{$child->formatted_length}}</div>
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
