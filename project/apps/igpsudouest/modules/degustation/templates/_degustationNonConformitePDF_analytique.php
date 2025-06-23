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

<table><tr><td><strong>Objet : Information de manquement grave relevé lors d'un contrôle interne analytique sur les conditions de production de l'IGP Comté Tolosan</strong></td></tr></table>
<br/><br/>

<table>
  <tr><td>Madame, Monsieur,</td></tr>
  <br/>
  <tr><td>Dans le cadre du contrôle interne analytique réalisé le <?php echo format_date($lot->prelevement_datetime, "P", "fr_FR"); ?> sur les échantillons prélevés dans votre cave, nous avons relevé le manquement grave suivant :</td></tr>
  <tr><td><strong><?php echo $lot->motif ?></strong></td></tr>
  <tr><td>Pour : <strong><?php echo showProduitCepagesLot($lot, false) ?> de <?php echo $lot->volume ?> hl</strong></td></tr>
  <br/>
  <tr><td>Il en résulte que <strong>ce lot de vin est non loyal et marchand</strong> et ne peut être commercialisé en IGP Comté Tolosan. Le plan de contrôle prévoit donc que l'opérateur doit procéder à <strong>son déclassement en VSIG, dans un délais de 10 jours.</strong> Sans réponse de votre part, nous serons dans l'obligation de communiquer ce manquement grave à notre organisme de contrôle externe, QUALISUD, qui procédera à un contrôle externe, qui vous sera facturé.</td></tr>
</table><br/><br/>

<table>
    <tr><td>Nous restons bien sûr à votre disposition pour tout complément d'information.</td></tr>
    <br/>
  <tr><td>Dans cette attente, veuillez accepter, Madame, Monsieur, nos plus sincères et cordiales salutations</td></tr><br/>
</table>

<br/><br/>
<br/><br/>
<br/><br/>
<table style="width:1100px;padding-left:400px;" >
  <tr><td><?php echo Organisme::getInstance(null, 'degustation')->getResponsable() ?></td></tr>
  <tr><td><?php if(file_exists(Organisme::getInstance(null, 'degustation')->getImageSignaturePath())): ?><img src="<?php echo Organisme::getInstance(null, 'degustation')->getImageSignaturePath() ?>"/><?php endif; ?></td></tr>
</table>
