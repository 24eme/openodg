<?php use_helper('Date') ?>

<?php include_partial('parcellaireIntentionAffectation/breadcrumb', array('parcellaireIntentionAffectation' => $parcellaireIntentionAffectation)); ?>

<div class="page-header no-border">
    <h2>Identification parcellaire AOC</h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_request->isMethod(sfWebRequest::POST) && !$form->isValid()): ?>
 	<div class="alert alert-danger" role="alert">La saisie des surfaces affectables est invalide</div>
<?php endif; ?>

<form id="validation-form" action="<?php echo url_for("parcellaireintentionaffectation_edit", array("sf_subject" => $etablissement, "periode" => $periode)) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($parcellaireIntentionAffectation->declaration->getParcellesByDgc() as $dgc => $parcelles): ?>
    <div style="margin-bottom: 1em;" class="row">
        <div class="col-xs-12">
            <h3>Dénomination complémentaire <?php echo str_replace("-", " ", $dgc); ?></h3>
        </div>
    </div>
    <table id="parcelles_<?php echo $dgc; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-2">Commune</th>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1">Affectable?</th>
                <th class="col-xs-1">Date affectable depuis</th>
                <th class="col-xs-1" style="text-align: right;">Surf. affectable <span class="text-muted small">(ha)</span></th>
            </tr>
		</thead>
		<tbody>
		<?php
      $parcelles = $parcelles->getRawValue();
      ksort($parcelles);
			foreach ($parcelles as $parcelle):
                $formkey = $parcelle->produit_hash.'/'.$parcelle->parcelle_id;
			if (isset($form[$formkey])):
		?>
			<tr class="vertical-center" id="tr_<?php echo str_replace("/","-",$produitKey)."-".$parcelle->getKey();?>">
				<td><?php echo $parcelle->commune; ?></td>
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?><br><span class="text-muted"><?php echo $parcelle->getParcelleId(); ?></span></td>
                <td><?php echo $parcelle->cepage; ?></td>
                <td><?php echo $parcelle->campagne_plantation; ?></td>
                <td style="text-align: right;"><?php echo number_format($parcelle->superficie_parcellaire,4); ?></td>

            	<td class="text-center">
                	<div style="margin-bottom: 0;" id = "affectation" class="form-group <?php if($form[$formkey]['affectation']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$formkey]['affectation']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $form[$formkey]['affectation']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            	<td class="text-center"><?php echo ($parcelle->exist('date_affectation')) ? $parcelle->getDateAffectationFr() : '' ?></td>
                <td class="text-center <?php if($form[$formkey]['superficie']->renderError()): ?>has-error<?php endif; ?>">
                    <div style="margin-bottom: 0;" id = "surface" class="form-group">
                        <div class="col-xs-12<?php if ($parcelle->superficie_parcellaire != $parcelle->superficie) { echo " has-warning"; } ?>">
                            <?php echo $form[$formkey]['superficie']->render(array('class' => 'form-control text-right bsswitch-input affecte_superficie' , 'placeholder' => $parcelle->superficie, 'value' => $parcelle->superficie)); ?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php  endif; endforeach; ?>
        <tr><td colspan="8" class="text-right"><b>Total affecté</b></td><td style="padding-right: 20px;" class="text-right total_affecte"></td></tr>
        <tr><td colspan="8" class="text-right"><b>Total superficie</b></td><td style="padding-right: 20px;" class="text-right total_superficie"></td></tr>
        </tbody>
	</table>
    <?php  endforeach; ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider</button></div>
    </div>
</form>
</div>
