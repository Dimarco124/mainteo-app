<?php

namespace App\Http\Controllers;

use App\Models\BaseSite;
use App\Models\Client;
use App\Models\Equipement;
use Illuminate\Http\Request;

class BaseSiteController extends Controller
{
    public function index(Request $request)
    {
        $query = BaseSite::with('client', 'equipements');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom_base', 'like', "%{$search}%")
                  ->orWhere('code_base', 'like', "%{$search}%");
            });
        }

        if ($clientId = $request->input('client_id')) {
            $query->where('client_id', $clientId);
        }

        $bases      = $query->orderBy('nom_base', 'asc')->paginate(15);
        $totalCount = BaseSite::count();
        $clients    = Client::orderBy('nom')->get();

        return view('bases_sites.index', compact('bases', 'totalCount', 'clients'));
    }

    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        $selectedClientId = request('client_id');
        return view('bases_sites.create', compact('clients', 'selectedClientId'));
    }

    public function store(Request $request)
    {
        // Validation avec unicité du code_base PAR CLIENT
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'nom_base'  => 'required|string|max:100',
            'code_base' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = BaseSite::where('code_base', $value)
                        ->where('client_id', $request->client_id)
                        ->exists();
                    if ($exists) {
                        $fail('Ce code base existe déjà pour ce client.');
                    }
                },
            ],
        ]);

        BaseSite::create($validated);

        return redirect()->route('clients.combined', ['view' => 'bases'])->with('success', 'Base créée avec succès !');
    }

    public function show($id)
    {
        $base = BaseSite::with('client', 'equipements')->findOrFail($id);
        return view('bases_sites.show', compact('base'));
    }

    public function edit($id)
    {
        $base    = BaseSite::findOrFail($id);
        $clients = Client::orderBy('nom')->get();
        return view('bases_sites.edit', compact('base', 'clients'));
    }

    public function update(Request $request, $id)
    {
        $base = BaseSite::findOrFail($id);

        // Validation avec unicité du code_base PAR CLIENT (sauf la base actuelle)
        $validated = $request->validate([
            'client_id' => 'required|integer|exists:clients,id',
            'nom_base'  => 'required|string|max:100',
            'code_base' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) use ($request, $base) {
                    $exists = BaseSite::where('code_base', $value)
                        ->where('client_id', $request->client_id)
                        ->where('id', '!=', $base->id)
                        ->exists();
                    if ($exists) {
                        $fail('Ce code base existe déjà pour ce client.');
                    }
                },
            ],
        ]);

        $base->update($validated);

        return redirect()->route('clients.combined', ['view' => 'bases'])->with('success', 'Base mise à jour avec succès !');
    }

    public function destroy($id)
    {
        $base = BaseSite::findOrFail($id);
        
        // Vérifier s'il y a des sites rattachés
        if ($base->sites()->count() > 0) {
            return redirect()->route('clients.combined', ['view' => 'bases'])
                ->with('error', 'Impossible de supprimer cette base car elle contient des sites. Supprimez d\'abord les sites.');
        }
        
        $base->delete();
        
        return redirect()->route('clients.combined', ['view' => 'bases'])
            ->with('success', 'Base supprimée avec succès !');
    }
}
