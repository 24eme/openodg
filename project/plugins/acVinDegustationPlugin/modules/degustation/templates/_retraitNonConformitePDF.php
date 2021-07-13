<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF'); ?>
<?php $adresse = sfConfig::get('app_degustation_courrier_adresse'); ?>
<style>
</style>

<br/><br/>
<table style="width:1100px;font-size:13px;padding-left:400px;" >
  <tr>
    <td><strong><?php echo $etablissement->raison_sociale ?></strong></td>
  </tr>
  <tr>
    <td><strong><?php echo $etablissement->adresse ?></strong></td>
  </tr>
  <tr>
    <td><strong><?php echo $etablissement->code_postal .' '.$etablissement->commune ?></strong></td>
  </tr>
</table>
<br/>

<br/>
<br/>

<table style="font-size:12px;">

  <tr>
    <td>Réf : <?php echo $etablissement->cvi[0];echo $etablissement->cvi[1] .' '. $etablissement->cvi[2],$etablissement->cvi[3],$etablissement->cvi[4].' '.$etablissement->cvi[5],$etablissement->cvi[6],$etablissement->cvi[7],$etablissement->cvi[8],$etablissement->cvi[9] ?></td>
  </tr>
</table>
<br/>
<br/>
<table style="font-size:12px;"><tr><td style="width: 324px;"><?php echo 'Aix-en-Provence, le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table style="font-size:12px;"><tr><td>Objet : Levée d’un manquement relevé sur le Contrôle organoleptique du produit de l’IGP Pays des Bouches du Rhône, réf. NC Bouches du Rhône - 1416 Aix </td></tr></table>
<br/><br/><br/>


<table style="font-size:12px;">
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Suite à votre réponse en date du <?php echo date('d/m/Y',strtotime($degustation->date)) ?> et de l'action corrective proposée, nous avons le plaisir de vous informer que la Non-conformité citée en objet a été levée le <?php echo date('d/m/Y',strtotime($degustation->date)) ?>.</td></tr>
</table><br/>

<?php foreach($degustation->getLots() as $lot): ?>
  <?php if($lot->statut == "NON_CONFORME" && $lot->declarant_identifiant == $etablissement->identifiant && $lot->numero_dossier == $lot_dossier): ?>
        <?php $Newlot = $lot  ?>
  <?php endif; ?>
<?php endforeach; ?>
<p><strong>Lot Concerné par la Non-Conformité : <?php echo (int)$Newlot->numero_archive ?></strong></p>

<table border="1" cellpadding=0 cellspacing=0 style="text-align: center;font-size:12px;">
  <tr>
    <th style="font-weight:bold">N° de lot OPERATEUR</th>
    <th style="font-weight:bold">Logement<br/>(Cuve)</th>
    <th style="font-weight:bold">IGP<br/>Couleur</th>
    <th style="font-weight:bold">Cépage</th>
    <th style="font-weight:bold">Volume<br/>(HI)</th>
    <th style="font-weight:bold">Décision/<br/>Observation</th>
  </tr>
  <tr>
    <td><?php echo (int)$Newlot->numero_archive ?></td>
    <td><?php echo (int)$Newlot->numero_logement_operateur ?></td>
    <td><?php echo $Newlot->produit_libelle ?></td>
    <td><?php echo $Newlot->details ?></td>
    <td><?php echo sprintf("%.2f", $Newlot->volume) ?></td>
    <td><?php echo $Newlot->observation ?></td>
  </tr>
</table>

<p style="font-size:12px;">Vous souhaitant bonne réception de ces éléments, je vous prie d’agréer, Madame, Monsieur, nos plus sincères et cordiales salutations. </p>

<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
<br/>

<table style="text-align:center;font-size:10px;">
  <tr>
    <td><?php echo $adresse['raison_sociale'] ?></td>
  </tr>
  <tr>
    <td><?php echo $adresse['adresse'].' - '. $adresse['cp_ville'] .'-'. $adresse['telephone']  ?> </td>
  </tr>
</table>
