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
    <div class="col-sm-8">
        <h1>Select Thumbnail</h1>
        @if(count($media->getChildren()) > 0)
            <p>
                <div class="row">
                    @foreach($media->getChildren() as $child)
                    <div class="col-xs-6 col-md-2">
                        <div class="mediaItem thumbnailItem" data_thumbnail="{{$child->thumbnail}}" style="height: 70px; cursor: pointer;">
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
                                    height: 70px;"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </p>
        @else
            <p>
                <div class="alert alert-info" role="alert">Sorry, no media found.</div>
            </p>
        @endif
    </div>
    <div class="col-sm-4">
        <h1>Details</h1>
        <dl>
            <dt>Created</dt>
            <dd style="margin-bottom: .5rem">{{$media->created_at}}</dd>
            <dt>Last update</dt>
            <dd>{{$media->updated_at}}</dd>
        </dl>
        <h1>Directory</h1>
        {{Form::open(['action' => ['MediasController@update', $media->uuid], 'files' => true, 'method' => 'put'])}}
            <div class="form-group">
                {{Form::label('title', 'Media title:')}}
                {{Form::text('title', $media->title, ['class' => 'form-control'])}}
            </div>
            <div class="form-group">
                {{Form::label('thumbnail', 'Media thumbnail:')}}
                <img id="thumbPreview" src="{{asset('assets/thumbnails')}}/884b962b-96a1-4112-80fe-0d83fb8d2648.png" class="img-thumbnail" style="width: 100%;">
                {{Form::hidden('thumbnail', $media->thumbnail, ['class' => 'form-control'])}}
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

