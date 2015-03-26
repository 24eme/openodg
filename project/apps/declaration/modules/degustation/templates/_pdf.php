<?php use_helper("Date"); ?>
<?php use_helper('Degustation') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<style>
<?php echo styleDegustation(); ?>
</style>
<br/>
<br/>
<br/>
<table border="0">
    <tr>
        <td style="width: 450px;" >&nbsp;</td>
        <td style="width: 300px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $operateur->raison_sociale ?>
        </td>
    </tr>
    <tr>
        <td style="width: 450px;" >&nbsp;</td>
        <td style="width: 300px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $operateur->adresse; ?>
        </td>
    </tr>
    <tr>
        <td style="width: 450px;" >&nbsp;</td>
        <td style="width: 300px; padding-right: 40px; font-weight: bolder; ">
            <?php echo $operateur->code_postal . ' ' . $operateur->commune; ?>
        </td>
    </tr>
</table>
<br/>
<br/>
<br/>
<table>
    <tr>
        <td style="width: 450px;" >&nbsp;</td>
        <td style="width: 300px; padding-right: 40px; font-weight: bolder; ">            
            <?php echo $degustation->lieu . ', le ' . date('d/m/Y'); ?>
        </td>
    </tr>
</table>

<br/>

<table>
    <tr>
        <td>N/Réf.: ???? <?php echo ($prelevement->type_courrier == DegustationClient::COURRIER_TYPE_OPE) ? 'OPE' : ''; ?></td>
    </tr>
    <tr>
        <td>Clé Identité : <?php echo $prelevement->anonymat_degustation; ?></td>
    </tr>
    <tr>
        <td >
            Objet : Gestion locale : dégustation conseil des <?php echo $degustation->appellation_libelle . ' ' . substr($degustation->validation, 0, 4); ?>
        </td>
    </tr>
</table>
<br/>
<br/>
<table>
    <tr>
        <td>Madame, Monsieur,</td>
    </tr>
    <tr>
        <td>Vous avez présenter vos vins à la degustation conseil de la Gestion locale du <?php echo $degustation->appellation_libelle.' '.$prelevement->libelle; ?>. Celle-ci a eu lieu le <?php echo $operateur->date; ?>.
        </td>
    </tr>
    <tr>
        <td>Les experts dégustateurs ont fait les commentaires suivants  sur votre vin : </td>
    </tr>    
</table>
<br/>
<br/>
