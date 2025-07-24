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

<table style="width:1100px;" >
  <tr style="text-decoration: underline;"><td>Vos coordonnées :</td></tr>
  <tr><td>Email : <?php echo $etablissement->email ?></td></tr>
  <?php echo ($etablissement->telephone_bureau) ? '<tr><td>Bureau : '. $etablissement->telephone_bureau .'</td></tr>' : '' ?>
  <?php echo ($etablissement->telephone_mobile) ? '<tr><td>Mobile : '. $etablissement->telephone_mobile .'</td></tr>' : '' ?>
</table>

<br/>
<br/>
<table><tr><td style="width: 324px;"><?php echo 'Le ' . format_date(date('Y-m-d'), "P", "fr_FR"); ?></td></tr></table>
<br/><br/>

<table><tr><td><strong>Objet :</strong> Résultats contrôles organoleptiques <strong>absence de typicité cépage</strong></td></tr></table>
<br/><br/>


<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Lors de la séance de dégustation du <?php echo format_date($degustation->date, "P", "fr_FR"); ?>, le lot <?php showProduitCepagesLot($lot, false) ?> a fait l'objet d'une absence de typicité cépage revendiqué.</td></tr><br/>
  <tr><td>Il en résulte que <strong>ce lot est conforme en IGP Comté Tolosan (sans mention de cépage), mais il ne vous est pas possible de le commercialiser en IGP Comté Tolosan - <?php echo $lot->getCepagesLibelle(false); ?></strong> (toute transaction, expédition ou conditionnement, impossible), le temps nécessaire à un éventuel nouveau contrôle. Nous vous invitons à soumettre ce lot à un nouveau contrôle interne, si vous souhaitez absolument revendiquer le(s) cépage(s) <?php echo $lot->getCepagesLibelle(false); ?>.</td></tr><br/>
  <tr><td>N'hésitez donc pas à nous contacter dans les plus brefs délais pour tout complément d'information afin de trouver une issue favorable et rapide à la gestion de ce dossier.</td></tr>
  <tr><td>Nous nous tenons à votre disposition.</td></tr>
</table><br/><br/>

<table>
  <tr><td>Veuillez accepter, Madame, Monsieur, nos plus sincères et cordiales salutations.</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo Organisme::getInstance(null, 'degustation')->getResponsable() ?></td></tr>
  <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
