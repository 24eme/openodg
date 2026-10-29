<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper('Compte') ?>
<style>
<?php echo styleParcellaire(); ?>
</style>

<div><span class="h3">&nbsp;Exploitation&nbsp;</span></div>
<table class="table"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;">&nbsp;Nom : <i><?php echo $parcellaireManquant->declarant->raison_sociale ?></i></td>

                    <td><?php if ($parcellaireManquant->declarant->cvi): ?>N° CVI : <i><?php echo $parcellaireManquant->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
                </tr>
                <tr>
                    <td>&nbsp;Adresse : <i><?php echo $parcellaireManquant->declarant->adresse ?></i></td>
                    <td>N° SIRET : <i><?php echo formatSIRET($parcellaireManquant->declarant->siret); ?></i></td>
                </tr>
                <tr>
                    <td>&nbsp;Commune : <i><?php echo $parcellaireManquant->declarant->code_postal ?>, <?php echo $parcellaireManquant->declarant->commune ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Tel / Fax : <i><?php echo $parcellaireManquant->declarant->telephone ?> / <?php echo $parcellaireManquant->declarant->fax ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Email : <i><?php echo $parcellaireManquant->declarant->email ?></i></td>
                    <td></td>
                </tr>
            </table>
        </td></tr></table>
<br />
<br />
<i>Aucune parcelle n'a été déclarée pour cette année.</i>
<br />
<br />
