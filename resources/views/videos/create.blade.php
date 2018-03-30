@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-xs-3"></div>
    <div class="col-xs-6">
        
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        {{Form::open(['action' => 'VideosController@store', 'files' => true])}}
        
            <div class="form-group">
                {{Form::label('title', 'Video title:')}}
                {{Form::text('title', '', ['class' => 'form-control'])}}
            </div>
            
            <div class="form-group">
                {{Form::label('video', 'Video file:')}}
                {{Form::file('video', ['class' => 'form-control'])}}
            </div>
        
        {{Form::submit('Upload the video', ['class' => 'btn btn-success btn-block'])}}
    </div>
</div>

@endsection
