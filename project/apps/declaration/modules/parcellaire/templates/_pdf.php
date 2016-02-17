<?php use_helper("Date"); ?>
<?php use_helper('Parcellaire') ?>
<?php use_helper('TemplatingPDF') ?>
<?php use_helper('Float') ?>
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
                    <td>N° SIRET : <i><?php echo $parcellaire->declarant->siret ?></i></td>
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
        </td></tr></table>
<br /> 
<?php if (count($parcellesByLieu->acheteurs)): ?>
    <br /> 
    <span class="h3Alt">&nbsp;Destination des raisins&nbsp;</span><br/>
    <table class="tableAlt"><tr><td>
                <table border="0">
                    <?php if(!$cviFilter): ?>
                        <?php foreach ($parcellesByLieu->acheteurs as $type => $acheteurs): ?>
                            <tr>
                                <td><span style="font-family: Dejavusans">☒</span>&nbsp;<?php echo ParcellaireClient::$destinations_libelles[$type] ?>
                                    <?php
                                    $acheteurs_nom = array();
                                    foreach ($acheteurs as $acheteur) {
                                        if ($acheteur->cvi != $parcellaire->identifiant)
                                            $acheteurs_nom[] = $acheteur->nom;
                                    }
                                    $acheteurs_nom = array_unique($acheteurs_nom);
                                    if (count($acheteurs_nom))
                                        echo ' : <strong>';
                                    echo implode('</strong>, <strong>', $acheteurs_nom);
                                    if (count($acheteurs_nom))
                                        echo '</strong>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php $acheteursByCvi = array(); foreach ($parcellesByLieu->acheteurs as $type => $acheteurs) { foreach ($acheteurs as $acheteur) { $acheteursByCvi[$acheteur->cvi] = $acheteur->nom; }}  ?>
                        <tr>
                            <td><?php if(count($acheteursByCvi) > 1): ?>Partagées entre plusieurs destinataires dont <?php echo $acheteursByCvi[$cviFilter]; ?> <?php else: ?>Dédiées à <?php echo $acheteursByCvi[$cviFilter]; ?><?php endif; ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </td></tr></table>
    <br />
<?php endif; ?>
<div><span class="h3">&nbsp;<?php echo $parcellesByLieu->appellation_libelle; ?><?php echo ($parcellesByLieu->lieu_libelle) ? '&nbsp;-&nbsp;' . $parcellesByLieu->lieu_libelle : ''; ?>&nbsp;</span></div>

<table class="table" border="1" cellspacing=0 cellpadding=0 style="text-align: right;">
    <tr>
        <th class="th" style="text-align: center; width: 170px;">&nbsp;Commune</th>        
        <th class="th" style="text-align: center; width: 74px;">Section</th>       
        <th class="th" style="text-align: center; width: 74px;">Numéro</th>
        <th class="th" style="text-align: center; width: 160px;">Cépage</th>        
        <th class="th" style="text-align: center; width: 60px;">VT/SGN</th>
        <th class="th" style="text-align: center; width: 100px;">Surface</th>
    </tr>
    <?php
    foreach ($parcellesByLieu->parcelles as $detailHash => $parcelle):
        $hasVtSgn = $parcelle->parcelle->getCepage()->getConfig()->hasVtsgn();
        ?>
        <tr>
            <td class="td" style="text-align:left;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->parcelle->commune ?>&nbsp;</td>        
            <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->parcelle->section ?>&nbsp;</td>       
            <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->parcelle->numero_parcelle ?>&nbsp;</td>
            <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;<?php echo $parcelle->cepage_libelle ?>&nbsp;</td>
            <?php if (!$hasVtSgn): ?>
            <td class="td" style="text-align:center; background-color: #ddd"><?php echo tdStart() ?>&nbsp;</td>
                               
                <?php elseif ($parcelle->parcelle->vtsgn): ?> 
                    <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;X&nbsp;</td>
                <?php else: ?>
                    <td class="td" style="text-align:center;"><?php echo tdStart() ?>&nbsp;&nbsp;</td>
    <?php endif; ?>
            <td class="td" style="text-align:right;"><?php echo tdStart() ?>&nbsp;<?php printf("%0.2f", $parcelle->parcelle->superficie); ?>&nbsp;<small>ares</small>&nbsp;&nbsp;&nbsp;</td>
        </tr>
<?php endforeach; ?>
</table>

<br />
<br />
