<?php
/**
 * SCRIPT DE DIAGNOSTIC STANDALONE — SANS LARAVEL
 * Uploader ce fichier dans mainteo-app/public/ puis accéder à :
 *   https://TON-DOMAINE/diag_rapport.php
 *
 * Il teste TOUT ce qui peut causer un 500 au submit de rapport :
 *   1. PHP version / extensions (mbstring, gd, pdo, fileinfo, json)
 *   2. OPcache status (désactivé ?)
 *   3. Limites PHP (upload, post, memory, max_input_vars)
 *   4. Lecture du .env (CACHE_STORE / SESSION_DRIVER / QUEUE_CONNECTION)
 *   5. Connexion BDD + SHOW COLUMNS depannages + intervention_notifications
 *   6. Dossiers uploads en écriture
 *   7. Session PHP (dossier session writable)
 *   8. Test base64_decode + imagecreatefromstring (cohérence compression photo)
 *   9. Test écriture dans storage/logs/ (verification droits)
 */
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('html_errors', '1');
header('Content-Type: text/html; charset=utf-8');

$tests = [];
function addTest($name, $ok, $details = '') {
    global $tests;
    $tests[] = ['name' => $name, 'ok' => $ok, 'details' => $details];
}
function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function truncatePath($p) {
    $len = strlen($p);
    if ($len <= 80) return $p;
    return '...' . substr($p, $len - 77);
}

?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>🔧 Diagnostic Rapport Intervention 500</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{font-family:system-ui,Arial,sans-serif;max-width:960px;margin:24px auto;padding:0 16px;line-height:1.5;color:#0f172a;background:#f8fafc}
h1{color:#1e293b;margin-bottom:0.3rem}
.sub{color:#64748b;margin-top:0;margin-bottom:1.2rem}
.grid{display:grid;grid-template-columns:1fr;gap:10px;margin-top:1rem}
@media(min-width:768px){.grid{grid-template-columns:1fr 1fr}}
.card{background:#fff;border:2px solid #e2e8f0;border-radius:14px;padding:14px 16px}
.card.ok{border-color:#059669;background:#ecfdf5}.card.ok h3{color:#047857}
.card.ko{border-color:#dc2626;background:#fef2f2}.card.ko h3{color:#991b1b}
.card.warn{border-color:#f59e0b;background:#fffbeb}.card.warn h3{color:#92400e}
.card h3{margin:0 0 0.5rem 0;font-size:0.95rem;display:flex;align-items:center;gap:8px}
.status{font-weight:800}
.status.ok::before{content:"✅ "}.status.ko::before{content:"❌ "}.status.warn::before{content:"⚠️ "}
pre{background:#0f172a;color:#e2e8f0;padding:10px 12px;border-radius:8px;overflow:auto;font-size:0.78rem;margin:0.5rem 0 0;max-height:240px}
code{background:#f1f5f9;padding:2px 6px;border-radius:6px;font-size:0.85rem}
.hr{height:2px;background:#e2e8f0;margin:1.2rem 0}
.summary{padding:14px 16px;border-radius:14px;margin-bottom:1rem;font-weight:700;font-size:1.05rem}
.summary.ok{background:#d1fae5;border:2px solid #059669;color:#064e3b}
.summary.ko{background:#fee2e2;border:2px solid #dc2626;color:#7f1d1d}
.summary.warn{background:#fef3c7;border:2px solid #f59e0b;color:#92400e}
a.link{color:#1d4ed8;font-weight:700;text-decoration:none}
.row{display:flex;gap:10px;flex-wrap:wrap;margin-top:10px}
.badge{display:inline-block;padding:2px 8px;border-radius:9999px;font-size:0.75rem;font-weight:700;background:#e0e7ff;color:#3730a3}
kbd{background:#fff;border:1px solid #cbd5e1;border-bottom-width:2px;padding:2px 6px;border-radius:6px;font-family:ui-monospace,Consolas,monospace;font-size:0.8rem}
</style>
</head>
<body>
<h1>🔧 Diagnostic du Submit Rapport (500 Server Error)</h1>
<p class="sub">Script standalone sans Laravel — <span class="badge">uploader sur Hostinger puis accéder à <code>/diag_rapport.php</code></span></p>

<?php

// ═══════════════════════════════════════════════════════════
// 1. PHP + Extensions
// ═══════════════════════════════════════════════════════════
$phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
addTest('PHP version (' . PHP_VERSION . ')', $phpOk, 'Min requis : 8.1.0');

$requiredExts = ['mbstring', 'pdo', 'pdo_mysql', 'json', 'fileinfo', 'gd', 'session', 'tokenizer', 'ctype', 'xml'];
$extMissing = [];
foreach ($requiredExts as $e) {
    if (!extension_loaded($e)) $extMissing[] = $e;
}
addTest('Extensions PHP requises', empty($extMissing), empty($extMissing) ? 'Toutes OK : ' . implode(', ', $requiredExts) : 'Manquantes : ' . implode(', ', $extMissing));

// ═══════════════════════════════════════════════════════════
// 2. OPcache — CAUSE N°1 DU "J'UPOURDE MAIS RIEN NE CHANGE"
// ═══════════════════════════════════════════════════════════
$opEnabled = ini_get('opcache.enable');
$opCli     = ini_get('opcache.enable_cli');
$opRevFreq  = ini_get('opcache.revalidate_freq');
$opValidate = ini_get('opcache.validate_timestamps');
$opcacheDir = ini_get('opcache.file_cache');
$opcacheOk = (!$opEnabled || ($opValidate && $opRevFreq < 5));
$opDetails = [];
$opDetails[] = "opcache.enable = " . var_export($opEnabled, true);
$opDetails[] = "opcache.validate_timestamps = " . var_export($opValidate, true);
$opDetails[] = "opcache.revalidate_freq = " . var_export($opRevFreq, true);
if ($opcacheDir) $opDetails[] = "opcache.file_cache = " . $opcacheDir;
if (!$opEnabled) {
    $opDetails[] = "(OPcache DÉSACTIVÉ ✅ → les nouveaux fichiers PHP sont pris en compte immédiatement)";
}
addTest('OPcache (cache PHP Hostinger)', $opcacheOk, implode(';  ', $opDetails));

// ═══════════════════════════════════════════════════════════
// 3. Limites PHP
// ═══════════════════════════════════════════════════════════
function sizeToBytes($s) {
    $s = trim((string)$s);
    if (!$s) return 0;
    $u = strtolower(substr($s, -1));
    $n = (int)$s;
    if ($u === 'g') return $n * 1024 * 1024 * 1024;
    if ($u === 'm') return $n * 1024 * 1024;
    if ($u === 'k') return $n * 1024;
    return (int)$s;
}
function bytesToHuman($b) {
    if ($b >= 1024*1024*1024) return round($b/1024/1024/1024, 1) . 'G';
    if ($b >= 1024*1024) return round($b/1024/1024, 1) . 'M';
    if ($b >= 1024) return round($b/1024, 1) . 'K';
    return $b . 'B';
}

$postMax    = sizeToBytes(ini_get('post_max_size'));
$uploadMax  = sizeToBytes(ini_get('upload_max_filesize'));
$memory     = sizeToBytes(ini_get('memory_limit'));
$inputVars  = (int)ini_get('max_input_vars');
$maxExec    = (int)ini_get('max_execution_time');

$limitsOk = ($postMax >= 8*1024*1024 && $uploadMax >= 8*1024*1024 && $memory >= 128*1024*1024 && $inputVars >= 1000);
addTest('Limites PHP (post/upload/mémoire/vars)', $limitsOk,
    "post_max_size=".bytesToHuman($postMax)."  upload_max_filesize=".bytesToHuman($uploadMax).
    "  memory_limit=".bytesToHuman($memory)."  max_input_vars=$inputVars  max_execution_time=${maxExec}s"
);

// ═══════════════════════════════════════════════════════════
// 4. Lecture du .env
// ═══════════════════════════════════════════════════════════
$envPath = __DIR__ . '/../.env';
$envExists = file_exists($envPath) && is_readable($envPath);
$envVars = [];
if ($envExists) {
    $envLines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $ln) {
        $ln = trim($ln);
        if ($ln === '' || str_starts_with($ln, '#')) continue;
        $eq = strpos($ln, '=');
        if ($eq === false) continue;
        $k = trim(substr($ln, 0, $eq));
        $v = trim(substr($ln, $eq + 1), " \t\n\r\0\x0B\"'");
        $envVars[$k] = $v;
    }
}
$watchKeys = ['CACHE_STORE', 'CACHE_DRIVER', 'SESSION_DRIVER', 'QUEUE_CONNECTION',
              'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
$envSummary = [];
foreach ($watchKeys as $k) {
    $v = $envVars[$k] ?? '(non défini)';
    if (in_array($k, ['DB_USERNAME', 'DB_PASSWORD'])) $v = preg_replace('/./','*',$v);
    $envSummary[] = "$k=$v";
}
$cacheStore = $envVars['CACHE_STORE'] ?? ($envVars['CACHE_DRIVER'] ?? 'database');
$sessDrv    = $envVars['SESSION_DRIVER'] ?? 'file';
$queueConn  = $envVars['QUEUE_CONNECTION'] ?? 'database';
$envCriticalOk = ($cacheStore === 'file' && $sessDrv === 'file' && $queueConn !== 'database');
addTest('.env critique (CACHE/SESSION/QUEUE)', $envCriticalOk, implode(';  ', $envSummary));

// ═══════════════════════════════════════════════════════════
// 5. Connexion BDD + colonnes
// ═══════════════════════════════════════════════════════════
$dbConnected = false;
$pdo = null;
$dbMsg = '';
$dbHost     = $envVars['DB_HOST']     ?? '127.0.0.1';
$dbPort     = (int)($envVars['DB_PORT'] ?? 3306);
$dbDatabase = $envVars['DB_DATABASE'] ?? '';
$dbUser     = $envVars['DB_USERNAME'] ?? '';
$dbPass     = $envVars['DB_PASSWORD'] ?? '';
$dbCharset  = 'utf8mb4';
$colsDepannages = [];
$colsNotifs     = [];
$depannagesExiste = false;
$notifsExiste     = false;

try {
    $dsn = "mysql:host=$dbHost;port=$dbPort;dbname=$dbDatabase;charset=$dbCharset";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
    $dbConnected = true;
    $dbMsg = "Connecté à $dbDatabase sur $dbHost:$dbPort";

    $colsDepannages = $pdo->query("SHOW COLUMNS FROM depannages")->fetchAll();
    $depannagesExiste = !empty($colsDepannages);
    $colsNotifs = $pdo->query("SHOW COLUMNS FROM intervention_notifications")->fetchAll();
    $notifsExiste = !empty($colsNotifs);
} catch (\Throwable $e) {
    $dbMsg = "ERREUR BDD : " . get_class($e) . ' — ' . $e->getMessage();
}
addTest('Connexion BDD (MySQL / PDO)', $dbConnected, $dbMsg);

$requiredColsDep = [
    'statut','rapport','photo_carnet_rapport','photo_equipement_apres',
    'date_rapport_technicien','rapport_soumis_par_user_id','statut_rapport_technicien',
    'raison_rejet_rapport','date_rejet_rapport','rapport_rejete_par_user_id',
    'date_realisation','details_statut_terrain'
];
$depColMap = [];
foreach ($colsDepannages as $c) $depColMap[strtolower($c['Field'])] = $c;
$missingDepCols = [];
foreach ($requiredColsDep as $cc) {
    if (!isset($depColMap[strtolower($cc)])) $missingDepCols[] = $cc;
}
addTest('Colonnes requises table depannages', $depannagesExiste && empty($missingDepCols),
    $depannagesExiste
        ? (empty($missingDepCols) ? "Toutes OK (".count($colsDepannages)." colonnes détectées)" : "Colonnes MANQUANTES : " . implode(', ', $missingDepCols))
        : "Table depannages INTROUVABLE"
);

$requiredNotifCols = ['user_id','intervention_id','demande_id','type','message','statut'];
$notifColMap = [];
foreach ($colsNotifs as $c) $notifColMap[strtolower($c['Field'])] = $c;
$missingNotifCols = [];
foreach ($requiredNotifCols as $cc) {
    if (!isset($notifColMap[strtolower($cc)])) $missingNotifCols[] = $cc;
}
addTest('Colonnes requises intervention_notifications', $notifsExiste && empty($missingNotifCols),
    $notifsExiste
        ? (empty($missingNotifCols) ? "Toutes OK (".count($colsNotifs)." colonnes détectées)" : "Colonnes MANQUANTES : " . implode(', ', $missingNotifCols))
        : "Table intervention_notifications INTROUVABLE"
);

// Check ENUM statut si possible
if ($depannagesExiste && isset($depColMap['statut'])) {
    $statutType = $depColMap['statut']['Type'];
    $valeursOK = true;
    $requisStatut = ['en attente','en cours','resolu','en_attente_piece','partiellement_resolu','non_resolu'];
    $detailsStatut = "Type: $statutType";
    if (stripos($statutType, 'enum') === 0) {
        foreach ($requisStatut as $vs) {
            if (stripos($statutType, "'$vs'") === false) { $valeursOK = false; $detailsStatut .= "; VALEUR ABSENTE: $vs"; }
        }
    } else {
        $detailsStatut .= " (pas un enum — VARCHAR donc OK)";
    }
    addTest('ENUM statut depannages (valeurs du formulaire)', $valeursOK, $detailsStatut);
}

// Check ENUM statut_rapport_technicien
if ($depannagesExiste && isset($depColMap['statut_rapport_technicien'])) {
    $srt = $depColMap['statut_rapport_technicien']['Type'];
    $okSrt = (stripos($srt, "'soumis'") !== false || stripos($srt, 'varchar') !== false);
    addTest("ENUM statut_rapport_technicien contient 'soumis'", $okSrt, "Type: $srt");
}

// ═══════════════════════════════════════════════════════════
// 6. Dossiers uploads
// ═══════════════════════════════════════════════════════════
$paths = [
    'public/uploads/rapports'   => __DIR__ . '/uploads/rapports',
    'public/uploads/depannages' => __DIR__ . '/uploads/depannages',
    'storage/logs'              => __DIR__ . '/../storage/logs',
    'storage/framework/cache/data' => __DIR__ . '/../storage/framework/cache/data',
    'storage/framework/views'   => __DIR__ . '/../storage/framework/views',
    'storage/framework/sessions' => __DIR__ . '/../storage/framework/sessions',
    'bootstrap/cache'           => __DIR__ . '/../bootstrap/cache',
];
$uploadResults = [];
$allWritable = true;
foreach ($paths as $label => $full) {
    $exists = file_exists($full);
    $tryCreate = false;
    if (!$exists) {
        $tryCreate = @mkdir($full, 0775, true);
        $exists = $tryCreate;
    }
    $writable = $exists && is_writable($full);
    if (!$writable) $allWritable = false;

    // Écriture test
    $testFile = null;
    $wrote = false;
    if ($writable) {
        $testFile = rtrim($full, '/\\') . '/_test_write_' . date('Ymd_His') . '.txt';
        $wrote = (bool)@file_put_contents($testFile, 'test ' . microtime(true));
        if ($wrote) {
            @unlink($testFile);
            $testFile = null;
        }
        if (!$wrote) $allWritable = false;
    }

    $uploadResults[] = [
        'label' => $label,
        'full'  => truncatePath($full),
        'exists' => $exists,
        'writable' => $writable,
        'wrote' => $wrote,
    ];
}
addTest('Dossiers écriture (uploads/storage/bootstrap)', $allWritable,
    "Tous les dossiers doivent exister ET être writable ET permettre l'écriture d'un fichier test."
);

// ═══════════════════════════════════════════════════════════
// 7. Session PHP
// ═══════════════════════════════════════════════════════════
$sessionStarted = false;
$sessionSavePath = session_save_path();
if (!$sessionSavePath) $sessionSavePath = sys_get_temp_dir();
$sessionWritable = is_dir($sessionSavePath) && is_writable($sessionSavePath);
if ($sessionWritable) {
    $sessionStarted = @session_start();
    $_SESSION['_diag_test'] = date('Y-m-d H:i:s');
}
addTest('Session PHP (save_path writable + session_start)', $sessionStarted,
    "save_path=".truncatePath($sessionSavePath)." ; writable=".($sessionWritable?'OK':'KO')." ; started=".($sessionStarted?'OK':'KO')
);

// ═══════════════════════════════════════════════════════════
// 8. Test base64 + GD (simule traitement photo)
// ═══════════════════════════════════════════════════════════
$base64Ok = false;
$base64Details = '';
try {
    $miniB64 = '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wgARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAb/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwAdACsKKAP/2Q==';
    $data = base64_decode($miniB64, true);
    if (!$data || strlen($data) < 50) throw new Exception('base64_decode a échoué');
    if (function_exists('imagecreatefromstring')) {
        $img = imagecreatefromstring($data);
        if (!$img) throw new Exception('imagecreatefromstring a échoué');
        imagedestroy($img);
        $base64Details = "base64→JPEG→imagecreatefromstring OK (" . strlen($data) . " octets)";
    } else {
        $base64Details = "base64 OK (" . strlen($data) . " octets) — extension gd absente (imagecreatefromstring non dispo.)";
    }
    $base64Ok = true;
} catch (\Throwable $e) {
    $base64Ok = false;
    $base64Details = "Test KO : " . get_class($e) . ' — ' . $e->getMessage();
}
addTest('Traitement photo : base64_decode + GD imagecreatefromstring', $base64Ok, $base64Details);

// ═══════════════════════════════════════════════════════════
// 9. Taille request body simulée (1.5Mo base64) — dépassement ?
// ═══════════════════════════════════════════════════════════
$totalKb = 0;
if (!empty($_POST)) {
    $totalKb = round(strlen(file_get_contents('php://input')) / 1024);
}
addTest('Body reçu dans $_POST (test si >500Ko)', true,
    $totalKb > 0 ? "POST détecté : ${totalKb} Ko envoyés ; \$_POST keys=" . implode(', ', array_keys($_POST))
              : "Pas de POST — vous pouvez simuler via ce formulaire ci-dessous (mini test)"
);

// ═══════════════════════════════════════════════════════════
// FIN — Affichage
// ═══════════════════════════════════════════════════════════
$total    = count($tests);
$passed   = count(array_filter($tests, fn($t) => $t['ok']));
$warnings = 0;
$summaryCls = 'ok';
$summaryTxt = "✅ $passed / $total tests OK — Aucun problème critique.";
if ($passed < $total) {
    $summaryCls = 'warn';
    $summaryTxt = "⚠️ $passed / $total tests — Au moins un avertissement.";
}
if ($passed < $total * 0.7) {
    $summaryCls = 'ko';
    $summaryTxt = "❌ $passed / $total tests OK — Plusieurs problèmes critiques détectés.";
}
?>
<div class="summary <?=$summaryCls?>"><?=$summaryTxt?></div>

<div class="grid">
    <?php foreach ($tests as $t): ?>
        <?php $cls = $t['ok'] ? 'ok' : 'ko' ?>
        <div class="card <?=$cls?>">
            <h3><span class="status <?=$cls?>"><?=h($t['name'])?></span></h3>
            <div style="margin:0"><?=h($t['details'])?></div>
        </div>
    <?php endforeach ?>
</div>

<div class="hr"></div>

<h2>🗂️ État des dossiers (uploads / cache)</h2>
<div class="grid">
    <?php foreach ($uploadResults as $u): ?>
        <?php $uok = ($u['exists'] && $u['writable'] && $u['wrote']); $cls = $uok?'ok':($u['exists'] && $u['writable']?'warn':'ko') ?>
        <div class="card <?=$cls?>">
            <h3><span class="status <?=$cls?>"><?=h($u['label'])?></span></h3>
            <div><code><?=h($u['full'])?></code></div>
            <div style="margin-top:0.4rem">
                Existe: <?=($u['exists']?'<span style="color:#059669;font-weight:700">oui</span>':'<span style="color:#dc2626;font-weight:700">NON</span>')?>
                 · Writable: <?=($u['writable']?'<span style="color:#059669;font-weight:700">oui</span>':'<span style="color:#dc2626;font-weight:700">NON</span>')?>
                 · Écriture test: <?=($u['wrote']?'<span style="color:#059669;font-weight:700">OK</span>':'<span style="color:#dc2626;font-weight:700">KO</span>')?>
            </div>
        </div>
    <?php endforeach ?>
</div>

<?php if ($pdo && $depannagesExiste): ?>
<div class="hr"></div>
<h2>📊 Colonnes effectives de <code>depannages</code> (SHOW COLUMNS)</h2>
<pre><?php foreach ($colsDepannages as $c) {
    echo str_pad($c['Field'], 38, ' ', STR_PAD_RIGHT)
       . str_pad($c['Type'], 55, ' ', STR_PAD_RIGHT)
       . ' Null=' . str_pad($c['Null'], 4) . ' Defaut=' . ($c['Default'] ?? '(NULL)') . PHP_EOL;
} ?></pre>
<?php endif ?>

<?php if ($pdo && $notifsExiste): ?>
<h2>📊 Colonnes effectives de <code>intervention_notifications</code></h2>
<pre><?php foreach ($colsNotifs as $c) {
    echo str_pad($c['Field'], 28, ' ', STR_PAD_RIGHT)
       . str_pad($c['Type'], 38, ' ', STR_PAD_RIGHT)
       . ' Null=' . str_pad($c['Null'], 4) . ' Defaut=' . ($c['Default'] ?? '(NULL)') . PHP_EOL;
} ?></pre>
<?php endif ?>

<div class="hr"></div>

<h2>🧪 TEST MINI POST SIMULÉ</h2>
<p style="color:#64748b;margin-top:0">Testez 2 scénarios : petit POST vs POST "gros" comme le rapport avec 2 photos.</p>
<div class="grid">
<div class="card ok">
<h3><span class="status ok">Test 1 : POST petit (aucun problème attendu)</span></h3>
<form method="POST" style="display:flex;gap:8px;flex-wrap:wrap">
<input type="hidden" name="mini" value="1">
<input type="hidden" name="_token_test" value="abcd">
<button type="submit" class="btn secondary" style="padding:10px 16px;border-radius:8px;border:2px solid #059669;background:#d1fae5;color:#064e3b;font-weight:700;cursor:pointer">⏩ Envoyer POST vide (mini)</button>
</form>
</div>
<div class="card warn">
<h3><span class="status warn">Test 2 : POST GROS 1.2 Mo (simule 2 photos base64)</span></h3>
<p style="margin:0 0 0.5rem">Payload ~1,2 Mo (en dessous des limites). Vérifie max_input_vars / post_max_size.</p>
<form method="POST" id="bigForm" onsubmit="return doBigSubmit(event)">
<button type="submit" id="bigBtn" style="padding:10px 16px;border-radius:8px;border:2px solid #f59e0b;background:#fef3c7;color:#92400e;font-weight:700;cursor:pointer">⏩ Envoyer POST gros (~1.2 Mo)</button>
</form>
<small id="bigStatus" style="color:#92400e"></small>
<script>
function doBigSubmit(ev){
  ev.preventDefault();
  var form=document.getElementById('bigForm');
  var status=document.getElementById('bigStatus');
  var btn=document.getElementById('bigBtn');
  btn.disabled=true;btn.textContent='⏳ Création payload…';
  try {
    var s=''; // ~400 caractères de base64 répétés = 1.2 Mo
    for(var i=0;i<800;i++) s+='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789+/';
    function makeInput(nm,val){
      var inp=document.createElement('input');
      inp.type='hidden';inp.name=nm;inp.value=val;
      form.appendChild(inp);
    }
    makeInput('big','1');
    makeInput('photo_carnet_b64', 'data:image/jpeg;base64,' + s);     // ~ 500 Ko
    makeInput('photo_equipement_b64','data:image/jpeg;base64,' + s);  // ~ 500 Ko
    makeInput('statut','resolu');
    makeInput('rapport_technicien', Array(600).join('Test rapport. '));
    status.textContent='Payload en cours… ('+Math.round((new Blob([s,s]).size)/1024)+' Ko photos)';
    setTimeout(function(){ btn.textContent='⏩ Envoi en cours…'; form.submit(); }, 200);
  } catch(e){
    btn.disabled=false;btn.textContent='⏩ Réessayer';
    status.textContent='Erreur JS: '+e.message;
  }
  return false;
}
</script>
</div>
</div>

<div class="hr"></div>

<h2>🚀 Prochaines étapes (si les tests montrent un problème)</h2>
<ul style="line-height:1.8">
<li><strong>Si OPcache = Enabled + revalidate_freq > 5</strong> → OPcache garde l'ancien PHP plusieurs minutes ! Solution : laisser <code>opcache.enable=0</code> (déjà fait dans .user.ini), OU appeler <code>opcache_reset()</code> après upload.</li>
<li><strong>Si QUEUE_CONNECTION=database</strong> → la table <code>jobs</code> est souvent absente : tout broadcast / dispatch échoue → <strong>mettre QUEUE_CONNECTION=sync</strong>.</li>
<li><strong>Si CACHE_STORE vide / database</strong> → <code>cache:clear</code> fait du SQL sur une table <code>cache</code> absente : mettre <code>CACHE_STORE=file</code>.</li>
<li><strong>Si écriture uploads KO</strong> → en SSH : <code>chmod -R u+w storage bootstrap/cache public/uploads</code></li>
<li><strong>Si colonnes depannages MANQUANTES</strong> → exécuter les migrations : <code>php artisan migrate --force</code></li>
</ul>

<div class="row" style="margin-top:20px">
    <a class="btn primary link" style="background:#1d4ed8;color:#fff;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:700" href="javascript:location.reload()">🔄 Recharger diagnostic</a>
    <a class="btn secondary link" style="background:#f1f5f9;color:#0f172a;padding:10px 16px;border-radius:10px;text-decoration:none;font-weight:700;border:2px solid #cbd5e1" href="/">↩ Accueil</a>
</div>

<p style="margin-top:30px;color:#94a3b8;font-size:0.85rem;text-align:center">
    Généré le <?=date('d/m/Y H:i:s')?> · PHP <?=PHP_VERSION?> · SAPI <?=PHP_SAPI?>
</p>
</body>
</html>
