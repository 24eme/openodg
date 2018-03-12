<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaireIrrigable' => $parcellaireIrrigable)); ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'parcelles', 'parcellaireIrrigable' => $parcellaireIrrigable)) ?>
<div class="page-header">
    <h2>Parcelles irrigables sur votre exploitation <br/><small>Merci de lister vos parcelles irrigables</small></h2>
</div>

<form action="<?php echo url_for("parcellaireirrigable_parcelles", $parcellaireIrrigable) ?>" method="post" class="form-horizontal">

<?php foreach ($parcellaireIrrigable->getParcellesFromLastParcellaire()->getParcellesByCommune() as $commune => $parcelles): ?>
    <div class="row">
        <div class="col-xs-6">
            <h3><?php echo $commune; ?></h3>
        </div>
        <div class="col-xs-6">
           <p class="text-right" style="margin-top: 20px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#parcelles_<?php echo $commune; ?>"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette commune sont irrigables</a></p>
       </div>
    </div>
    <table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped">
		<thead>
        	<tr>
                <th class="col-xs-3">Lieu-dit</th>
                <th class="col-xs-1" style="text-align: right;">Sect°</th>
                <th class="col-xs-1">N° parcelle</th>
                <th class="col-xs-3">Cépage</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surface <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-2 text-center">Irrigable ?</th>
            </tr>
		</thead>
		<tbody>
		<?php foreach ($parcelles as $parcelle): ?>
			<tr style="cursor: pointer;" class="parcellerow switch-to-higlight <?php if ($parcellaireIrrigable->exist($parcelle->getHash())): ?>success<?php endif; ?>" >
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: right;"><?php echo $parcelle->section; ?></td>
                <td><?php echo $parcelle->numero_parcelle; ?></td>
                <td><?php echo $parcelle->cepage; ?></td>
                <td><?php echo $parcelle->campagne_plantation; ?></td>
                <td style="text-align: right;"><?php echo $parcelle->superficie; ?></td>
				<td class="text-center"><input <?php if ($parcellaireIrrigable->exist($parcelle->getHash())): ?>checked="checked"<?php endif; ?> type="checkbox" name="parcelles[]" value="<?php echo $parcelle->getHash() ?>" class="bsswitch" data-size='small' data-on-text="<span class='glyphicon glyphicon-ok-sign'></span>" data-off-text="<span class='glyphicon'></span>" data-on-color="success" /></td>
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
