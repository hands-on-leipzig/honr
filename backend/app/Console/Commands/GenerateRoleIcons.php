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
        $icon = $this->getIconForRole($role->name);
        
        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="64" height="64">
  <defs>
    <style>
      .icon-stroke { fill: none; stroke: #1f2937; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
      .icon-fill { fill: #1f2937; stroke: none; }
      .icon-light { fill: #1f2937; opacity: 0.3; }
    </style>
  </defs>
  {$icon}
</svg>
SVG;

        return $svg;
    }

    private function getIconForRole(string $roleName): string
    {
        // Map role names to improved, distinctive SVG icons
        // Icons use filled shapes and clearer symbols for better recognition
        $icons = [
            // Moderator:in - Microphone icon
            'Moderator:in' => '<g class="icon-fill"><rect x="28" y="12" width="8" height="16" rx="1"/><path d="M 30 28 Q 32 30 34 28"/><path d="M 26 32 Q 26 36 32 36 Q 38 36 38 32"/></g><g class="icon-stroke"><path d="M 24 44 L 24 48 M 28 44 L 28 48 M 36 44 L 36 48 M 40 44 L 40 48"/></g>',
            
            // Coach:in - Person with clipboard/coaching board
            'Coach:in' => '<g class="icon-fill"><circle cx="32" cy="20" r="6"/><path d="M 20 38 Q 20 32 32 32 Q 44 32 44 38 L 44 48 L 20 48 Z"/></g><g class="icon-stroke"><rect x="46" y="24" width="8" height="10" rx="1"/><path d="M 48 26 L 52 26"/></g>',
            
            // Coach:in Challenge - Coach with Challenge badge
            'Coach:in Challenge' => '<g class="icon-fill"><circle cx="32" cy="20" r="6"/><path d="M 20 38 Q 20 32 32 32 Q 44 32 44 38 L 44 48 L 20 48 Z"/></g><g class="icon-stroke"><rect x="46" y="24" width="8" height="10" rx="1"/><path d="M 48 26 L 52 26 M 50 28 L 50 32"/></g><g class="icon-fill"><rect x="47" y="29" width="2" height="2"/></g>',
            
            // Coach:in Explore - Coach with Explore circle
            'Coach:in Explore' => '<g class="icon-fill"><circle cx="32" cy="20" r="6"/><path d="M 20 38 Q 20 32 32 32 Q 44 32 44 38 L 44 48 L 20 48 Z"/></g><g class="icon-stroke"><circle cx="50" cy="29" r="5"/><circle cx="50" cy="29" r="3"/></g>',
            
            // Juror:in - Clipboard with checkmark
            'Juror:in' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-stroke" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M 28 32 L 30 34 L 36 28"/></g>',
            
            // Juror:in Roboter-Design - Clipboard with robot
            'Juror:in Roboter-Design' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><rect x="26" y="26" width="12" height="10" rx="1"/><circle cx="30" cy="31" r="1.5"/><circle cx="34" cy="31" r="1.5"/></g><g class="icon-stroke" stroke-width="1.5"><path d="M 28 35 L 36 35"/></g>',
            
            // Juror:in Grundwerte - Clipboard with heart/values symbol
            'Juror:in Grundwerte' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><path d="M 32 28 C 30 26 28 28 28 30 C 28 32 32 36 32 36 C 32 36 36 32 36 30 C 36 28 34 26 32 28 Z"/></g>',
            
            // Juror:in Teamwork - Clipboard with people/team symbol
            'Juror:in Teamwork' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><circle cx="28" cy="28" r="3"/><circle cx="36" cy="28" r="3"/></g><g class="icon-stroke" stroke-width="2"><path d="M 26 32 Q 32 36 38 32"/></g>',
            
            // Juror:in Forschung - Clipboard with lightbulb/research symbol
            'Juror:in Forschung' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><path d="M 32 24 C 28 24 26 26 26 30 C 26 32 28 34 30 34 L 34 34 C 36 34 38 32 38 30 C 38 26 36 24 32 24 Z"/><rect x="31" y="34" width="2" height="4"/></g><g class="icon-stroke" stroke-width="1.5"><path d="M 30 38 L 34 38"/></g>',
            
            // Juror:in SAP Sonderpreis - Clipboard with code/programming symbol
            'Juror:in "SAP Sonderpreis für beste Programmierung"' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><rect x="26" y="28" width="4" height="4"/><rect x="34" y="28" width="4" height="4"/><rect x="26" y="34" width="4" height="4"/><rect x="34" y="34" width="4" height="4"/></g>',
            
            // Schiedsrichter:in - Whistle icon
            'Schiedsrichter:in' => '<g class="icon-fill"><rect x="24" y="20" width="14" height="20" rx="2"/></g><g class="icon-stroke"><circle cx="42" cy="30" r="4"/><path d="M 46 30 L 50 30"/></g><g class="icon-fill"><circle cx="28" cy="24" r="2"/></g>',
            
            // Ober-Schiedsrichter:in - Whistle with star/crown
            'Ober-Schiedsrichter:in' => '<g class="icon-fill"><rect x="24" y="20" width="14" height="20" rx="2"/></g><g class="icon-stroke"><circle cx="42" cy="30" r="4"/><path d="M 46 30 L 50 30"/></g><g class="icon-fill"><circle cx="28" cy="24" r="2"/><path d="M 32 16 L 33 19 L 36 19 L 34 21 L 35 24 L 32 22 L 29 24 L 30 21 L 28 19 L 31 19 Z"/></g>',
            
            // Robot-Checker:in - Magnifying glass with robot
            'Robot-Checker:in' => '<g class="icon-stroke"><circle cx="36" cy="28" r="8"/><path d="M 42 34 L 48 40"/></g><g class="icon-fill"><rect x="28" y="24" width="8" height="6" rx="0.5"/><circle cx="30" cy="27" r="0.8"/><circle cx="34" cy="27" r="0.8"/></g><g class="icon-stroke" stroke-width="1"><path d="M 30 29 L 34 29"/></g>',
            
            // Live Challenge Juror - Clipboard with live/streaming symbol
            'Live Challenge Juror' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><circle cx="32" cy="30" r="6"/><circle cx="32" cy="30" r="3"/></g><g class="icon-stroke"><path d="M 28 26 L 28 34 M 36 26 L 36 34"/></g>',
            
            // Live Challenge Leiter:in - Person with live/streaming symbol
            'Live Challenge Leiter:in' => '<g class="icon-fill"><circle cx="32" cy="20" r="6"/><path d="M 20 38 Q 20 32 32 32 Q 44 32 44 38 L 44 48 L 20 48 Z"/></g><g class="icon-fill"><circle cx="50" cy="30" r="5"/><circle cx="50" cy="30" r="2.5" fill="#ffffff"/></g><g class="icon-stroke"><path d="M 47 28 L 47 32 M 53 28 L 53 32"/></g>',
            
            // Jury-Leiter:in - Clipboard with star/leader symbol
            'Jury-Leiter:in' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-fill"><path d="M 32 24 L 33 27 L 36 27 L 34 29 L 35 32 L 32 30 L 29 32 L 30 29 L 28 27 L 31 27 Z"/></g>',
            
            // Regional-Partner:in Challenge - Map pin with Challenge badge
            'Regional-Partner:in Challenge' => '<g class="icon-fill"><path d="M 32 14 L 40 26 L 36 26 L 36 40 L 28 40 L 28 26 L 24 26 Z"/></g><g class="icon-stroke"><circle cx="32" cy="48" r="4"/><path d="M 30 46 L 34 46 M 32 44 L 32 48"/></g><g class="icon-fill"><rect x="30" y="30" width="4" height="2"/></g>',
            
            // Gutachter:in - Clipboard with magnifying glass
            'Gutachter:in' => '<g class="icon-stroke"><path d="M 20 16 L 44 16 L 44 48 L 20 48 Z"/><path d="M 24 20 L 24 44 M 40 20 L 40 44"/></g><g class="icon-stroke"><circle cx="30" cy="30" r="4"/><path d="M 33 33 L 36 36"/></g>',
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

        // Default icon - improved generic badge
        return '<g class="icon-fill"><circle cx="32" cy="32" r="16"/></g><g class="icon-stroke" stroke="#ffffff" stroke-width="2"><path d="M 32 24 L 32 40 M 24 32 L 40 32"/></g>';
    }
}
