<?php use_helper('Date') ?>

<?php include_partial('parcellaireIntentionAffectation/breadcrumb', array('parcellaireIntentionAffectation' => $parcellaireIntentionAffectation)); ?>

<div class="page-header no-border">
    <h2>Intention d'affectation parcellaire
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier</small>
    </h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_request->isMethod(sfWebRequest::POST) && !$form->isValid()): ?>
 	<div class="alert alert-danger" role="alert">La saisie des surfaces affectables est invalide</div>
<?php endif; ?>

<p>Veuillez activer les parcelles pouvant prétendre à une dénomination complémentaire.</p>

<form id="validation-form" action="<?php echo url_for("parcellaireintentionaffectation_edit", array("sf_subject" => $etablissement, "campagne" => $campagne)) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($parcellaireIntentionAffectation->declaration->getParcellesByCommune(null) as $commune => $parcelles): ?>
	    <div class="row">
        <div class="col-xs-6">
            <h3><?php echo $commune; ?></h3>
        </div>
        <div class="col-xs-6">
           <p class="text-right" style="margin-top: 20px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#parcelles_<?php echo $commune; ?>" style="display: none;"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette commune sont affectées</a><a href="javascript:void(0)" class="bootstrap-switch-removeall" data-target="#parcelles_<?php echo $commune; ?>" style="display: none;"><span class='glyphicon glyphicon-remove'></span>&nbsp;Désélectionner toutes les parcelles de cette commune</a></p>
       </div>
    </div>
    <table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-2">Dénom. compl.</th>
                <th class="col-xs-1">Affectation?</th>
                <th class="col-xs-1">Date affectation</th>
                <th class="col-xs-1" style="text-align: right;">Surf. affectatable <span class="text-muted small">(ha)</span></th>
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
                <td style="text-align: right;"><?php echo $parcelle->superficie; ?></td>
            	<td><?php echo $parcelle->getDgcLibelle(); ?></td>
            	<td class="text-center">
                	<div style="margin-bottom: 0;" id = "affectation" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['affectation']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['affectation']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $form[$produitKey][$parcelle->getKey()]['affectation']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            	<td class="text-center"><?php echo $parcelle->getDateAffectationFr() ?></td>
                <td class="text-center">
                    <div style="margin-bottom: 0;" id = "surface" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['superficie_affectation']->hasError()): ?>has-error<?php endif; ?>">
                        <div class="col-xs-12">
                            <?php echo $form[$produitKey][$parcelle->getKey()]['superficie_affectation']->render(array('class' => 'form-control text-center bsswitch-input' , 'placeholder' => $parcelle->superficie)); ?>
                        </div>
                    </div>
                </td>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
	</table>
    <?php  endforeach; ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
</div>