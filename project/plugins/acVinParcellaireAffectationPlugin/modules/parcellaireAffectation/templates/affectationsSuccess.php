<?php use_helper('Date') ?>

<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaireAffectation' => $parcellaireAffectation)); ?>
<?php include_partial('parcellaireAffectation/step', array('step' => 'affectations', 'parcellaireAffectation' => $parcellaireAffectation)) ?>

<form id="validation-form" action="<?php echo url_for("parcellaireaffectation_affectations", $parcellaireAffectation) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($parcellaireAffectation->declaration->getParcellesByDgc() as $dgc => $parcelles): ?>
    <div style="margin-bottom: 1em;" class="row">
        <div class="col-xs-12">
            <h3>Dénomination complémentaire de <?php echo str_replace("-", " ", $dgc); ?></h3>
        </div>
    </div>
    <table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surf. totale <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1" style="text-align: right;">Surf. dédiée&nbsp;<span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1">Affectée?</th>
                <th class="col-xs-1">Type</th>

            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
                $produitKey = str_replace('/declaration/', '', $parcelle->getProduit()->getHash());
			if (isset($form[$produitKey][$parcelle->getKey()])):
		?>
			<tr class="vertical-center" id="tr_<?php echo str_replace("/","-",$produitKey)."-".$parcelle->getKey();?>">
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
                <td><?php echo $parcelle->cepage; ?></td>
                <td><?php echo $parcelle->campagne_plantation; ?></td>
                <td style="text-align: right;"><?php echo number_format($parcelle->superficie,4); ?></td>
                <td style="justify-content: space-between; display: flex;"><span style=""  class="text-muted"><?php $percent = 100*($parcelle->superficie_affectation / $parcelle->superficie); echo ceil($percent)."%"; ?>&nbsp;</span><span style="text-align: right;"><?php echo  number_format($parcelle->superficie_affectation,4); ?></span></td>
            	<td class="text-center">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['affectee']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['affectee']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $form[$produitKey][$parcelle->getKey()]['affectee']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
                <td style="text-align: center;">
                    <?php if ($parcelle->superficie_affectation != $parcelle->superficie): ?>
                        <span>Partiel</span>
                    <?php else: ?><span>Total</span>
                <?php endif; ?>
                </td>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
	</table>
    <?php  endforeach; ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectation_exploitation", $parcellaireAffectation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>
