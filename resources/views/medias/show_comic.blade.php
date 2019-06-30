@extends('layouts.app')

@section('content')

<div style="background-color: #fff; border: 1px #d3e0e9 solid; bottom: 30px; right: 30px; padding: 5px; position: fixed; z-index: 50;">
    <span id="topSpan"></span>
</div>

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

@foreach($children as $child)
<div class="row">
    <div class="col-xs-3"></div>
    <div class="col-xs-6 images">
        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="{{ asset('assets/files/'.$child->file) }}" alt="{{ $child->title }}" class="lazy" style="width: 100%;" />
    </div>
</div>
<br />
@endforeach
@endsection

@section('javascript')
let images = [];

let instance = $(".lazy").Lazy({
    visibleOnly: false,
    enableThrottle: true,
    throttle: 250,
    chainable: false,
    afterLoad: function(element) {
        images = [];
        $('.images img').each(function(){
            let img = $(this);
            images.push({
                "top": img.offset().top,
                "element": img
            });
        });
        getCurrentPage();
        $('#topSpan').text("Page "+data.currentPage+" of "+data.totalPages);
    },
    onFinishedAll: function() {
        //console.log('onFinishedAll');
    }
});
instance.loadAll();

let data = {
    currentPage: 1,
    totalPages: images.length
}

let getCurrentPage = function() {
    let pos = $(window).scrollTop();
    let image;

    for(let i = 0; i < images.length; i++) {
        if (pos < images[i].top-50 && i == 0) {
            image = 1;
            break;
        } else if (pos < images[i].top-50) {
            image = i;
            break;
        } else if (pos > images[images.length-1].top-50) {
            image = images.length;
            break;
        }
    }

    data.currentPage = image;
    data.totalPages = images.length;
}

$(window).scroll(function() {
    getCurrentPage();
    $('#topSpan').text("Page "+data.currentPage+" of "+data.totalPages);
});

$(window).keydown(function(e) {
    if (e.keyCode == 39) {
        if (data.currentPage == data.totalPages) {
            return;
        }

        data.currentPage++;
        $(window).scrollTop( images[data.currentPage-1].top-30 );
    } else if (e.keyCode == 37) {
        if (data.currentPage == 1) {
            return;
        }

        data.currentPage--;
        $(window).scrollTop( images[data.currentPage-1].top-30 );
    }
});
@endsection