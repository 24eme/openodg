<?php
use_helper('Csv');

printf("\xef\xbb\xbf");//UTF8 BOM (pour windows)
echo "#nom complet ; type ; raison sociale ; civilité ; nom ; prénom ; adresse complement destinataire ; adresse ; adresse complément lieu; code postal ; commune ; pays ; téléphone bureau ; téléphone mobile ; téléphone privé ; fax ; email ; cvi ;  siret ; statut ; date de création ; date d'archivage ; identifiant interne ; id ; attributs ; syndicats ; mots clés\n";
$allTypeCompte = CompteClient::getInstance()->getAllTypesCompteWithLibelles();
foreach ($results as $res) {
  $data = $res->getData()->getRawValue();
  
  echo '"' . escapeCSVValue($data['nom_a_afficher']) . '";';
  echo '"' . escapeCSVValue($allTypeCompte[$data['type_compte']]) . '";';
  echo '"' . escapeCSVValue($data['raison_sociale']) . '";';
  echo '"' . escapeCSVValue($data['civilite']) . '";';
  echo '"' . escapeCSVValue($data['prenom']) . '";';
  echo '"' . escapeCSVValue($data['nom']) . '";';
  echo '"' . escapeCSVValue($data['adresse_complement_destinataire']) . '";';
  echo '"' . escapeCSVValue($data['adresse']) . '";';
  echo '"' . escapeCSVValue($data['adresse_complement_lieu']) . '";';
  echo '"' . escapeCSVValue($data['code_postal']) . '";';
  echo '"' . escapeCSVValue($data['commune']) . '";';
  echo '"' . escapeCSVValue($data['pays']) . '";';
  echo '"' . escapeCSVValue($data['telephone_bureau']) . '";';
  echo '"' . escapeCSVValue($data['telephone_mobile']) . '";';
  echo '"' . escapeCSVValue($data['telephone_prive']) . '";';
  echo '"' . escapeCSVValue($data['fax']) . '";';
  echo '"' . escapeCSVValue($data['email']) . '";';
  echo '"' . escapeCSVValue($data['cvi']) . '";';
  echo '"' . escapeCSVValue($data['siret']) . '";';
  echo '"' . escapeCSVValue($data['statut']) . '";';  
  echo '"' . escapeCSVValue($data['date_creation']) . '";';  
  echo '"' . escapeCSVValue($data['date_archivage']) . '";';  
  echo '"' . escapeCSVValue($data['identifiant_interne']) . '";';  
  echo '"' . escapeCSVValue($data['_id']) . '";';  
  echo '"' . escapeCSVValue(implode(", ", $data['infos']['attributs'])) . '";';  
  echo '"' . escapeCSVValue(implode(", ", $data['infos']['syndicats'])) . '";';  
  echo '"' . escapeCSVValue(implode(", ", $data['infos']['manuels'])) . '";';
  echo '"' . escapeCSVValue(str_replace("\n", '\n', $data['commentaires'])) . '";';  
  echo "\n";
}
