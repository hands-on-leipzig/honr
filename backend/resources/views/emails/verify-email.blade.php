@extends('emails.layout')

@section('title', 'E-Mail-Adresse bestätigen')

@section('content')
    <h2>E-Mail-Adresse bestätigen</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <p>vielen Dank für deine Registrierung bei HONR! Bitte bestätige deine E-Mail-Adresse, um dein Konto zu aktivieren.</p>

    <p style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">E-Mail-Adresse bestätigen</a>
    </p>

    <p>Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:</p>
    <p class="link">{{ $verificationUrl }}</p>

    <p>Dieser Link ist 24 Stunden gültig.</p>

    <p>Falls du dich nicht registriert hast, kannst du diese E-Mail ignorieren.</p>
@endsection
