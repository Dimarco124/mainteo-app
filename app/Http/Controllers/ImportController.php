<?php

namespace App\Http\Controllers;

use App\Imports\ClientsImport;
use App\Imports\BasesImport;
use App\Imports\SitesImport;
use App\Imports\EquipementsImport;
use App\Imports\EmplacementsImport;
use App\Exports\ClientsTemplateExport;
use App\Exports\BasesTemplateExport;
use App\Exports\SitesTemplateExport;
use App\Exports\EquipementsTemplateExport;
use App\Exports\EquipementsExistingDataExport;
use App\Exports\EmplacementsTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class ImportController extends Controller
{
    /**
     * Page d'import des clients
     */
    public function importClientsForm()
    {
        return view('imports.clients');
    }

    /**
     * Télécharger le template Excel clients
     */
    public function downloadClientsTemplate()
    {
        return Excel::download(new ClientsTemplateExport, 'template_clients.xlsx');
    }

    /**
     * Importer les clients
     */
    public function importClients(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // 10MB max
        ]);

        try {
            $import = new ClientsImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $errors = $import->getErrors();

            $message = "Import terminé : {$imported} client(s) créé(s), {$updated} mis à jour.";
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " erreur(s) détectée(s).";
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Ligne {$failure->row()} : " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Erreurs de validation détectées')->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Page d'import des bases
     */
    public function importBasesForm()
    {
        return view('imports.bases');
    }

    /**
     * Télécharger le template Excel bases
     */
    public function downloadBasesTemplate()
    {
        return Excel::download(new BasesTemplateExport, 'template_bases.xlsx');
    }

    /**
     * Importer les bases
     */
    public function importBases(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new BasesImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $errors = $import->getErrors();

            $message = "Import terminé : {$imported} base(s) créée(s), {$updated} mise(s) à jour.";
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " erreur(s) détectée(s).";
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Ligne {$failure->row()} : " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Erreurs de validation détectées')->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Page d'import des sites
     */
    public function importSitesForm()
    {
        return view('imports.sites');
    }

    /**
     * Télécharger le template Excel sites
     */
    public function downloadSitesTemplate()
    {
        return Excel::download(new SitesTemplateExport, 'template_sites.xlsx');
    }

    /**
     * Importer les sites
     */
    public function importSites(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new SitesImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $errors = $import->getErrors();

            $message = "Import terminé : {$imported} site(s) créé(s), {$updated} mis à jour.";
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " erreur(s) détectée(s).";
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Ligne {$failure->row()} : " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Erreurs de validation détectées')->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Page d'import des équipements
     */
    public function importEquipementsForm()
    {
        return view('imports.equipements');
    }

    /**
     * Télécharger le template Excel équipements
     */
    public function downloadEquipementsTemplate()
    {
        return Excel::download(new EquipementsTemplateExport, 'template_equipements.xlsx');
    }

    /**
     * Exporter les équipements existants avec colonne emplacement vide (prêts pour renseignement)
     */
    public function exportExistingEquipements()
    {
        return Excel::download(new EquipementsExistingDataExport, 'equipements_existants_pour_emplacements.xlsx');
    }

    /**
     * Importer les équipements
     */
    public function importEquipements(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new EquipementsImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $errors = $import->getErrors();

            $message = "Import terminé : {$imported} équipement(s) créé(s), {$updated} mis à jour.";
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " erreur(s) détectée(s).";
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Ligne {$failure->row()} : " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Erreurs de validation détectées')->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }

    /**
     * Page d'import des emplacements
     */
    public function importEmplacementsForm()
    {
        return view('imports.emplacements');
    }

    /**
     * Télécharger le template Excel emplacements
     */
    public function downloadEmplacementsTemplate()
    {
        return Excel::download(new EmplacementsTemplateExport, 'template_emplacements.xlsx');
    }

    /**
     * Importer les emplacements
     */
    public function importEmplacements(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new EmplacementsImport();
            Excel::import($import, $request->file('file'));

            $imported = $import->getImportedCount();
            $updated = $import->getUpdatedCount();
            $errors = $import->getErrors();

            $message = "Import terminé : {$imported} emplacement(s) créé(s), {$updated} mis à jour.";
            
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " erreur(s) détectée(s).";
                return back()->with('warning', $message)->with('errors', $errors);
            }

            return back()->with('success', $message);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errors = [];
            
            foreach ($failures as $failure) {
                $errors[] = "Ligne {$failure->row()} : " . implode(', ', $failure->errors());
            }
            
            return back()->with('error', 'Erreurs de validation détectées')->with('errors', $errors);
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'import : ' . $e->getMessage());
        }
    }
}
