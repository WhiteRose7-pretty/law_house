@extends('layouts.base')

@section('head_content')
<meta name="yh-auth-hash-id" content="{{$hash_id}}">
<meta name="environemnt" content="{{App::environment()}}">
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{ mix('/js/admin.js') }}"></script>
<script src="https://kit.fontawesome.com/d13e2d4ad5.js" crossorigin="anonymous"></script>
<!-- Popper.JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
@endsection

@section('body_class','admin')

@section('body_content')
<div id="app-loader" class="app-loader-admin">
    <div class="message">inicjalizacja...</div>
</div>
<div id="app">
    <app></app>
</div>
@endsection

@section('body_last')
@endsection
