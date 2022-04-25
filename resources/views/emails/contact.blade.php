@component('mail::message')
# {{$subject}}

Wiadomość z formularza kontaktowego na stronie testy.iusvitae.pl

Imię: {{$name}}

Email: {{$email}}

Wiadomość:

{!! $message !!}

Miłego Dnia,

{{ config('app.name') }}
@endcomponent
