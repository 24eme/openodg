<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
<?php use_helper("Date"); ?>
<?php
$compte = $constats->getCompte();
$telFaxRowDecr = "&nbsp;";
$telFaxRow = "";
if ($compte->telephone_bureau) {
    $telFaxRowDecr .= "Tél. Bureau&nbsp;/";
    $telFaxRow.=$compte->telephone_bureau . "&nbsp;/";
}
if ($compte->telephone_mobile) {
    $telFaxRowDecr .= "&nbsp;Tél. Mobile&nbsp;/";
    $telFaxRow.="&nbsp;" . $compte->telephone_mobile . "&nbsp;/";
}
if ($compte->telephone_prive) {
    $telFaxRowDecr .= "&nbsp;Tél. Privé&nbsp;/";
    $telFaxRow.="&nbsp;" . $compte->telephone_prive . "&nbsp;/";
}
if ($compte->fax) {
    $telFaxRowDecr .= "&nbsp;Fax&nbsp;/";
    $telFaxRow.="&nbsp;" . $compte->fax . "&nbsp;/";
}
$telFax = substr($telFaxRowDecr, 0, strlen($telFaxRowDecr) - 1) . ": <i>" . substr($telFaxRow, 0, strlen($telFaxRow) - 1) . "</i>";

$libelle_vtsgn = ($constat->type_vtsgn == 'SGN')? 'Sélection de Grains Nobles' : 'Vendanges Tardives'
?>
<style>
<?php echo styleConstat(); ?>
</style>
<span class="h3Alt">&nbsp;Exploitation&nbsp;</span><br/>
<table class="tableAlt"><tr><td>
            <table border="0">
                <tr>
                    <td style="width: 420px;"></td>
                    <td></td>
                </tr>
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
                    <td><?php echo $telFax; ?></td>
                    <td></td>
                </tr>
                <tr>
                    <td>&nbsp;Email : <i><?php echo $constats->email ?></i></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="width: 420px;"></td>
                    <td></td>
                </tr>
            </table>
        </td></tr></table>
<br />
<br />
<div><span class="h3">&nbsp;Constat&nbsp;</span></div>
<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: left; width: 280px;vertical-align: middle;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Mention&nbsp;<?php echo tdStart() ?></th>         
        <th class="td" style="text-align: right; width: 358px;"><?php echo tdStart() ?><?php echo tdStart() ?><?php echo $libelle_vtsgn; ?>&nbsp;&nbsp;<?php echo tdStart() ?></th>      
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 280px;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Produit&nbsp;<?php echo tdStart() ?></th>         
        <th class="td" style="text-align: right; width: 358px;"><?php echo tdStart() ?><?php echo tdStart() ?><?php echo $constat->produit_libelle ?>&nbsp;&nbsp;<?php echo tdStart() ?></th>      
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 280px;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Dénomination complémentaire&nbsp;<?php echo tdStart() ?></th>         
        <th class="td" style="text-align: right; width: 358px;"><?php echo tdStart() ?><?php echo tdStart() ?><?php echo $constat->denomination_lieu_dit ?>&nbsp;&nbsp;<?php echo tdStart() ?></th>      
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 280px;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;Volume&nbsp;<?php echo tdStart() ?></th>         
        <th class="td" style="text-align: right; width: 358px;"><?php echo tdStart() ?><?php echo tdStart() ?><?php echo $constat->volume_obtenu ?>&nbsp;hl&nbsp;&nbsp;<?php echo tdStart() ?></th>      
    </tr>
    <tr>
        <th class="th" style="text-align: left; width: 280px;"><?php echo tdStart() ?><?php echo tdStart() ?>&nbsp;T.A.V.N.(*)&nbsp;<?php echo tdStart() ?></th>         
        <th class="td" style="text-align: right; width: 358px;"><?php echo tdStart() ?><?php echo tdStart() ?><?php echo $constat->degre_potentiel_volume ?>&nbsp;°&nbsp;&nbsp;<?php echo tdStart() ?></th>      
    </tr>
</table>
<br />
<br />
<br />
(*) Titre Alcoométrique Volumique Nature (conversion à 16,83°/l)
<br />
<br />
<br />
<?php echo "Signé électroniquement le " . ucfirst(format_date($constat->date_signature, "P", "fr_FR")) . ","; ?>
<br />
<br />
<table cellspacing=0 cellpadding=0>
    <tr><td class="tdH2Big"><span class="h2">Signature</span></td></tr>
</table>
<br />
<img style="height: 130px" src="<?php echo $constat->signature_base64; ?>" />