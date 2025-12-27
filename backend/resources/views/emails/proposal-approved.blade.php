@extends('emails.layout')

@section('title', 'Vorschlag genehmigt')

@section('content')
    <h2>Vorschlag genehmigt</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <div class="success-box">
        <p><strong>Dein Vorschlag wurde genehmigt!</strong></p>
    </div>

    <div class="info-box">
        <p><strong>Art des Vorschlags:</strong> 
        @if($proposalType === 'level')
            Level
        @elseif($proposalType === 'role')
            Rolle
        @elseif($proposalType === 'country')
            Land
        @elseif($proposalType === 'location')
            Standort
        @elseif($proposalType === 'event')
            Veranstaltung
        @else
            {{ $proposalType }}
        @endif
        </p>
        
        <p><strong>Name:</strong> {{ $proposalName }}</p>
        
        @if($proposalDescription)
        <p><strong>Beschreibung:</strong> {{ $proposalDescription }}</p>
        @endif

        @if($proposalType === 'role' && isset($additionalContext['first_program']))
        <p><strong>Programm:</strong> {{ $additionalContext['first_program'] }}</p>
        @endif

        @if($proposalType === 'event' && isset($additionalContext))
        <p><strong>Veranstaltungsdetails:</strong></p>
        <ul style="margin-left: 20px; margin-top: 8px;">
            @if(isset($additionalContext['program']))
            <li>Programm: {{ $additionalContext['program'] }}</li>
            @endif
            @if(isset($additionalContext['season']))
            <li>Saison: {{ $additionalContext['season'] }}</li>
            @endif
            @if(isset($additionalContext['level']))
            <li>Level: {{ $additionalContext['level'] }}</li>
            @endif
            @if(isset($additionalContext['location']))
            <li>Standort: {{ $additionalContext['location'] }}</li>
            @endif
            @if(isset($additionalContext['date']))
            <li>Datum: {{ $additionalContext['date'] }}</li>
            @endif
        </ul>
        @endif

        @if($proposalType === 'location' && isset($additionalContext['country']))
        <p><strong>Land:</strong> {{ $additionalContext['country'] }}</p>
        @endif
    </div>

    <p>Dein Vorschlag ist jetzt für alle Benutzer verfügbar und kann bei der Erfassung von Engagements verwendet werden.</p>
@endsection
