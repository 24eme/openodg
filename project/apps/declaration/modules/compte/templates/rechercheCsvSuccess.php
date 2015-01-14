<?php
printf("\xef\xbb\xbf");//UTF8 BOM (pour windows)
echo "#nom complet ; type ; raison sociale ; civilité ; nom ; prénom ; adresse ; code postal ; commune ; pays ; téléphone bureau ; téléphone mobile ; téléphone privé ; fax ; email ; cvi ;  siret ; statut ; date de création ; date d'archivage ; id contact ; attributs ; syndicats ; mots clés\n";
$allTypeCompte = CompteClient::getInstance()->getAllTypesCompteWithLibelles();
foreach ($results as $res) {
  $data = $res->getData()->getRawValue();
  
  echo '"'.$data['nom_a_afficher']. '";';
  echo '"'.$allTypeCompte[$data['type_compte']]. '";';
  echo '"'.$data['raison_sociale']. '";';
  echo '"'.$data['civilite']. '";';
  echo '"'.$data['prenom']. '";';
  echo '"'.$data['nom']. '";';
  echo '"'.$data['adresse']. '";';
  echo '"'.$data['code_postal']. '";';
  echo '"'.$data['commune']. '";';
  echo '"'.$data['pays']. '";';
  echo '"'.$data['telephone_bureau']. '";';
  echo '"'.$data['telephone_mobile']. '";';
  echo '"'.$data['telephone_prive']. '";';
  echo '"'.$data['fax']. '";';
  echo '"'.$data['email']. '";';
  echo '"'.$data['cvi']. '";';
  echo '"'.$data['siret']. '";';
  echo '"'.$data['statut']. '";';  
  echo '"'.$data['date_creation']. '";';  
  echo '"'.$data['date_archivage']. '";';  
  echo '"'.$data['_id']. '";';  
  echo '"'.implode(", ", $data['infos']['attributs']). '";';  
  echo '"'.implode(", ", $data['infos']['syndicats']). '";';  
  echo '"'.implode(", ", $data['infos']['manuels']). '";';  
  echo "\n";
}
