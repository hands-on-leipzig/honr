<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FirstProgram;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class AdminFirstProgramController extends Controller
{
    private const LOGO_DIR = 'images/logos/programs';
    private const DEFAULT_LOGO = 'images/logos/programs/default.svg';

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
            FirstProgram::withCount(['seasons', 'roles', 'events'])
                ->orderBy('sort_order')
                ->get()
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:first_programs,name',
            'sort_order' => 'required|integer',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.unique' => 'Dieser Programmname existiert bereits.',
        ]);

        $program = FirstProgram::create($request->only(['name', 'sort_order', 'valid_from', 'valid_to']));

        return response()->json($program, 201);
    }

    public function update(Request $request, FirstProgram $firstProgram)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:first_programs,name,' . $firstProgram->id,
            'sort_order' => 'required|integer',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ], [
            'name.unique' => 'Dieser Programmname existiert bereits.',
        ]);

        $firstProgram->update($request->only(['name', 'sort_order', 'valid_from', 'valid_to']));

        return response()->json($firstProgram);
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'programs' => 'required|array',
            'programs.*.id' => 'required|exists:first_programs,id',
            'programs.*.sort_order' => 'required|integer',
        ]);

        foreach ($request->programs as $item) {
            FirstProgram::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['message' => 'Reihenfolge aktualisiert.']);
    }

    public function destroy(FirstProgram $firstProgram)
    {
        // Check for dependent records
        if ($firstProgram->seasons()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Saisons damit verknüpft sind.'
            ], 422);
        }

        if ($firstProgram->roles()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Rollen damit verknüpft sind.'
            ], 422);
        }

        if ($firstProgram->events()->exists()) {
            return response()->json([
                'message' => 'Dieses Programm kann nicht gelöscht werden, da noch Veranstaltungen damit verknüpft sind.'
            ], 422);
        }

        $firstProgram->delete();

        return response()->json(['message' => 'Programm gelöscht.']);
    }

    public function uploadLogo(Request $request, FirstProgram $firstProgram)
    {
        $request->validate([
            'logo' => 'required|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
        ]);

        if ($firstProgram->logo_path) {
            $this->deleteLogoFile($firstProgram->logo_path);
        }

        $extension = $request->file('logo')->getClientOriginalExtension();
        $filename = $firstProgram->id . '.' . $extension;
        $path = self::LOGO_DIR . '/' . $filename;
        $dir = public_path(self::LOGO_DIR);

        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
        $request->file('logo')->move($dir, $filename);
        $firstProgram->update(['logo_path' => $path]);

        return response()->json([
            'logo_path' => $path,
            'logo_url' => asset($path),
        ]);
    }

    public function deleteLogo(FirstProgram $firstProgram)
    {
        if ($firstProgram->logo_path) {
            $this->deleteLogoFile($firstProgram->logo_path);
        }

        $firstProgram->update(['logo_path' => self::DEFAULT_LOGO]);

        return response()->json(['message' => 'Logo gelöscht.']);
    }
}

