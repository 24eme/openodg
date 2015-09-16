<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper("Date"); ?>
<style>
<?php echo styleConstat(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
<table border="0">
    <tr>
        <td style="width: 420px;">&nbsp;Nom : <i><?php echo $constats->raison_sociale ?></i></td>
        <td>N° CVI : <i><?php echo $constats->cvi ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Adresse : <i><?php echo $constats->adresse ?></i></td>
        <td>SIRET : <i><?php echo $constats->getCompte()->siret ?></i></td>
    </tr>
    <tr>
        <td>&nbsp;Commune : <i><?php echo $constats->code_postal ?>, <?php echo $constats->commune ?></i></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;Tel / Fax : <i><?php echo $constats->getCompte()->telephone_prive ?> / <?php echo $constats->getCompte()->fax ?></i></td>
        <td></td>
    </tr>
    <tr>
        <td>&nbsp;Email : <i><?php echo $constats->email ?></i></td>
        <td></td>
    </tr>
</table>
</td></tr></table>
<br />
<div><span class="h3">&nbsp;Constats&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 200px;">&nbsp;Mention</th>         
        <th class="td" style="text-align: right; width: 440px;"><?php  echo $constat->type_vtsgn ?>&nbsp;&nbsp;</th>      
    </tr>
     <tr>
        <th class="th" style="text-align: left; width: 200px;">&nbsp;Produit</th>         
        <th class="td" style="text-align: right; width: 440px;"><?php  echo $constat->produit_libelle ?>&nbsp;&nbsp;</th>      
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 200px;">&nbsp;Dénomination</th>         
        <th class="td" style="text-align: right; width: 440px;"><?php  echo $constat->denomination_lieu_dit ?>&nbsp;&nbsp;</th>      
    </tr>
     <tr>
        <th class="th" style="text-align: left; width: 200px;">&nbsp;Volume</th>         
        <th class="td" style="text-align: right; width: 440px;"><?php  echo $constat->volume_obtenu ?>&nbsp;hl&nbsp;&nbsp;</th>      
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 200px;">&nbsp;T.A.V.N.</th>         
        <th class="td" style="text-align: right; width: 440px;"><?php  echo $constat->degre_potentiel_volume ?>&nbsp;°&nbsp;&nbsp;</th>      
    </tr>
</table>
<br />
<br />
<br />
<table cellspacing=0 cellpadding=0>
<tr><td class="tdH2Big"><span class="h2">Signature</span></td></tr>
</table>
<br />
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    
 <tr>
     <td class="td" style="text-align:left; width: 300px;"><?php echo tdStart() ?><img style="width: 300px;" src="<?php  echo $constat->signature_base64; ?>" /></td>
 </tr>
</table>