@extends('emails.layout')

@section('title', 'Passwort zurücksetzen')

@section('content')
    <h2>Passwort zurücksetzen</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <p>du hast eine Passwort-Zurücksetzung für dein HONR-Konto angefordert.</p>

    <p style="text-align: center;">
        <a href="{{ $resetUrl }}" class="button">Passwort zurücksetzen</a>
    </p>

    <p>Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:</p>
    <p class="link">{{ $resetUrl }}</p>

    <div class="warning-box">
        <strong>Wichtig:</strong> Dieser Link ist 24 Stunden gültig. Falls du keine Passwort-Zurücksetzung angefordert hast, kannst du diese E-Mail ignorieren.
    </div>
@endsection
