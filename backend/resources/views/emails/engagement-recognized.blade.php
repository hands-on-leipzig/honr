@extends('emails.layout')

@section('title', 'Einsatz anerkannt')

@section('content')
    <h2>Einsatz anerkannt</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <div class="success-box">
        <p><strong>Dein Einsatz wurde anerkannt!</strong></p>
    </div>

    <div class="info-box">
        <p><strong>Rolle:</strong> {{ $engagement->role->name }}</p>
        @if($engagement->role->firstProgram)
        <p><strong>Programm:</strong> {{ $engagement->role->firstProgram->name }}</p>
        @endif
        
        <p><strong>Veranstaltung:</strong></p>
        <ul style="margin-left: 20px; margin-top: 8px;">
            <li>Datum: {{ \Carbon\Carbon::parse($engagement->event->date)->format('d.m.Y') }}</li>
            @if($engagement->event->level)
            <li>Level: {{ $engagement->event->level->name }}</li>
            @endif
            @if($engagement->event->location)
            <li>Standort: {{ $engagement->event->location->name }}{{ $engagement->event->location->city ? ', ' . $engagement->event->location->city : '' }}</li>
            @endif
            @if($engagement->event->season)
            <li>Saison: {{ $engagement->event->season->name }}</li>
            @endif
        </ul>
    </div>

    <p>Dein Einsatz zählt jetzt zu deinen anerkannten Engagements und wird bei der Berechnung deiner Badges berücksichtigt.</p>
@endsection
