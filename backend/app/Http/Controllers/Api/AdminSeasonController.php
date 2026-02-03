<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminSeasonController extends Controller
{
    private const LOGO_DIR = 'images/logos/seasons';
    private const DEFAULT_LOGO = 'images/logos/seasons/default.svg';

    private function logoPathToAbsolute(string $path): string
    {
        return str_starts_with($path, 'images/')
            ? public_path($path)
            : storage_path('app/public/' . $path);
    }

    private function deleteLogoFile(string $path): void
    {
        $abs = $this->logoPathToAbsolute($path);
        if (File::exists($abs) && !str_ends_with($path, 'default.svg')) {
            File::delete($abs);
        }
    }
    public function index()
    {
        return response()->json(
            Season::with('firstProgram:id,name')
                ->withCount('events')
                ->orderBy('start_year', 'desc')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:1998|max:2100',
            'first_program_id' => 'required|exists:first_programs,id',
        ], [
            'name.required' => 'Der Name ist erforderlich.',
        ]);

        // Check composite unique constraints
        $exists = Season::where('first_program_id', $request->first_program_id)
            ->where(function ($q) use ($request) {
                $q->where('name', $request->name)
                  ->orWhere('start_year', $request->start_year);
            })->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Diese Saison existiert bereits für dieses Programm.'
            ], 422);
        }

        $season = Season::create($request->only(['name', 'start_year', 'first_program_id']));

        return response()->json($season, 201);
    }

    public function update(Request $request, Season $season)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_year' => 'required|integer|min:1998|max:2100',
            'first_program_id' => 'required|exists:first_programs,id',
        ]);

        // Check composite unique constraints (excluding current record)
        $exists = Season::where('first_program_id', $request->first_program_id)
            ->where('id', '!=', $season->id)
            ->where(function ($q) use ($request) {
                $q->where('name', $request->name)
                  ->orWhere('start_year', $request->start_year);
            })->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Diese Saison existiert bereits für dieses Programm.'
            ], 422);
        }

        $season->update($request->only(['name', 'start_year', 'first_program_id']));

        return response()->json($season);
    }

    public function destroy(Season $season)
    {
        if ($season->events()->exists()) {
            return response()->json([
                'message' => 'Diese Saison kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        $season->delete();

        return response()->json(['message' => 'Saison gelöscht.']);
    }

    public function uploadLogo(Request $request, Season $season)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        if ($season->logo_path) {
            $this->deleteLogoFile($season->logo_path);
        }

        $extension = $request->file('logo')->getClientOriginalExtension();
        $filename = $season->id . '.' . $extension;
        $path = self::LOGO_DIR . '/' . $filename;
        $dir = public_path(self::LOGO_DIR);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $request->file('logo')->move($dir, $filename);
        $season->update(['logo_path' => $path]);

        return response()->json([
            'logo_path' => $path,
            'logo_url' => asset($path),
        ]);
    }

    public function deleteLogo(Season $season)
    {
        if ($season->logo_path) {
            $this->deleteLogoFile($season->logo_path);
        }

        $season->update(['logo_path' => self::DEFAULT_LOGO]);

        return response()->json(['message' => 'Logo gelöscht.']);
    }
}

