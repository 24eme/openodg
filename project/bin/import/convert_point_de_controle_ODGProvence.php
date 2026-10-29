hist#!/usr/bin/env php
<?php

/**
 * Script de conversion CSV -> YAML pour les points de contrôle
 * Usage: php generate_yaml.php <fichier_csv>
 */

// Vérifier les arguments
if ($argc < 2) {
    fprintf(STDERR, "Erreur: Veuillez spécifier un fichier CSV en argument.\n");
    fprintf(STDERR, "Usage: php %s <fichier_csv>\n", $argv[0]);
    exit(1);
}

$csvFile = $argv[1];

// Vérifier que le fichier existe
if (!file_exists($csvFile)) {
    fprintf(STDERR, "Erreur: Le fichier '%s' n'existe pas.\n", $csvFile);
    exit(1);
}

// Lire le fichier CSV
$lines = file($csvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if ($lines === false) {
    fprintf(STDERR, "Erreur: Impossible de lire le fichier '%s'.\n", $csvFile);
    exit(1);
}

// La première ligne contient les en-têtes
$headers = str_getcsv($lines[0], ';');
array_shift($headers); // Retirer la première colonne vide (avant "Code")

// Indices des colonnes importantes
$codeIndex = array_search('Code', $headers);
$pointControleIndex = array_search('Points_de_contrôle', $headers);
$libelleIndex = array_search('Libellés des manquements', $headers);
$typeIndex = array_search('Type', $headers);
$planActionIndex = array_search('Plan d’action formalisé à fournir', $headers);
$mesureTraitement1Index = array_search('Mesure de traitement 1', $headers);
$modaliteVerif1Index = array_search('Modalité de vérification du retour à la conformité 1', $headers);
$mesureOdgAvpiIndex = array_search('Mesure suivi ODG ou transmission AVPI', $headers);
$delaisIndex = array_search('Délais opérateur', $headers);
$lieuxControleIndex = array_search('Lieux du contrôle', $headers); // Nouvelle colonne

// Vérifier que les colonnes essentielles existent
$requiredColumns = [
    'Code' => $codeIndex,
    'Points_de_contrôle' => $pointControleIndex,
    'Libellés des manquements' => $libelleIndex,
    'Type' => $typeIndex,
    'Lieux du contrôle' => $lieuxControleIndex
];

foreach ($requiredColumns as $columnName => $index) {
    if ($index === false) {
        fprintf(STDERR, "Erreur: Colonne '%s' non trouvée dans le CSV.\n", $columnName);
        fprintf(STDERR, "Colonnes disponibles: %s\n", implode(', ', $headers));
        exit(1);
    }
}

// Structure de données
$pointsDeControle = [];

// Parcourir les lignes à partir de la deuxième
for ($i = 1; $i < count($lines); $i++) {
    $row = str_getcsv($lines[$i], ';');

    // Vérifier que la ligne a le bon nombre de colonnes
    if (count($row) <= 1) {
        continue;
    }

    // La première colonne est vide, on commence à l'index 1
    $code = isset($row[1]) ? trim($row[1]) : '';
    $pointControle = isset($row[2]) ? trim($row[2]) : '';
    $libelle = isset($row[3]) ? trim($row[3]) : '';
    $type = isset($row[4]) ? trim($row[4]) : '';
    $planAction = isset($row[5]) ? trim($row[5]) : '';
    $mesureTraitement1 = isset($row[6]) ? trim($row[6]) : '';
    $mesureOdgAvpi = isset($row[8]) ? trim($row[8]) : '';
    $delais = isset($row[9]) ? trim($row[9]) : '';
    $modaliteVerif1 = isset($row[10]) ? trim($row[10]) : '';

    // Nouvelle colonne: Lieux du contrôle (dernière colonne)
    $lieuxControle = '';
    if (isset($row[count($row) - 1])) {
        $lieuxControle = trim($row[count($row) - 1]);
    }

    // Ignorer les lignes sans code ou sans point de contrôle
    if (empty($code) || empty($pointControle)) {
        continue;
    }

    // Créer une clé normalisée pour le point de contrôle
    $pointControleKey = str_replace(' ', '_', strtolower($pointControle));
    $pointControleKey = preg_replace('/[^a-z0-9_]/', '', $pointControleKey);

    // Cas spéciaux pour les clés
    $specialCases = [
        'cmmp' => 'CMMP',
        'cmmp_des_parcelles_irriguees' => 'CMMP des parcelles irriguees',
        'di' => 'DI',
        'h_di' => 'H DI'
    ];

    if (isset($specialCases[$pointControleKey])) {
        $pointControleKey = $specialCases[$pointControleKey];
    }

    // S'assurer que le point de contrôle existe
    if (!isset($pointsDeControle[$pointControleKey])) {
        $pointsDeControle[$pointControleKey] = [
            'libelle' => str_replace('_', ' ', $pointControle),
            'constats' => []
        ];
    }

    // Créer une clé unique pour le constat (certains codes se répètent)
    $constatKey = $code;
    $counter = 1;
    while (isset($pointsDeControle[$pointControleKey]['constats'][$constatKey])) {
        $constatKey = $code . chr(96 + $counter); // RTM046b, RTM046c, etc.
        $counter++;
    }

    // Ajouter le constat avec les nouveaux champs terrain/documentaire
    $pointsDeControle[$pointControleKey]['constats'][$constatKey] = [
        'libelle' => $libelle,
        'types' => [$type],
        'plan' => strtolower($planAction) === 'oui',
        'mesure_traitement' => $mesureTraitement1,
        'modalite_verif_retour_conformite' => $modaliteVerif1,
        'mesure_odg_ou_avpi' => $mesureOdgAvpi,
        'delais' => $delais,
        'terrain' => false,  // Valeur par défaut
        'documentaire' => false // Valeur par défaut
    ];

    // Définir le booléen en fonction du lieu de contrôle
    $lieuxControleLower = strtolower($lieuxControle);
    if ($lieuxControleLower === 'terrain') {
        $pointsDeControle[$pointControleKey]['constats'][$constatKey]['terrain'] = true;
    } elseif ($lieuxControleLower === 'documentaire') {
        $pointsDeControle[$pointControleKey]['constats'][$constatKey]['documentaire'] = true;
    }
}

// Fusionner les types et les lieux de contrôle pour les codes en double
foreach ($pointsDeControle as $pcKey => &$pc) {
    $mergedConstats = [];
    foreach ($pc['constats'] as $code => $constat) {
        $baseCode = preg_replace('/[a-z]$/', '', $code); // Enlever le suffixe a, b, c...

        if (!isset($mergedConstats[$baseCode])) {
            $mergedConstats[$baseCode] = $constat;
        } else {
            // Fusionner les types
            $mergedConstats[$baseCode]['types'] = array_unique(
                array_merge($mergedConstats[$baseCode]['types'], $constat['types'])
            );

            // Fusionner les lieux de contrôle (OR logique)
            $mergedConstats[$baseCode]['terrain'] = $mergedConstats[$baseCode]['terrain'] || $constat['terrain'];
            $mergedConstats[$baseCode]['documentaire'] = $mergedConstats[$baseCode]['documentaire'] || $constat['documentaire'];

            // Garder la première occurrence des autres champs (ils sont identiques)
        }
    }
    $pc['constats'] = $mergedConstats;
}

// Re-indexer les types pour qu'ils soient des listes propres
foreach ($pointsDeControle as &$pc) {
    foreach ($pc['constats'] as &$constat) {
        $constat['types'] = array_values($constat['types']);
        // Trier les types pour avoir "Habilitation" avant "Suivi"
        sort($constat['types']);
    }
}

// Trier les points de contrôle par ordre alphabétique
ksort($pointsDeControle);

// Construire la structure finale
$output = [
    'all' => [
        'configuration' => [
            'controle' => [
                'points_de_controle' => $pointsDeControle
            ]
        ]
    ]
];

/**
 * Convertit un tableau PHP en YAML
 *
 * @param array $array Le tableau à convertir
 * @param int $indent Le niveau d'indentation actuel
 * @return string Le YAML généré
 */
function arrayToYaml($array, $indent = 0) {
    $yaml = '';
    $spaces = str_repeat('  ', $indent);

    foreach ($array as $key => $value) {
        if (is_array($value)) {
            if (array_keys($value) === range(0, count($value) - 1)) {
                // Liste indexée
                $yaml .= $spaces . "$key:\n";
                foreach ($value as $item) {
                    if (is_array($item)) {
                        $yaml .= $spaces . "  - " . trim(arrayToYaml($item, $indent + 1)) . "\n";
                    } else {
                        $yaml .= $spaces . "  - " . yamlEscapeValue($item) . "\n";
                    }
                }
            } else {
                // Tableau associatif
                $yaml .= $spaces . "$key:\n";
                $yaml .= arrayToYaml($value, $indent + 1);
            }
        } else {
            // Valeur scalaire
            $yaml .= $spaces . "$key: " . yamlEscapeValue($value) . "\n";
        }
    }

    return $yaml;
}

/**
 * Échappe une valeur pour le format YAML
 *
 * @param mixed $value La valeur à échapper
 * @return string La valeur échappée
 */
function yamlEscapeValue($value) {
    if ($value === true) {
        return 'true';
    }
    if ($value === false) {
        return 'false';
    }

    if ($value === null || $value === '') {
        return '""';
    }

    if (is_numeric($value)) {
        return $value;
    }

    // Échapper les caractères spéciaux YAML
    if (strpos($value, ':') !== false ||
        strpos($value, '#') !== false ||
        strpos($value, "\n") !== false ||
        strpos($value, '[') !== false ||
        strpos($value, ']') !== false ||
        strpos($value, '{') !== false ||
        strpos($value, '}') !== false ||
        $value === '' ||
        $value[0] === ' ' ||
        $value[0] === '-') {

        // Échapper les guillemets doubles
        $value = str_replace('"', '\\"', $value);
        return '"' . $value . '"';
    }

    return $value;
}

// Générer le YAML
$yamlContent = arrayToYaml($output);

// Afficher sur la sortie standard
echo $yamlContent;

// Fin du script
exit(0);
?>
