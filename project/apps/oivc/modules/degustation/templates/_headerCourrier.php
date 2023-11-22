<?php use_helper("Date"); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Float'); ?>

<style>
    * {
        font-size: 12px;
    }
    th {
        font-weight: bold;
    }
</style>

<table class="small">
    <tr>
        <td style="text-align:center;"><small style="font-size: 11px;"><img src="file://<?php echo sfConfig::get('sf_web_dir').'/images/pdf/'; ?>logo_oivc.jpg" height="75"/><br/>
Sancerre - Pouilly - Menetou Salon - Quincy - Reuilly - Coteaux du Giennois - Chateaumeillant<br/>
9 route de Chavignol 18300 Sancerre<br/>
02 48 54 29 88 - 06 87 99 96 05 - 06 87 55 96 40 - spaulat.oivc@yahoo.fr christelleantoine18@yahoo.fr<br/>
</small>
</td>
    </tr>
</table>

<br/><br/>
<table style="width:1100px;padding-left:400px;" >
    <tr><td></td></tr>
    <tr><td><?php echo $courrier->declarant->raison_sociale ?></td></tr>
    <tr><td><?php echo $courrier->declarant->adresse ?></td></tr>
    <tr><td><?php echo $courrier->declarant->code_postal .' '.$courrier->declarant->commune ?></td></tr>
    <tr><td></td></tr>
    <tr><td>Le <?php echo $courrier->getDateFormat('d/m/Y') ?></td></tr>
</table>
<br/>
<br/>
<br/>

<strong>Objet : <?php echo $objet ?></strong>
<p></p>

<p>Madame, Monsieur,</p>
