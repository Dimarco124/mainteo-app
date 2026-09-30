<?php

namespace App\Http\Controllers;

use App\Models\Equipement;
use App\Models\FicheFroid;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FicheFroidController extends Controller
{
    public function index(Request $request)
    {
        $query = FicheFroid::with(['equipement', 'technicien']);

        // Recherche par mot-clé (freon, équipement, observations)
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('freon', 'like', "%{$search}%")
                  ->orWhere('observations', 'like', "%{$search}%")
                  ->orWhereHas('equipement', function ($eqQuery) use ($search) {
                      $eqQuery->where('equipement_code', 'like', "%{$search}%")
                              ->orWhere('equipement_nom', 'like', "%{$search}%");
                  });
            });
        }

        // Filtre par type de fréon
        if ($freon = $request->input('freon')) {
            $query->where('freon', $freon);
        }

        $fichesFroid = $query->orderBy('date_saisie', 'desc')->paginate(15);
        $totalCount = FicheFroid::count();

        return view('fiches_froid.index', compact('fichesFroid', 'totalCount'));
    }

    public function show($id)
    {
        $ficheFroid = FicheFroid::with(['equipement', 'technicien'])->findOrFail($id);

        return view('fiches_froid.show', compact('ficheFroid'));
    }

    public function create(Request $request)
    {
        $equipements = Equipement::all();
        $selectedEquipementId = $request->input('equipement_id');

        return view('fiches_froid.create', compact('equipements', 'selectedEquipementId'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'equipement_id' => 'required|integer',
            'freon' => 'required|string|max:50',
            'etat_filtres' => 'nullable|string|max:100',
            'dismatic' => 'nullable|string|max:100',
            'dpn' => 'nullable|string|max:100',
            'support' => 'nullable|string|max:100',
            'telecommande' => 'nullable|string|max:100',
            'cuivre' => 'nullable|string|max:100',
            'armaflex' => 'nullable|string|max:100',
            'test' => 'nullable|string|max:100',
            'etat_general' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
        ]);

        $ficheFroid = new FicheFroid($validated);
        $ficheFroid->technicien_id = $user->id;
        $ficheFroid->date_saisie = now();
        $ficheFroid->save();

        return redirect()->route('fiches-froid.show', $ficheFroid->id)
            ->with('success', 'Fiche Froid (F-GAS) enregistrée avec succès !');
    }

    public function edit($id)
    {
        $ficheFroid = FicheFroid::findOrFail($id);
        $equipements = Equipement::all();

        return view('fiches_froid.edit', compact('ficheFroid', 'equipements'));
    }

    public function update(Request $request, $id)
    {
        $ficheFroid = FicheFroid::findOrFail($id);

        $validated = $request->validate([
            'equipement_id' => 'required|integer',
            'freon' => 'required|string|max:50',
            'etat_filtres' => 'nullable|string|max:100',
            'dismatic' => 'nullable|string|max:100',
            'dpn' => 'nullable|string|max:100',
            'support' => 'nullable|string|max:100',
            'telecommande' => 'nullable|string|max:100',
            'cuivre' => 'nullable|string|max:100',
            'armaflex' => 'nullable|string|max:100',
            'test' => 'nullable|string|max:100',
            'etat_general' => 'nullable|string|max:100',
            'observations' => 'nullable|string',
        ]);

        $ficheFroid->update($validated);

        return redirect()->route('fiches-froid.show', $ficheFroid->id)
            ->with('success', 'Fiche Froid mise à jour avec succès !');
    }
}
