<?php use_helper("Date"); ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<style>
<?php echo styleTravauxMarc(); ?>
</style>

<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt">
    <tr>
        <td>
            <table border="0">
            <tr>
                <td style="width: 420px;">&nbsp;Nom : <i><?php echo $travauxmarc->declarant->raison_sociale ?></i></td>

                <td><?php if($travauxmarc->declarant->cvi): ?>N° CVI : <i><?php echo $travauxmarc->declarant->cvi ?></i><?php else: ?>&nbsp;<?php endif; ?></td>
            </tr>
            <tr>
                <td>&nbsp;Adresse : <i><?php echo $travauxmarc->declarant->adresse ?></i></td>
                <td>N° SIRET : <i><?php echo $travauxmarc->declarant->siret ?></i></td>
            </tr>
            <tr>
                <td>&nbsp;Commune : <i><?php echo $travauxmarc->declarant->code_postal ?> <?php echo $travauxmarc->declarant->commune ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Tel / Fax : <i><?php echo $travauxmarc->declarant->telephone ?> / <?php echo $travauxmarc->declarant->fax ?></i></td>
                <td></td>
            </tr>
            <tr>
                <td>&nbsp;Email : <i><?php echo $travauxmarc->declarant->email ?></i></td>
                <td></td>
            </tr>
            </table>
        </td>
    </tr>
</table>
