@component('mail::message')
# Hola {{$user->name}}

Gracias por registrarte en la aplicación, por favor confirma tu registro pulsado el siguiente boton.

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Confirmar Registro
@endcomponent

Gracias,<br>
{{ config('app.name') }}

<a href={{route('verify', $user->verification_token)}}> Pulsa aqui, si tienes problemas.</a>
@endcomponent