<?php use_helper('Float'); ?>
<?php
$currentParcellaire = $parcellaireManquant->getParcellaire();
$previousParcelles = [];
foreach($parcellaireManquant->getParcelles() as $p) {
    $pMatch = $currentParcellaire->findParcelle($p, 0.75);
    if($pMatch) {
        $previousParcelles[$pMatch->getHash()] = true;
    }
}
?>
<?php $parcellaire = $parcellaireManquant->getParcellaire2Reference(); ?>
<?php if ($parcellaire) foreach ($parcellaire->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
    <div class="row">
        <div class="col-xs-6">
            <h3><?php echo $commune; ?></h3>
        </div>
        <div class="col-xs-6">
           <p class="text-right" style="margin-top: 20px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#parcelles_<?php echo $commune; ?>" style="display: none;"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette commune ont des pieds morts ou manquants</a><a href="javascript:void(0)" class="bootstrap-switch-removeall" data-target="#parcelles_<?php echo $commune; ?>" style="display: none;"><span class='glyphicon glyphicon-remove'></span>&nbsp;Désélectionner toutes les parcelles de cette commune</a></p>
       </div>
    </div>
    <table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-3">Lieu-dit</th>
                <th class="col-xs-1" style="text-align: right;">Section</th>
                <th class="col-xs-1">N° parcelle</th>
                <th class="col-xs-3">Produit</th>
                <th class="col-xs-1 text-center">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surface <span class="text-muted small">(ha)</span></th>

                <th class="col-xs-2 text-center">Pieds morts ou manquants ? <span class="text-muted">(si&nbsp;+&nbsp;de&nbsp;<?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>%)</span></th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($parcelles as $parcelle): ?>
			<tr style="cursor: pointer;" class="parcellerow switch-to-higlight <?php if ($parcellaireManquant->findParcelle($parcelle)): ?>success<?php endif; ?>" >
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: right;"><?php echo $parcelle->section; ?></td>
                <td><?php echo $parcelle->numero_parcelle; ?></td>
                <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
                <td class="text-center"><?php echo $parcelle->campagne_plantation; ?></td>
                <td class="text-right"><?php echoFloatFr($parcelle->superficie_affectation, 4); ?></td>
				<td class="text-center"><input <?php if (array_key_exists($parcelle->getHash(), $previousParcelles)): ?>checked="checked"<?php endif; ?> type="checkbox" name="parcelles[]" value="<?php echo $parcelle->getHash() ?>" class="bsswitch" data-size='small' data-on-text="<span class='glyphicon glyphicon-ok-sign'></span>" data-off-text="<span class='glyphicon'></span>" data-on-color="success" /></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>
