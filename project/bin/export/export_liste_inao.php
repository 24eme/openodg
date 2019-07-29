<?php
function generateHash($datas) {
    $date = str_replace('-', '', $datas[0]);
    $type = $datas[7];
    $statut = $datas[1];
    $id = $datas[2];
    $produit = $datas[9];
    return $date.'-'.$id.'-'.$type.'-'.$statut.'-'.$produit;
}

if (!isset($argv[1]) ||
    empty($argv[1])) {
    echo "Bad Script Call";
    return;
}

$csv = $argv[1];
if (!file_exists($csv)) {
    echo "$csv Not Found";
    return;
}

$historique = array();
if (($handle = fopen($csv, "r")) !== false) {
    while (($datas = fgetcsv($handle, 0, ";")) !== false) {
        if (count($datas) != 12) {
            continue;
        }
	if (!preg_match("/^[0-9]+/", $datas[0])) {
	    continue;
	}
        $hash = generateHash($datas);
        $historique[$hash] = new DateTime($datas[0]);
    }
    fclose($handle);
}
ksort($historique);

$dates = array();
foreach ($historique as $h => $d) {
    $tabH = explode('-', $h);
    $key = $tabH[1].'-'. $tabH[2].'-'. $tabH[4];
    if (!isset($dates[$key])) {
        $dates[$key] = array('depot' => null, 'enregistrement' => null, 'decision' => null);
    }
    if ($tabH[3] == 'COMPLET') {
        $dates[$key]['depot'] = $d;
    }
    if ($tabH[3] == 'ENREGISTREMENT') {
        $dates[$key]['enregistrement'] = $d;
    }
    if (strpos($tabH[3], 'VALIDE') !== false) {
        $dates[$key]['decision'] = $d;
    }
}
unset($historique);
$toUnset = array();
foreach ($dates as $key => $d) {
    if (!$d['decision']) {
        $toUnset[] = $key;
    }
}
foreach ($toUnset as $key) {
    unset($dates[$key]);
}

$etablissements = array();
$config = array();
$confFile = dirname(__FILE__).'/../config.inc';
if (file_exists($confFile)) {
if (($handle = fopen($confFile, "r")) !== false) {
    while (($line = fgets($handle)) !== false) {
        if (strpos($line, "COUCHDBDOMAIN") === 0) {
            $tab = explode('=', $line);
            $config['domaine'] = str_replace(array(PHP_EOL, ' '), '', $tab[1]);
        }
        if (strpos($line, "COUCHDBPORT") === 0) {
            $tab = explode('=', $line);
            $config['port'] = str_replace(array(PHP_EOL, ' '), '', $tab[1]);
        }
        if (strpos($line, "COUCHDBBASE") === 0) {
            $tab = explode('=', $line);
            $config['base'] = str_replace(array(PHP_EOL, ' '), '', $tab[1]);
        }
    }
    fclose($handle);
}
}
if (count($config) != 3) {
    $config = null;
}

echo "Libelle Appellation;Date depot DI;Date Enregistrement DI;N CVI;N SIRET;Cle identite;Nom ou raison sociale de l'operateur;Adresse 1;Adresse 2;Adresse 3;CP;Commune;Telephone;Telecopie;Email;Activité;Etat de l'habilitation;Date de décision;Observations\n";
if (($handle = fopen($csv, "r")) !== false) {
    while (($datas = fgetcsv($handle, 0, ";")) !== false) {
        if (count($datas) != 12) {
            continue;
        }
	if (!preg_match("/^[0-9]+/", $datas[0])) {
            continue;
        }
        if ($datas[1] != 'VALIDE') {
            continue;
        }
        $types = explode(',', $datas[11]);

        foreach($types as $type) {
            $key = $datas[2].'-'. $datas[7].'-'. $datas[9];

            if (!isset($dates[$key])) {
                continue;
            }

            $depot = $dates[$key]['depot'];
            $date_depot = "";
            if ($depot) {
                $date_depot = $depot->format('d/m/Y');
            }
            $enregistrement = $dates[$key]['enregistrement'];
            $date_enregistrement = "";
            if ($enregistrement) {
                $date_enregistrement = $enregistrement->format('d/m/Y');
            }
            $decision = $dates[$key]['decision'];
            $date_decision = "";
            if ($decision) {
                $date_decision = $decision->format('d/m/Y');
            }

            if ($config && !isset($etablissements[$datas[2]])) {
                $content = file_get_contents("http://".$config['domaine'].":".$config['port']."/".$config['base']."/ETABLISSEMENT-".$datas[2]);
                if ($content !== false) {
                    $etablissements[$datas[2]] = json_decode(file_get_contents("http://".$config['domaine'].":".$config['port']."/".$config['base']."/ETABLISSEMENT-".$datas[2]));
                }
            }

            if (!$etablissement = $etablissements[$datas[2]]) {
                continue;
            }

            $adresses = explode(' - ', str_replace(array('"',','),array('',''), $etablissement->siege->adresse));
            $a = (isset($adresses[0]))? $adresses[0] : "";
            $a_comp = (isset($adresses[1]))? $adresses[1] : "";
            $a_comp1 = (isset($adresses[2]))? $adresses[2] : "";

            echo $datas[10].";".$date_depot.";".$date_enregistrement.";".$etablissement->cvi.";".$etablissement->siret.";".$datas[2].";".$etablissement->raison_sociale.";".$a.";".$a_comp.";".$a_comp1.";";
            echo $etablissement->siege->code_postal.";".$etablissement->siege->commune.";".$etablissement->telephone_bureau.";".$etablissement->fax.";".$etablissement->email.";".$type.";".$datas[7].";".$date_decision.";".$datas[4]."\n";
        }
    }
    fclose($handle);
}
