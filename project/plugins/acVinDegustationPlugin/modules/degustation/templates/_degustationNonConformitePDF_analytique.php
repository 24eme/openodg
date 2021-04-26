<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>

<style>
    table {
        font-size: 12px;
    }

    th {
        font-weight: bold;
    }
</style>

<table style="width:1100px;padding-left:400px;" >
  <tr><td></td></tr>
  <tr><td><?php echo $etablissement->raison_sociale ?></td></tr>
  <tr><td><?php echo $etablissement->adresse ?></td></tr>
  <tr><td><?php echo $etablissement->adresse_complementaire ?></td></tr>
  <tr><td><?php echo $etablissement->code_postal .' '.$etablissement->commune ?></td></tr>
</table>
<br/>

<br/>
<br/>
<table><tr><td style="width: 324px;"><?php echo 'Le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table><tr><td><strong>Objet : Information de manquement relevé lors d'un contrôle interne analytique</strong></td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Dans le cadre du contrôle interne analytique réalisé sur les échantillons prélevés dans votre cave, nous avons relevé le manquement suivant :</td></tr>
  <tr><td><strong>Manquement MAJEUR : <?php echo $lot->motif ?></strong></td></tr>
  <tr><td>Pour : <strong><?php echo showProduitCepagesLot($lot) ?> de <?php echo $lot->volume ?> hl</strong></td></tr>
  <tr><td>Conformément à la grille de traitement des manquements (CP03), la sanction est <strong>retrait du bénéfice de l'IGP pour le lot concerné</strong>.</td></tr>
</table><br/><br/>

<table>
  <tr><td>Veuillez accepter, Madame, Monsieur, nos plus sincères et cordiales salutations</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo $courrierInfos['responsable'] ?></td></tr>
  <tr><td><img src="<?php echo $courrierInfos['signature'] ?>"/></td></tr>
</table>
