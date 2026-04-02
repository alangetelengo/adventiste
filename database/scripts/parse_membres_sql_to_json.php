<?php

/**
 * Usage: php database/scripts/parse_membres_sql_to_json.php [chemin/vers/membres.sql] [sortie.json]
 * Par défaut : lit database/imports/membres.sql → database/data/membres_export.json
 */

declare(strict_types=1);

function extractInsertBodies(string $sql, string $table): array
{
    $pattern = '/INSERT INTO `'.$table.'`\s+[^)]+\)\s+VALUES\s*/is';
    if (! preg_match_all($pattern, $sql, $matches, PREG_OFFSET_CAPTURE)) {
        return [];
    }
    $chunks = [];
    foreach ($matches[0] as $idx => $m) {
        $start = $m[1] + strlen($m[0]);
        $nextInsert = preg_match($pattern, $sql, $nm, PREG_OFFSET_CAPTURE, $start + 1);
        $end = $nextInsert ? $nm[0][1] : strlen($sql);
        $body = trim(substr($sql, $start, $end - $start));
        if (preg_match('/\r?\n\r?\n--/', $body, $m, PREG_OFFSET_CAPTURE)) {
            $body = substr($body, 0, $m[0][1]);
        }
        $body = rtrim($body, " \t\n\r,;");
        $chunks[] = $body;
    }

    return $chunks;
}

function splitValueTuples(string $valuesBody): array
{
    $rows = [];
    $depth = 0;
    $start = 0;
    $len = strlen($valuesBody);
    for ($i = 0; $i < $len; $i++) {
        $ch = $valuesBody[$i];
        if ($ch === '(') {
            if ($depth === 0) {
                $start = $i + 1;
            }
            $depth++;
        } elseif ($ch === ')') {
            $depth--;
            if ($depth === 0) {
                $rows[] = substr($valuesBody, $start, $i - $start);
            }
        }
    }

    return $rows;
}

function splitFields(string $rowInner): array
{
    $fields = [];
    $cur = '';
    $inString = false;
    $n = strlen($rowInner);
    for ($i = 0; $i < $n; $i++) {
        $c = $rowInner[$i];
        if ($inString) {
            if ($c === '\\' && $i + 1 < $n) {
                $cur .= $rowInner[$i + 1];
                $i++;

                continue;
            }
            if ($c === "'") {
                $inString = false;
                $fields[] = $cur;
                $cur = '';
                while ($i + 1 < $n && ($rowInner[$i + 1] === ' ' || $rowInner[$i + 1] === "\n" || $rowInner[$i + 1] === "\r" || $rowInner[$i + 1] === "\t" || $rowInner[$i + 1] === ',')) {
                    $i++;
                }

                continue;
            }
            $cur .= $c;

            continue;
        }
        if ($c === ' ' || $c === "\n" || $c === "\r" || $c === "\t") {
            continue;
        }
        if ($c === "'") {
            $inString = true;
            $cur = '';

            continue;
        }
        if (substr($rowInner, $i, 4) === 'NULL') {
            $fields[] = null;
            $i += 3;
            while ($i + 1 < $n && ($rowInner[$i + 1] === ' ' || $rowInner[$i + 1] === "\n" || $rowInner[$i + 1] === "\r" || $rowInner[$i + 1] === "\t" || $rowInner[$i + 1] === ',')) {
                $i++;
            }

            continue;
        }
        $j = $i;
        while ($j < $n && $rowInner[$j] !== ',') {
            $j++;
        }
        $token = trim(substr($rowInner, $i, $j - $i));
        if ($token === '') {
            $i = $j;

            continue;
        }
        $fields[] = is_numeric($token) && ! str_starts_with($token, '0') ? (int) $token : $token;
        $i = $j;
    }

    return $fields;
}

$src = $argv[1] ?? dirname(__DIR__).'/imports/membres.sql';
$out = $argv[2] ?? dirname(__DIR__).'/data/membres_export.json';

if (! is_readable($src)) {
    fwrite(STDERR, "Fichier introuvable ou illisible : {$src}\n");

    exit(1);
}

$sql = file_get_contents($src);
$bodies = extractInsertBodies($sql, 'membres');
if ($bodies === []) {
    fwrite(STDERR, "Aucun INSERT INTO `membres` trouvé.\n");

    exit(1);
}

$all = [];
foreach ($bodies as $body) {
    foreach (splitValueTuples($body) as $tuple) {
        $all[] = splitFields($tuple);
    }
}

$expectedCols = 33;
$mapped = [];
foreach ($all as $idx => $f) {
    if (count($f) < $expectedCols) {
        fwrite(STDERR, 'Ligne '.($idx + 1).' : '.count($f)." champs (attendu {$expectedCols})\n");

        exit(1);
    }
    $mapped[] = [
        'source_id' => (int) $f[0],
        'source_eglise_id' => (int) $f[1],
        'numero_membre' => $f[2],
        'nom' => $f[3],
        'prenom' => $f[4],
        'date_naissance' => $f[5],
        'lieu_naissance' => $f[6],
        'nom_pere' => $f[7],
        'nom_mere' => $f[8],
        'sexe' => $f[9],
        'situation_matrimoniale' => $f[10],
        'date_mariage' => $f[11],
        'conjoint' => $f[12],
        'nationalite' => $f[13],
        'adresse' => $f[14],
        'ville' => $f[15],
        'code_postal' => $f[16],
        'telephone' => $f[17],
        'telephone2' => $f[18],
        'email' => $f[19],
        'profession' => $f[20],
        'formation' => $f[21],
        'famille_id' => $f[22],
        'mode_adhesion' => $f[23],
        'date_adhesion' => $f[24],
        'date_bapteme' => $f[25],
        'officiant_bapteme' => $f[26],
        'statut' => $f[27],
        'notes' => $f[28],
        'photo' => $f[29],
        'created_at' => $f[30],
        'updated_at' => $f[31],
        'deleted_at' => $f[32],
    ];
}

$dir = dirname($out);
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

file_put_contents($out, json_encode($mapped, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo count($mapped)." lignes → {$out}\n";
