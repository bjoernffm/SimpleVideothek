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
        {{Form::open(['action' => 'MediasController@store', 'files' => true])}}
            <div class="form-group">
                {{Form::label('title', 'Media title:')}}
                {{Form::text('title', '', ['class' => 'form-control'])}}
            </div>
            <div class="form-group">
                {{Form::label('type', 'Type')}}
                {{Form::select('type', [
                    'DIRECTORY' => 'Directory',
                    'COMIC' => 'Comic (Image container)',
                    'IMAGE' => 'Image'
                ], null, ['class' => 'form-control'])}}
            </div>
            <div class="form-group">
                {{Form::label('root', 'Directory')}}
                {{Form::select('root', $directories, null, ['class' => 'form-control'])}}
            </div>
            <div class="form-group">
                {{Form::label('media', 'Media file:')}}
                {{Form::file('media', ['class' => 'form-control'])}}
            </div>
            <div class="form-group">
                {{Form::label('imdb_id', 'IMDb ID:')}}
                {{Form::text('imdb_id', '', ['class' => 'form-control'])}}
            </div>
            <hr />
            {{Form::submit('Create the media', ['class' => 'btn btn-success btn-block'])}}
        {{Form::close()}}
    </div>
</div>

@endsection
