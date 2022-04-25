@extends('layouts.front')

@section('body_class','welcome')

@section('body_content')

@include('layouts.parts.front-header',['bg' => true])

<div class="container ius">
    <h1 class="mb-4">
        Aktualności
    </h1>

    @forelse($list as $c)
    <a href="{{route('news',['title_uri'=>$c->title_uri])}}" style="color:inherit;">
        <div class="row">
            <div class="col-4 pb-4">
                @if(!empty($c->getFirstMedia()))
                <img src="{{$c->getFirstMedia()->getUrl('thumb')}}" class="w-100" alt="{{$c->title}}">
                @endif
            </div>
            <div class="col-8">
                <div class="yh-fs-16 yh-v-o-50">{{date('Y/m/d',strtotime($c->published_at))}}</div>
                <h2>{{$c->title}}</h2>
                <p>{{$c->lead}}</p>
            </div>
        </div>
    </a>
    @empty
    lista aktualności jest pusta
    @endforelse

    {{-- @if($content->category=='news')
    <div class="mb-1 yh-fs-18 yh-fw-5 yh-v-o-50">
        @if(!empty($content->published_at))

        @else
            {{date('Y/m/d')}}
        @endif
    </div>
    @endif
    <h1 class="mb-4">
        {{$content->title}}
    </h1>
    @if(!empty($image))
    <img src="{{$image}}" class="w-100 mb-5" alt="{{$content->title}}">
    @endif
    <div>
        {!!$content->full!!}
    </div>
    @if(empty($content->published_at))
    <div class="text-center mt-5">
        <a href="{{route('publish',['id'=>$content->id])}}" class="btn btn-lg btn-success">Opublikuj</a>
    </div>
    @endif --}}
    <div class="yh-gap-10"></div>
</div>

@include('layouts.parts.front-footer')

@endsection
