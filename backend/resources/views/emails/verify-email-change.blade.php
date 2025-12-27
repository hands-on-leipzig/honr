@extends('emails.layout')

@section('title', 'Neue E-Mail-Adresse bestätigen')

@section('content')
    <h2>Neue E-Mail-Adresse bestätigen</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <p>du hast eine Änderung deiner E-Mail-Adresse angefordert. Bitte bestätige deine neue E-Mail-Adresse:</p>

    <div class="info-box">
        <strong>Neue E-Mail-Adresse:</strong> {{ $newEmail }}
    </div>

    <p style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">E-Mail-Adresse bestätigen</a>
    </p>

    <p>Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:</p>
    <p class="link">{{ $verificationUrl }}</p>

    <p>Dieser Link ist 24 Stunden gültig.</p>

    <p>Falls du diese Änderung nicht angefordert hast, kannst du diese E-Mail ignorieren. Deine E-Mail-Adresse bleibt unverändert.</p>
@endsection
