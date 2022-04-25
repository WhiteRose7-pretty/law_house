@component('mail::message')
# Potwierdź zmianę adresu email

Kliknij poniższy przycisk by dokonać zatwierdzenia zmiany Twojego emaila

@component('mail::button', ['url' => $url])
Potwierdź nowy adres email
@endcomponent

Miłego Dnia,

{{ config('app.name') }}
@endcomponent
