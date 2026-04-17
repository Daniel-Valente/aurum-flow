@extends('layouts.auth')

@section('content')

<x-auth-header>
    Cambiar contraseña
</x-auth-header>

<x-layouts.auth.card>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="password" name="password" placeholder="Nueva contraseña">
        <input type="password" name="password_confirmation" placeholder="Confirmar contraseña">

        <button type="submit">Guardar</button>
    </form>

</x-layouts.auth.card>

@endsection
