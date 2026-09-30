<?php

namespace App\Helpers;

use App\Models\ZoneSite;

class CodeGenerator
{
    /**
     * Génère un code emplacement unique à partir du nom
     * 
     * Exemples :
     * - "Bureau Technicien" → "BT" ou "BUTEC" si "BT" existe
     * - "Salle de Conference" → "SDC" ou "SACON" si "SDC" existe
     * - "Villa 12" → "VIL12"
     * - "Grande Semotheque" → "GRSE"
     */
    public static function generateEmplacementCode(string $nomZone, ?int $excludeId = null): string
    {
        // Nettoyer le nom
        $nomClean = self::cleanName($nomZone);
        
        // Stratégie 1 : Initiales des mots (max 6 caractères)
        $code = self::generateInitials($nomClean);
        
        // Vérifier l'unicité
        if (!self::isCodeUnique($code, $excludeId)) {
            // Stratégie 2 : Initiales étendues (premières lettres de chaque mot)
            $code = self::generateExtendedCode($nomClean);
            
            if (!self::isCodeUnique($code, $excludeId)) {
                // Stratégie 3 : Ajouter un numéro séquentiel
                $baseCode = $code;
                $counter = 1;
                do {
                    $code = $baseCode . $counter;
                    $counter++;
                } while (!self::isCodeUnique($code, $excludeId) && $counter < 100);
            }
        }
        
        return strtoupper($code);
    }
    
    /**
     * Nettoyer le nom (enlever accents, caractères spéciaux, etc.)
     */
    private static function cleanName(string $name): string
    {
        // Enlever les accents
        $name = self::removeAccents($name);
        
        // Enlever les caractères spéciaux sauf espaces, tirets et chiffres
        $name = preg_replace('/[^a-zA-Z0-9\s\-]/', '', $name);
        
        // Remplacer tirets par espaces
        $name = str_replace('-', ' ', $name);
        
        // Enlever les espaces multiples
        $name = preg_replace('/\s+/', ' ', $name);
        
        return trim($name);
    }
    
    /**
     * Enlever les accents
     */
    private static function removeAccents(string $string): string
    {
        $accents = [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c', 'ñ' => 'n',
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C', 'Ñ' => 'N',
        ];
        
        return strtr($string, $accents);
    }
    
    /**
     * Générer les initiales (première lettre de chaque mot)
     * Exemple : "Bureau Technicien" → "BT"
     */
    private static function generateInitials(string $name): string
    {
        $words = explode(' ', $name);
        $code = '';
        
        foreach ($words as $word) {
            if (!empty($word)) {
                // Si le mot contient un chiffre, prendre tout le mot
                if (preg_match('/\d/', $word)) {
                    $code .= $word;
                } else {
                    $code .= substr($word, 0, 1);
                }
            }
        }
        
        // Limiter à 6 caractères maximum
        return substr($code, 0, 6);
    }
    
    /**
     * Générer un code étendu (2-3 premières lettres de chaque mot)
     * Exemple : "Bureau Technicien" → "BUTEC"
     */
    private static function generateExtendedCode(string $name): string
    {
        $words = explode(' ', $name);
        $code = '';
        
        // Mots courts (articles, prépositions) à ignorer
        $skipWords = ['de', 'du', 'la', 'le', 'les', 'et', 'a'];
        
        foreach ($words as $word) {
            if (empty($word) || in_array(strtolower($word), $skipWords)) {
                continue;
            }
            
            // Si le mot contient un chiffre, prendre tout
            if (preg_match('/\d/', $word)) {
                $code .= $word;
            } else {
                // Prendre 2-3 premières lettres selon la longueur du mot
                $len = strlen($word) <= 4 ? 2 : 3;
                $code .= substr($word, 0, $len);
            }
        }
        
        // Limiter à 8 caractères maximum
        return substr($code, 0, 8);
    }
    
    /**
     * Vérifier si le code est unique
     */
    private static function isCodeUnique(string $code, ?int $excludeId = null): bool
    {
        $query = ZoneSite::where('code_zone', $code);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return !$query->exists();
    }
}
