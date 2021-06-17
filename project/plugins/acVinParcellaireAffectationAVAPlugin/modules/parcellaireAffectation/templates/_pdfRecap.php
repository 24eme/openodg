<?php use_helper("Date"); ?>
<?php use_helper('ParcellaireAffectation') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper('Compte') ?>
<style>
<?php echo styleParcellaire(); ?>
</style>
<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $parcellaire->declarant->raison_sociale ?></i></td>

                    <td><?php if ($parcellaire->declarant->cvi): ?>N° CVI : <i><?php echo $parcellaire->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
                <tr>
                    <td>&nbsp;Adresse : <i><?php echo $parcellaire->declarant->adresse ?></i></td>
                    <td>N° SIRET : <i><?php echo formatSIRET($parcellaire->declarant->siret); ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $parcellaire->declarant->code_postal ?>, <?php echo $parcellaire->declarant->commune ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $parcellaire->declarant->telephone ?> / <?php echo $parcellaire->declarant->fax ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Email : <i><?php echo $parcellaire->declarant->email ?></i></td>
                    <td></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br /> 
<div><span class="h3">&nbsp;Récapitulatif&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: center; width: 359px;">&nbsp;Appellation <?php if(!$parcellaire->isParcellaireCremant()): ?>- Lieu <?php endif; ?>- Cépage</th>        
        <th class="th" style="text-align: center; width: 180px;">Commune</th>  
        <th class="th" style="text-align: center; width: 100px;">Surface</th>
    </tr>

    <?php foreach ($parcellesForRecap as $parcelleByLieuxCommuneAndCepage) : ?>
            <tr>
                <td class = "td" style = "text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelleByLieuxCommuneAndCepage->appellation_lieu_libelle.' - '.$parcelleByLieuxCommuneAndCepage->cepage_libelle; ?>&nbsp;</td>        
                <td class="td" style="text-align: center;">&nbsp;<?php echo $parcelleByLieuxCommuneAndCepage->commune ?>&nbsp;</td>       
               
                <td class="td" style="text-align:right;">&nbsp;<?php printf("%0.2f", $parcelleByLieuxCommuneAndCepage->total_superficie); ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
            </tr> 
    <?php endforeach; ?>
</table>  
<br />
<?php if($engagement && $parcellaire->hasVtsgn() && $parcellaire->validation): ?>
<br /> 
                <table border="0">
                        <tr>
                            <td><span style="font-family: Dejavusans">☒</span>&nbsp;Je m'engage à respecter les conditions de production des mentions VT/SGN et les modalités de contrôle liées.
                            </td>
                        </tr>
                </table>
           
    <br />
<?php endif; ?>
<br />