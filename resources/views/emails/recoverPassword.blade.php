@component('mail::message')
# Hola {{$user->name}}

Te hemos enviado este correo por que has solicitado la recuperación de contraseña de tu cuenta.

@component('mail::button', ['url' => route('recover', $passRecover->token_pass)])
Recuperar mi cuenta
@endcomponent

Gracias,<br>
{{ config('app.name') }}

<a href={{route('recover', $passRecover->token_pass)}}> Pulsa aqui, si tienes problemas.</a>
@endcomponent