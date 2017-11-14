<?php
$csv = "# id société ; nom complet ; type ; civilité ; nom ; prénom ; adresse ; adresse complémentaire ; code postal ; commune ; pays ; téléphone bureau ; téléphone mobile ; téléphone perso ; fax ; email ; commentaire ; nom groupe ; fonction ; type société ; société raison sociale ; société adresse ; société adresse complémentaire ; société code postal ; société commune ; société téléphone ; société fax ; société email; code de création \n";
$groupe = null;
if(isset($selected_typetags) && (count($selected_typetags->getRawValue()) == 1)){
  $tags = $selected_typetags->getRawValue();
  if(array_key_exists('groupes',$tags)){
    $groupe = $tags['groupes'][0];

  }
}

foreach ($results as $res) {
  $data = $res->getData();

  $societe_informations = $data['doc']['societe_informations'];
  $groupesAndFonction = CompteClient::getGroupesAndFonction($data['doc']['groupes'],$groupe);

  $csv .= '"'.preg_replace('/SOCIETE-/', '', $data['doc']['id_societe']). '";';
  $csv .= '"'.sfOutputEscaper::unescape($data['doc']['nom_a_afficher']). '";';
  $csv .= '"'.CompteClient::getInstance()->createTypeFromOrigines($data['doc']['origines']).'";';
  $csv .= '"'.$data['doc']['civilite']. '";';
  $csv .= '"'.sfOutputEscaper::unescape($data['doc']['prenom']). '";';
  $csv .= '"'.sfOutputEscaper::unescape($data['doc']['nom']). '";';
  $csv .= '"'.sfOutputEscaper::unescape($data['doc']['adresse']). '";';
  $csv .= '"'.sfOutputEscaper::unescape($data['doc']['adresse_complementaire']). '";';
  $csv .= '"'.$data['doc']['code_postal']. '";';
  $csv .= '"'.sfOutputEscaper::unescape($data['doc']['commune']). '";';
  $csv .= '"'.$data['doc']['pays']. '";';
  $csv .= '"'.$data['doc']['telephone_bureau']. '";';
  $csv .= '"'.$data['doc']['telephone_mobile']. '";';
  $csv .= '"'.$data['doc']['telephone_perso']. '";';
  $csv .= '"'.$data['doc']['fax']. '";';
  $csv .= '"'.$data['doc']['email']. '";';
  $csv .= '"'.$data['doc']['commentaire']. '";';
  if($groupe){
    $csv .= '"'.$groupesAndFonction['nom']. '";';
    $csv .= '"'.$groupesAndFonction['fonction']. '";';
  }else{
      $csv .= '"";';
      $csv .= '"'.$data['doc']['fonction']. '";';;
  }

  $csv .= '"'.$societe_informations['type']. '";';
  $csv .= '"'.sfOutputEscaper::unescape($societe_informations['raison_sociale']). '";';
  $csv .= '"'.sfOutputEscaper::unescape($societe_informations['adresse']). '";';
  $csv .= '"'.sfOutputEscaper::unescape($societe_informations['adresse_complementaire']). '";';
  $csv .= '"'.$societe_informations['code_postal']. '";';
  $csv .= '"'.sfOutputEscaper::unescape($societe_informations['commune']). '";';
  $csv .= '"'.$societe_informations['telephone']. '";';
  $csv .= '"'.$societe_informations['fax']. '";';
  $csv .= '"'.$societe_informations['email']. '";';
  $csv .= '"'.(preg_match("/\{TEXT\}/", $data['doc']['mot_de_passe'])) ? str_replace("{TEXT}", "", $data['doc']['mot_de_passe']) : null . '"';
  $csv .= "\n";
}
echo utf8_decode($csv);
