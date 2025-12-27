@extends('emails.layout')

@section('title', 'Einladung zu HONR')

@section('content')
    <h2>Einladung zu HONR</h2>

    <p>Hallo,</p>

    <div class="info-box">
        <p><strong>Du wurdest zu HONR eingeladen!</strong></p>
        <p>Ein Administrator hat ein Konto für dich erstellt. Folge den nächsten Schritten, um dein Konto zu aktivieren und loszulegen.</p>
    </div>

    <h3>Nächste Schritte:</h3>
    <ol class="steps">
        <li><strong>E-Mail-Adresse bestätigen:</strong> Klicke auf den Button unten, um deine E-Mail-Adresse zu bestätigen und dein Konto zu aktivieren.</li>
        <li><strong>Erstes Login:</strong> Nach der Bestätigung kannst du dich mit deiner E-Mail-Adresse und dem unten angezeigten Passwort anmelden.</li>
        <li><strong>Einstellungen anpassen:</strong> Beim ersten Login wirst du aufgefordert, dein Passwort zu ändern und deine Einstellungen anzupassen.</li>
    </ol>

    <p style="text-align: center;">
        <a href="{{ $verificationUrl }}" class="button">E-Mail-Adresse bestätigen</a>
    </p>

    <p>Falls der Button nicht funktioniert, kopiere diesen Link in deinen Browser:</p>
    <p class="link">{{ $verificationUrl }}</p>

    <div class="password-box">
        <p style="margin: 0 0 8px 0; font-size: 14px; color: #6b7280; font-weight: normal;">Dein temporäres Passwort für das erste Login:</p>
        <p style="margin: 0; font-family: 'Courier New', monospace; font-size: 20px; letter-spacing: 2px;">{{ $password }}</p>
    </div>

    <p><strong>Wichtig:</strong> Bitte ändere dieses Passwort nach dem ersten Login in den Einstellungen.</p>

    <p>Dieser Bestätigungslink ist 24 Stunden gültig.</p>
@endsection
