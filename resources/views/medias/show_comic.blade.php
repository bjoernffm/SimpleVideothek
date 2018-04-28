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

<div class="comic_carousel">
    @foreach($children as $child)
    <div>
        <img src="{{ asset('assets/files/'.$child->file) }}" alt="{{ $child->title }}">
    </div>
    @endforeach
</div>
@endsection