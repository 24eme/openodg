<?php use_helper("Date"); ?>
<div class="row">
    <div class="col-xs-12">
        <?php foreach ($parcellesByCommunes as $commune_key => $parcelles): ?>
            <h3><strong> <?php echo "Commune " . $parcelles->commune; ?></strong> <span class="small right" style="text-align: right;"><?php echo $parcelles->total_superficie . ' (ares)'; ?></span></h3>
            <table class="table table-striped">
                <tbody>
                    <?php foreach ($parcelles->produits as $parcelle): ?> 
                        <tr>
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
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>

    </div>
</div>