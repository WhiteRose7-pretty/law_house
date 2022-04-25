@component('mail::message')
# {{$subject}}

{!! $message !!}

Miłego Dnia,

{{ config('app.name') }}
@endcomponent
