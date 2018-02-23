<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'parcelles', 'parcellaireIrrigable' => $parcellaireIrrigable)) ?>
<div class="page-header">
    <h2>Parcelles irrigables sur votre exploitation <small>Merci de selectionner vos parcelles irrigables</small></h2>
</div>

<form action="<?php echo url_for("parcellaireirrigable_parcelles", $parcellaireIrrigable) ?>" method="post" class="form-horizontal">

<?php foreach ($parcellaireIrrigable->getParcellesFromLastParcellaire() as $produitKey => $parcellesProduit): ?>
	<h3><?php echo $parcellesProduit->libelle; ?></h3>
    <table class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-3">Commune</th>
                <th class="col-xs-1">Section</th>
                <th class="col-xs-1">Parcelle</th>
                <th class="col-xs-3">Cépage</th>
                <th class="col-xs-1">Surface</th>
                <th class="col-xs-1"></th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($parcellesProduit->detail as $parcelleKey => $detail): ?>
			<tr style="cursor: pointer;">
				<td class="col-xs-3"><?php echo $detail->commune; ?></td>
				<td class="col-xs-1 text-right"><?php echo $detail->section;  ?></td>
				<td class="col-xs-1 text-right"><?php echo $detail->numero_parcelle;  ?></td>
				<td class="col-xs-3"><?php echo $detail->getCepageLibelle();  ?></td>
				<td class="col-xs-1 text-right"><?php printf("%0.2f&nbsp;<small class='text-muted'>ha</small>", $detail->superficie); ?></td>
				<td class="col-xs-1 text-center"><input <?php if ($parcellaireIrrigable->exist($detail->getHash())): ?>checked="checked"<?php endif; ?> type="checkbox" name="parcelles[]" value="<?php echo $detail->getHash() ?>" class="bsswitch" data-size='mini' data-on-text="<span class='glyphicon glyphicon-ok-sign'></span>" data-off-text="<span class='glyphicon'></span>" data-on-color="success" /></td>
            </tr>
        <?php  endforeach; ?>
        </tbody>
	</table>
<?php  endforeach; ?>

	<div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("parcellaireirrigable_exploitation", $parcellaireIrrigable); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-6 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>