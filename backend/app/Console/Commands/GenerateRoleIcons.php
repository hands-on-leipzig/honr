<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateRoleIcons extends Command
{
    protected $signature = 'roles:generate-icons {--update-descriptions : Update role descriptions} {--generate-svgs : Generate and store SVG icons}';
    protected $description = 'Generate German descriptions and SVG icons for all roles';

    // FIRST LEGO League role descriptions (German)
    private $roleDescriptions = [
        'Moderator:in' => 'Moderiert Veranstaltungen und führt durch das Programm bei FIRST LEGO League Events.',
        'Coach:in Challenge' => 'Trainiert und betreut ein FIRST LEGO League Challenge Team bei der Vorbereitung auf Wettbewerbe.',
        'Juror:in' => 'Bewertet Teams in verschiedenen Kategorien während der FIRST LEGO League Wettbewerbe.',
        'Juror:in Roboter-Design' => 'Bewertet die technische Umsetzung und das Design der Roboter der Teams.',
        'Juror:in Grundwerte' => 'Bewertet, wie Teams die FIRST LEGO League Grundwerte wie Entdeckung, Innovation und Teamwork leben.',
        'Juror:in Teamwork' => 'Bewertet die Zusammenarbeit und Kommunikation innerhalb der Teams.',
        'Juror:in Forschung' => 'Bewertet die Forschungsprojekte der Teams zu aktuellen gesellschaftlichen Themen.',
        'Schiedsrichter:in' => 'Überwacht und bewertet die Roboter-Performance der Teams auf dem Spielfeld.',
        'Robot-Checker:in' => 'Prüft die Roboter der Teams vor dem Wettbewerb auf Regelkonformität.',
        'Live Challenge Juror' => 'Bewertet Teams während der Live Challenge Events bei FIRST LEGO League.',
        'Jury-Leiter:in' => 'Leitet und koordiniert die Jury-Arbeit bei FIRST LEGO League Wettbewerben.',
        'Ober-Schiedsrichter:in' => 'Leitet das Schiedsrichter-Team und entscheidet bei strittigen Fällen.',
        'Live Challenge Leiter:in' => 'Organisiert und leitet Live Challenge Events bei FIRST LEGO League.',
        'Regional-Partner:in Challenge' => 'Organisiert und unterstützt FIRST LEGO League Challenge Events in einer Region.',
        'Gutachter:in' => 'Bewertet und prüft Teams bei FIRST LEGO League Explore Veranstaltungen.',
        'Coach:in Explore' => 'Trainiert und betreut ein FIRST LEGO League Explore Team bei der Vorbereitung auf Ausstellungen.',
        'Coach:in' => 'Trainiert und betreut ein FIRST LEGO League Team bei der Vorbereitung auf Wettbewerbe.',
        'Juror:in "SAP Sonderpreis für beste Programmierung"' => 'Bewertet Teams für den SAP Sonderpreis für die beste Programmierung des Roboters.',
    ];

    public function handle()
    {
        $roles = Role::all();

        if ($this->option('update-descriptions')) {
            $this->info('Updating role descriptions...');
            $this->updateDescriptions($roles);
        }

        if ($this->option('generate-svgs')) {
            $this->info('Generating SVG icons...');
            $this->generateSvgs($roles);
        }

        if (!$this->option('update-descriptions') && !$this->option('generate-svgs')) {
            $this->error('Please specify --update-descriptions and/or --generate-svgs');
            return 1;
        }

        $this->info('Done!');
        return 0;
    }

    private function updateDescriptions($roles)
    {
        foreach ($roles as $role) {
            if (isset($this->roleDescriptions[$role->name])) {
                $role->update(['description' => $this->roleDescriptions[$role->name]]);
                $this->line("Updated description for: {$role->name}");
            } else {
                $this->warn("No description found for: {$role->name}");
            }
        }
    }

    private function generateSvgs($roles)
    {
        // Ensure directory exists
        Storage::disk('public')->makeDirectory('logos/roles');

        foreach ($roles as $role) {
            $svg = $this->generateSvgForRole($role);
            
            if ($svg) {
                $filename = $role->id . '.svg';
                $path = 'logos/roles/' . $filename;
                
                Storage::disk('public')->put($path, $svg);
                $role->update(['logo_path' => $path]);
                
                $this->info("Generated SVG for: {$role->name} (ID: {$role->id})");
            } else {
                $this->warn("Failed to generate SVG for: {$role->name}");
            }
        }
    }

    private function generateSvgForRole(Role $role): ?string
    {
        // Generate SVG based on role name and description
        $iconData = $this->getIconForRole($role->name);
        
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">
  <defs>
    <style>
      .icon-stroke { fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
      .icon-fill { stroke: none; }
      .icon-light { opacity: 0.3; }
    </style>
  </defs>
  {$iconData}
</svg>
SVG;

        return $svg;
    }

    private function getIconForRole(string $roleName): string
    {
        // Map role names to colorful, distinctive SVG icons
        // Icons use colors and detailed shapes for immediate recognition
        $icons = [
            // Moderator:in - Colorful microphone (Red/Orange for communication)
            'Moderator:in' => '<defs><linearGradient id="micGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#EF4444;stop-opacity:1" /><stop offset="100%" style="stop-color:#F97316;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#micGrad)"><rect x="26" y="10" width="12" height="20" rx="2"/><ellipse cx="32" cy="30" rx="10" ry="6"/></g><g class="icon-fill" fill="#ffffff" opacity="0.3"><rect x="28" y="14" width="8" height="2" rx="0.5"/><rect x="28" y="18" width="8" height="2" rx="0.5"/><rect x="28" y="22" width="8" height="2" rx="0.5"/></g><g class="icon-fill" fill="#DC2626"><rect x="30" y="36" width="4" height="8" rx="1"/></g><g class="icon-stroke" stroke="#DC2626" stroke-width="2"><path d="M 24 44 L 24 50 M 28 44 L 28 50 M 36 44 L 36 50 M 40 44 L 40 50"/></g>',
            
            // Coach:in - Person with clipboard (Blue for guidance/teaching)
            'Coach:in' => '<defs><linearGradient id="coachGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" /><stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#coachGrad)"><circle cx="32" cy="18" r="7"/><path d="M 18 36 Q 18 30 32 30 Q 46 30 46 36 L 46 50 L 18 50 Z"/></g><g class="icon-fill" fill="#ffffff" opacity="0.2"><circle cx="32" cy="18" r="4"/></g><g class="icon-fill" fill="#1E3A8A"><rect x="48" y="22" width="10" height="12" rx="1.5"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="1.5"><path d="M 50 25 L 56 25"/></g>',
            
            // Coach:in Challenge - Coach with Challenge badge
            'Coach:in Challenge' => '<defs><linearGradient id="coachChalGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" /><stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#coachChalGrad)"><circle cx="32" cy="18" r="7"/><path d="M 18 36 Q 18 30 32 30 Q 46 30 46 36 L 46 50 L 18 50 Z"/></g><g class="icon-fill" fill="#ffffff" opacity="0.2"><circle cx="32" cy="18" r="4"/></g><g class="icon-fill" fill="#DC2626"><rect x="48" y="22" width="10" height="12" rx="1.5"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="1.5"><path d="M 50 25 L 56 25 M 53 25 L 53 30"/></g><g class="icon-fill" fill="#ffffff"><rect x="51" y="27" width="2" height="2"/></g>',
            
            // Coach:in Explore - Coach with Explore circle
            'Coach:in Explore' => '<defs><linearGradient id="coachExpGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" /><stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#coachExpGrad)"><circle cx="32" cy="18" r="7"/><path d="M 18 36 Q 18 30 32 30 Q 46 30 46 36 L 46 50 L 18 50 Z"/></g><g class="icon-fill" fill="#ffffff" opacity="0.2"><circle cx="32" cy="18" r="4"/></g><g class="icon-stroke" stroke="#10B981" stroke-width="2.5"><circle cx="52" cy="28" r="6"/><circle cx="52" cy="28" r="4"/></g><g class="icon-fill" fill="#10B981" opacity="0.2"><circle cx="52" cy="28" r="3"/></g>',
            
            // Juror:in - Clipboard with checkmark (Green for evaluation)
            'Juror:in' => '<defs><linearGradient id="jurorGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#jurorGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M 26 32 L 30 36 L 38 26"/></g>',
            
            // Juror:in Roboter-Design - Clipboard with robot (Teal for technical)
            'Juror:in Roboter-Design' => '<defs><linearGradient id="jurorRobGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#jurorRobGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#1F2937"><rect x="24" y="24" width="16" height="12" rx="1.5"/></g><g class="icon-fill" fill="#3B82F6"><circle cx="30" cy="30" r="2.5"/><circle cx="34" cy="30" r="2.5"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 26 36 L 38 36"/></g>',
            
            // Juror:in Grundwerte - Clipboard with heart/values symbol (Pink for values)
            'Juror:in Grundwerte' => '<defs><linearGradient id="jurorGradGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#jurorGradGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#EC4899"><path d="M 32 26 C 30 24 28 26 28 28 C 28 30 32 34 32 34 C 32 34 36 30 36 28 C 36 26 34 24 32 26 Z"/></g>',
            
            // Juror:in Teamwork - Clipboard with people/team symbol (Purple for collaboration)
            'Juror:in Teamwork' => '<defs><linearGradient id="jurorTeamGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#jurorTeamGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#8B5CF6"><circle cx="26" cy="26" r="4"/><circle cx="38" cy="26" r="4"/></g><g class="icon-fill" fill="#6D28D9"><path d="M 24 32 Q 32 38 40 32" stroke="#6D28D9" stroke-width="2" fill="none"/></g><g class="icon-fill" fill="#A78BFA" opacity="0.5"><path d="M 24 32 Q 32 38 40 32"/></g>',
            
            // Juror:in Forschung - Clipboard with lightbulb/research symbol (Yellow for research)
            'Juror:in Forschung' => '<defs><linearGradient id="jurorForschGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#jurorForschGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#FBBF24"><path d="M 32 22 C 28 22 26 24 26 28 C 26 30 28 32 30 32 L 34 32 C 36 32 38 30 38 28 C 38 24 36 22 32 22 Z"/></g><g class="icon-fill" fill="#F59E0B"><rect x="31" y="32" width="2" height="6" rx="1"/></g><g class="icon-stroke" stroke="#92400E" stroke-width="1.5"><path d="M 30 38 L 34 38"/></g>',
            
            // Juror:in SAP Sonderpreis - Clipboard with code/programming symbol (Orange for tech)
            'Juror:in "SAP Sonderpreis für beste Programmierung"' => '<defs><linearGradient id="jurorSAPGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#jurorSAPGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#F97316"><rect x="24" y="26" width="6" height="6" rx="0.5"/><rect x="34" y="26" width="6" height="6" rx="0.5"/><rect x="24" y="34" width="6" height="6" rx="0.5"/><rect x="34" y="34" width="6" height="6" rx="0.5"/></g>',
            
            // Schiedsrichter:in - Whistle icon (Yellow/Orange for referee)
            'Schiedsrichter:in' => '<defs><linearGradient id="refGrad" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" style="stop-color:#FBBF24;stop-opacity:1" /><stop offset="100%" style="stop-color:#F59E0B;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#refGrad)"><rect x="22" y="18" width="16" height="22" rx="2.5"/></g><g class="icon-fill" fill="#ffffff" opacity="0.3"><rect x="24" y="22" width="12" height="2" rx="0.5"/><rect x="24" y="26" width="12" height="2" rx="0.5"/></g><g class="icon-stroke" stroke="#92400E" stroke-width="2.5"><circle cx="44" cy="28" r="5"/><path d="M 49 28 L 54 28"/></g><g class="icon-fill" fill="#DC2626"><circle cx="26" cy="22" r="2.5"/></g>',
            
            // Ober-Schiedsrichter:in - Whistle with star/crown
            'Ober-Schiedsrichter:in' => '<defs><linearGradient id="refOberGrad" x1="0%" y1="0%" x2="100%" y2="0%"><stop offset="0%" style="stop-color:#FBBF24;stop-opacity:1" /><stop offset="100%" style="stop-color:#F59E0B;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#refOberGrad)"><rect x="22" y="18" width="16" height="22" rx="2.5"/></g><g class="icon-fill" fill="#ffffff" opacity="0.3"><rect x="24" y="22" width="12" height="2" rx="0.5"/><rect x="24" y="26" width="12" height="2" rx="0.5"/></g><g class="icon-stroke" stroke="#92400E" stroke-width="2.5"><circle cx="44" cy="28" r="5"/><path d="M 49 28 L 54 28"/></g><g class="icon-fill" fill="#DC2626"><circle cx="26" cy="22" r="2.5"/></g><g class="icon-fill" fill="#FCD34D"><path d="M 32 12 L 33 16 L 37 16 L 34 18 L 35 22 L 32 20 L 29 22 L 30 18 L 27 16 L 31 16 Z"/></g>',
            
            // Robot-Checker:in - Magnifying glass with robot (Purple for technical)
            'Robot-Checker:in' => '<defs><linearGradient id="checkGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#8B5CF6;stop-opacity:1" /><stop offset="100%" style="stop-color:#6D28D9;stop-opacity:1" /></linearGradient></defs><g class="icon-stroke" stroke="url(#checkGrad)" stroke-width="3"><circle cx="36" cy="26" r="9"/><path d="M 43 33 L 50 40"/></g><g class="icon-fill" fill="#1F2937"><rect x="26" y="22" width="10" height="8" rx="1"/></g><g class="icon-fill" fill="#3B82F6"><circle cx="30" cy="26" r="2"/><circle cx="32" cy="26" r="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="1.5"><path d="M 28 29 L 34 29"/></g>',
            
            // Live Challenge Juror - Clipboard with live/streaming symbol (Red for live)
            'Live Challenge Juror' => '<defs><linearGradient id="liveJurorGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#liveJurorGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#DC2626"><circle cx="32" cy="28" r="7"/><circle cx="32" cy="28" r="4" fill="#ffffff"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 28 24 L 28 32 M 36 24 L 36 32"/></g>',
            
            // Live Challenge Leiter:in - Person with live/streaming symbol (Blue + Red)
            'Live Challenge Leiter:in' => '<defs><linearGradient id="liveLeitGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#3B82F6;stop-opacity:1" /><stop offset="100%" style="stop-color:#1E40AF;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#liveLeitGrad)"><circle cx="32" cy="18" r="7"/><path d="M 18 36 Q 18 30 32 30 Q 46 30 46 36 L 46 50 L 18 50 Z"/></g><g class="icon-fill" fill="#DC2626"><circle cx="52" cy="28" r="6"/><circle cx="52" cy="28" r="3.5" fill="#ffffff"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 49 26 L 49 30 M 55 26 L 55 30"/></g>',
            
            // Jury-Leiter:in - Clipboard with star/leader symbol (Gold for leadership)
            'Jury-Leiter:in' => '<defs><linearGradient id="juryLeitGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#juryLeitGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-fill" fill="#FCD34D"><path d="M 32 22 L 33 26 L 37 26 L 34 28 L 35 32 L 32 30 L 29 32 L 30 28 L 27 26 L 31 26 Z"/></g>',
            
            // Regional-Partner:in Challenge - Map pin with Challenge badge (Teal for organization)
            'Regional-Partner:in Challenge' => '<defs><linearGradient id="regGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#14B8A6;stop-opacity:1" /><stop offset="100%" style="stop-color:#0D9488;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#regGrad)"><path d="M 32 12 L 42 24 L 38 24 L 38 44 L 26 44 L 26 24 L 22 24 Z"/></g><g class="icon-fill" fill="#ffffff" opacity="0.3"><path d="M 30 20 L 34 20 L 34 24 L 30 24 Z"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><circle cx="32" cy="50" r="5"/><path d="M 30 48 L 34 48 M 32 46 L 32 50"/></g><g class="icon-fill" fill="#DC2626"><rect x="30" y="32" width="4" height="2.5" rx="0.5"/></g>',
            
            // Gutachter:in - Clipboard with magnifying glass (Indigo for assessment)
            'Gutachter:in' => '<defs><linearGradient id="gutGrad" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:#10B981;stop-opacity:1" /><stop offset="100%" style="stop-color:#059669;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#gutGrad)"><path d="M 18 14 L 46 14 L 46 50 L 18 50 Z" rx="2"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 22 18 L 22 46 M 42 18 L 42 46"/></g><g class="icon-stroke" stroke="#4F46E5" stroke-width="3"><circle cx="28" cy="28" r="5"/><path d="M 32 32 L 36 36"/></g>',
        ];

        // Try exact match first
        if (isset($icons[$roleName])) {
            return $icons[$roleName];
        }

        // Try partial matches with improved fallbacks
        if (str_contains($roleName, 'Coach')) {
            if (str_contains($roleName, 'Challenge')) {
                return $icons['Coach:in Challenge'];
            }
            if (str_contains($roleName, 'Explore')) {
                return $icons['Coach:in Explore'];
            }
            return $icons['Coach:in'];
        }
        if (str_contains($roleName, 'Juror')) {
            if (str_contains($roleName, 'Roboter-Design')) {
                return $icons['Juror:in Roboter-Design'];
            }
            if (str_contains($roleName, 'Grundwerte')) {
                return $icons['Juror:in Grundwerte'];
            }
            if (str_contains($roleName, 'Teamwork')) {
                return $icons['Juror:in Teamwork'];
            }
            if (str_contains($roleName, 'Forschung')) {
                return $icons['Juror:in Forschung'];
            }
            if (str_contains($roleName, 'SAP')) {
                return $icons['Juror:in "SAP Sonderpreis für beste Programmierung"'];
            }
            if (str_contains($roleName, 'Live Challenge')) {
                return $icons['Live Challenge Juror'];
            }
            return $icons['Juror:in'];
        }
        if (str_contains($roleName, 'Schiedsrichter')) {
            if (str_contains($roleName, 'Ober-')) {
                return $icons['Ober-Schiedsrichter:in'];
            }
            return $icons['Schiedsrichter:in'];
        }
        if (str_contains($roleName, 'Robot-Checker')) {
            return $icons['Robot-Checker:in'];
        }
        if (str_contains($roleName, 'Live Challenge Leiter')) {
            return $icons['Live Challenge Leiter:in'];
        }
        if (str_contains($roleName, 'Jury-Leiter')) {
            return $icons['Jury-Leiter:in'];
        }
        if (str_contains($roleName, 'Regional-Partner')) {
            return $icons['Regional-Partner:in Challenge'];
        }
        if (str_contains($roleName, 'Gutachter')) {
            return $icons['Gutachter:in'];
        }
        if (str_contains($roleName, 'Moderator')) {
            return $icons['Moderator:in'];
        }

        // Default icon - colorful generic badge
        return '<defs><linearGradient id="defaultGrad" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" style="stop-color:#6B7280;stop-opacity:1" /><stop offset="100%" style="stop-color:#4B5563;stop-opacity:1" /></linearGradient></defs><g class="icon-fill" fill="url(#defaultGrad)"><circle cx="32" cy="32" r="18"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2.5"><path d="M 32 24 L 32 40 M 24 32 L 40 32"/></g>';
    }
}
