@component('mail::message')

Potwierdzenie wysyłania wiadomości do testy.iusvitae.pl

Wiadomość: {!! $message !!}

Twoja wiadomość została prawidłowo dostarczona do administratora.

Miłego dnia,

{{ config('app.name') }}
@endcomponent
