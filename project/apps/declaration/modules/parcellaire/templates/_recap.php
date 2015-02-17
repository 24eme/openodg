<?php use_helper("Date"); ?>
<div class="row">
    <div class="col-xs-12">
        <?php
        foreach ($parcellesByCommunes as $commune_key => $parcelles):
            ?>

            <h3><strong> <?php echo "Commune " . $parcelles->commune; ?></strong> <span class="small right" style="text-align: right;"><?php echo $parcelles->total_superficie . ' (ares)'; ?></span></h3>
            <table class="table table-striped">
                <tbody>
                    <?php foreach ($parcelles->produits as $parcelleKey => $parcelle): ?>
<?php
$styleline = '';
$styleproduit = '';
$styleparcelle = '';
$stylesuperficie = '';
if (!array_key_exists($commune_key, $parcellesByCommunesLastCampagne->getRawValue()) || !array_key_exists($parcelleKey, $parcellesByCommunesLastCampagne[$commune_key]->produits->getRawValue())) {
    $styleline = 'border-style: solid; border-width: 1px; border-color: darkgreen;';
}else {
    if ($parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->appellation_libelle != $parcelle->appellation_libelle || $parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->lieu_libelle != $parcelle->lieu_libelle || $parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->cepage_libelle != $parcelle->cepage_libelle) {
        $styleproduit = 'border-style: solid; border-width: 1px; border-color: darkorange;';
    }
    if ($parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->num_parcelle != $parcelle->num_parcelle) {
        $styleparcelle = 'border-style: solid; border-width: 1px; border-color: darkorange;';
    }
    if ($parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->superficie != $parcelle->superficie) {
        $styleline = (!$parcelle->superficie)? 'text-decoration: line-through; border-style: solid; border-width: 1px; border-color: darkred' : '';
        $stylesuperficie = (!$parcelle->superficie)? 'border-style: solid; border-width: 1px; border-color: darkred' : 'border-style: solid; border-width: 1px; border-color: darkorange';
    }
}
if (!$styleline && !$styleproduit && !$styleparcelle && !$stylesuperficie) {
    continue;
}
?>
                            <tr style="<?php echo $styleline;?>">
                                <td class="col-xs-6" style="<?php echo $styleproduit; ?>">
                                    <?php echo $parcelle->appellation_libelle . ' ' . $parcelle->lieu_libelle . ' - ' . $parcelle->cepage_libelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right; <?php echo $styleparcelle; ?>">
                                    <?php echo 'parcelle ' . $parcelle->num_parcelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right; <?php echo $stylesuperficie;?>">
                                    <?php echo $parcelle->superficie . ' (ares)'; ?>
                                </td>   
                            </tr> 
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

    </div>
</div>