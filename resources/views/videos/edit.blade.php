@extends('layouts.app')

@section('content')
<a href="{{action('VideosController@index')}}">back to overview</a>
<h1>{{$video->title}}</h1>
<div class="col-xs-8">
    <div class="row">
       <div class="col-sm-4">
            <form action="{{action('VideosController@destroy', [$video->uuid])}}" method="POST">
                {{ csrf_field() }}
                {{ method_field('DELETE') }}
                <button class="btn btn-default btn-sm btn-block">delete</button>
            </form>
        </div>
    </div>
    
</div>
@endsection
