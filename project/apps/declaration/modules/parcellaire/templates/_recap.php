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
                        <?php if (!array_key_exists($commune_key, $parcellesByCommunesLastCampagne->getRawValue()) || !array_key_exists($parcelleKey, $parcellesByCommunesLastCampagne[$commune_key]->produits->getRawValue())):
                            ?>
                            <tr style=" border-style: solid; border-width: 1px; border-color: darkgreen">
                                <td class="col-xs-6">
                                    <?php echo $parcelle->appellation_libelle . ' ' . $parcelle->lieu_libelle . ' - ' . $parcelle->cepage_libelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right;">
                                    <?php echo 'parcelle ' . $parcelle->num_parcelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right;">
                                    <?php echo $parcelle->superficie . ' (ares)'; ?>
                                </td>   
                            </tr> 
                            <?php continue; ?>
                        <?php endif; ?>

                        <?php
                        if ($parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->appellation_libelle != $parcelle->appellation_libelle ||
                                $parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->lieu_libelle != $parcelle->lieu_libelle ||
                                $parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->cepage_libelle != $parcelle->cepage_libelle
                        ):
                            ?>
                            <tr>
                                <td class="col-xs-6 " style="border-style: solid; border-width: 1px; border-color: darkorange">
                                    <?php echo $parcelle->appellation_libelle . ' ' . $parcelle->lieu_libelle . ' - ' . $parcelle->cepage_libelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right;">
                                    <?php echo 'parcelle ' . $parcelle->num_parcelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right; ">
                                    <?php echo $parcelle->superficie . ' (ares)'; ?>
                                </td>   
                            </tr> 
                            <?php continue; ?>
                        <?php endif; ?>
                        <?php if ($parcellesByCommunesLastCampagne[$commune_key]->produits[$parcelleKey]->superficie != $parcelle->superficie):
                            $styleBarre = (!$parcelle->superficie)? 'style="text-decoration: line-through"' : '';
                        $colorBorder = (!$parcelle->superficie)? 'darkred' : 'darkorange';
                            ?>
                            <tr <?php echo $styleBarre; ?> >
                                <td class="col-xs-6">
                                    <?php echo $parcelle->appellation_libelle . ' ' . $parcelle->lieu_libelle . ' - ' . $parcelle->cepage_libelle; ?>
                                </td>   
                                <td class="col-xs-3" style="text-align: right;">
                                    <?php echo 'parcelle ' . $parcelle->num_parcelle; ?>
                                </td>   
                                <td class="col-xs-3 borders" style="text-align: right;  border-style: solid; border-width: 1px; border-color: <?php echo $colorBorder; ?>">
                                    <?php echo $parcelle->superficie . ' (ares)'; ?>
                                </td>   
                            </tr> 
                            <?php continue; ?>
                        <?php endif; ?>                 
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

    </div>
</div>