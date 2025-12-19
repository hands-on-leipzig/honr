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
      .icon { fill: none; stroke: #1f2937; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    </style>
  </defs>
  {$icon}
</svg>
SVG;

        return $svg;
    }

    private function getIconForRole(string $roleName): string
    {
        // Map role names to SVG icon paths - simple, badge-friendly icons
        $icons = [
            'Moderator:in' => '<g class="icon"><circle cx="32" cy="28" r="8"/><path d="M 20 44 Q 32 52 44 44"/><path d="M 24 40 Q 32 46 40 40"/></g>',
            'Coach:in' => '<g class="icon"><circle cx="32" cy="22" r="7"/><path d="M 18 42 L 22 36 L 32 40 L 42 36 L 46 42"/><path d="M 18 42 L 32 48 L 46 42"/></g>',
            'Coach:in Challenge' => '<g class="icon"><circle cx="32" cy="22" r="7"/><path d="M 18 42 L 22 36 L 32 40 L 42 36 L 46 42"/><path d="M 18 42 L 32 48 L 46 42"/><rect x="20" y="30" width="24" height="3" rx="1"/></g>',
            'Coach:in Explore' => '<g class="icon"><circle cx="32" cy="22" r="7"/><path d="M 18 42 L 22 36 L 32 40 L 42 36 L 46 42"/><path d="M 18 42 L 32 48 L 46 42"/><circle cx="32" cy="32" r="5"/></g>',
            'Juror:in' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><path d="M 26 28 L 38 28 M 26 36 L 38 36"/></g>',
            'Juror:in Roboter-Design' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><rect x="22" y="26" width="20" height="14" rx="1"/><circle cx="28" cy="33" r="2"/><circle cx="36" cy="33" r="2"/><path d="M 24 40 L 40 40"/></g>',
            'Juror:in Grundwerte' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><path d="M 26 28 Q 32 24 38 28"/><path d="M 26 36 Q 32 40 38 36"/><circle cx="32" cy="32" r="2" fill="#1f2937"/></g>',
            'Juror:in Teamwork' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><circle cx="26" cy="28" r="4"/><circle cx="38" cy="28" r="4"/><path d="M 24 36 Q 32 42 40 36"/></g>',
            'Juror:in Forschung' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><path d="M 24 26 L 40 26 M 28 30 L 36 30 M 32 26 L 32 38"/><circle cx="32" cy="32" r="3"/></g>',
            'Juror:in "SAP Sonderpreis für beste Programmierung"' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><path d="M 24 26 L 40 26 L 38 38 L 26 38 Z"/><path d="M 28 30 L 36 30 M 30 32 L 34 32"/></g>',
            'Schiedsrichter:in' => '<g class="icon"><circle cx="32" cy="22" r="9"/><path d="M 20 38 L 18 56 L 32 52 L 46 56 L 44 38"/><path d="M 20 38 L 32 42 L 44 38"/><path d="M 24 32 L 32 36 L 40 32"/></g>',
            'Ober-Schiedsrichter:in' => '<g class="icon"><circle cx="32" cy="18" r="7"/><path d="M 22 34 L 18 56 L 32 52 L 46 56 L 42 34"/><path d="M 22 34 L 32 38 L 42 34"/><path d="M 26 28 L 32 30 L 38 28"/><circle cx="32" cy="18" r="2" fill="#1f2937"/></g>',
            'Robot-Checker:in' => '<g class="icon"><rect x="16" y="18" width="32" height="22" rx="2"/><circle cx="26" cy="29" r="3"/><circle cx="38" cy="29" r="3"/><path d="M 20 36 L 44 36"/><path d="M 24 40 L 40 40"/></g>',
            'Live Challenge Juror' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><circle cx="32" cy="32" r="7"/><path d="M 26 28 L 38 28 M 26 36 L 38 36"/></g>',
            'Live Challenge Leiter:in' => '<g class="icon"><circle cx="32" cy="22" r="9"/><path d="M 20 38 L 18 56 L 32 52 L 46 56 L 44 38"/><path d="M 20 38 L 32 42 L 44 38"/><circle cx="32" cy="32" r="5"/></g>',
            'Jury-Leiter:in' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><circle cx="32" cy="18" r="2" fill="#1f2937"/><path d="M 26 26 L 38 26 M 28 30 L 36 30 M 26 34 L 38 34"/></g>',
            'Regional-Partner:in Challenge' => '<g class="icon"><path d="M 32 14 L 42 24 L 38 24 L 38 42 L 26 42 L 26 24 L 22 24 Z"/><circle cx="32" cy="50" r="4"/></g>',
            'Gutachter:in' => '<g class="icon"><path d="M 18 18 L 46 18 L 42 50 L 22 50 Z"/><path d="M 26 26 L 38 26 M 32 26 L 32 36 M 26 34 L 38 34"/><circle cx="32" cy="30" r="2" fill="#1f2937"/></g>',
        ];

        // Try exact match first
        if (isset($icons[$roleName])) {
            return $icons[$roleName];
        }

        // Try partial matches
        if (str_contains($roleName, 'Coach')) {
            return $icons['Coach:in'];
        }
        if (str_contains($roleName, 'Juror')) {
            return $icons['Juror:in'];
        }
        if (str_contains($roleName, 'Schiedsrichter')) {
            return $icons['Schiedsrichter:in'];
        }

        // Default icon
        return '<g class="icon"><circle cx="32" cy="32" r="20"/><path d="M 32 20 L 32 44 M 20 32 L 44 32"/></g>';
    }
}
