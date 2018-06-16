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
<hr />

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
<div class="row">
    <div class="col-sm-3">
        <h1>Details</h1>
        <dl>
            <dt>Created</dt>
            <dd style="margin-bottom: .5rem">{{$media->created_at}}</dd>
            <dt>Last update</dt>
            <dd>{{$media->updated_at}}</dd>
            <dt>Thumbnail</dt>
            <dd><img id="thumbPreview" src="{{asset('assets/thumbnails')}}/{{$media->thumbnail}}" class="img-thumbnail" style="width: 100%;"></dd>
        </dl>
    </div>
    <div class="col-sm-9">
        <h1>Media</h1>
        {{Form::open(['action' => ['MediasController@update', $media->uuid], 'files' => true, 'method' => 'put'])}}
            <div class="form-group">
                {{Form::label('title', 'Media title:')}}
                {{Form::text('title', $media->title, ['class' => 'form-control'])}}
            </div>
            <div class="form-group">
                {{Form::label('tags[]', 'Tags:')}}
                @foreach ($tags as $tag)
                    <div>
                        {{$tag->name}} {{Form::checkbox('tags[]', $tag->id, in_array($tag->id, $selectedTags))}}
                    </div>
                @endforeach
            </div>
        {{Form::submit('Update', ['class' => 'btn btn-success btn-block'])}}
        {{Form::close()}}
    </div>
</div>
@endsection

@section('javascript')
    $('.thumbnailItem').click(function() {
        el = $(this);
        console.log(el.attr('data_thumbnail'));
        $('#thumbnail').val(el.attr('data_thumbnail'));
        $('#thumbPreview').attr('src', '{{asset('assets/thumbnails')}}/'+el.attr('data_thumbnail'));
    });
@endsection

