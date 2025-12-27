@extends('emails.layout')

@section('title', 'Badge erreicht')

@section('content')
    <h2>Badge erreicht!</h2>

    <p>Hallo {{ $user->nickname }},</p>

    <div class="success-box">
        <p><strong>Herzlichen Glückwunsch! Du hast ein neues Badge-Level erreicht.</strong></p>
    </div>

    <div class="badge-info">
        @if($logoPath)
        <img src="{{ config('app.url') }}/storage/{{ $logoPath }}" alt="{{ $roleName }}" style="max-width: 80px; height: auto; margin: 10px 0;">
        @endif
        <p><strong>{{ $roleName }}</strong></p>
        <div class="badge-level level-{{ $newLevel }}">
            @if($newLevel === 1)
                Basis
            @elseif($newLevel === 2)
                Bronze
            @elseif($newLevel === 3)
                Silber
            @elseif($newLevel === 4)
                Gold
            @else
                Level {{ $newLevel }}
            @endif
        </div>
        <p style="color: #6b7280; font-size: 14px;">{{ $engagementCount }} anerkannte Einsätze</p>
    </div>

    <div class="info-box">
        <p>Du hast jetzt <strong>{{ $engagementCount }}</strong> anerkannte Einsätze in der Rolle <strong>{{ $roleName }}</strong>.</p>
        @if($newLevel < 4)
        <p style="margin-top: 12px;">
            @if($newLevel === 1)
                Nächstes Level: Bronze (5 Einsätze)
            @elseif($newLevel === 2)
                Nächstes Level: Silber (20 Einsätze)
            @elseif($newLevel === 3)
                Nächstes Level: Gold (50 Einsätze)
            @endif
        </p>
        @endif
    </div>

    <p>Weiter so! Dein Engagement wird geschätzt.</p>
@endsection
