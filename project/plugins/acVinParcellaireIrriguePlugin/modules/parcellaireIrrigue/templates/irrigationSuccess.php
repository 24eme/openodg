<?php use_helper('Date') ?>

<?php include_partial('parcellaireIrrigue/breadcrumb', array('parcellaireIrrigue' => $parcellaireIrrigue)); ?>

<div class="page-header no-border">
    <h2>Identification des parcelles irriguées
    <?php if($parcellaireIrrigue->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier</small>
    <?php endif; ?>
    </h2>
</div>

<?php include_partial('global/flash'); ?>

<form id="validation-form" action="<?php echo url_for("parcellaireirrigue_edit", array('sf_subject' => $etablissement, 'campagne' => $campagne, 'papier' => $papier)) ?>" method="post" class="form-horizontal">
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php if(isset($form["date_papier"])): ?>
    <div class="row">
        <div class="form-group <?php if ($form["date_papier"]->hasError()): ?>has-error<?php endif; ?>">
            <?php if ($form["date_papier"]->hasError()): ?>
                <div class="alert alert-danger" role="alert"><?php echo $form["date_papier"]->getError(); ?></div>
            <?php endif; ?>
            <?php echo $form["date_papier"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
            <div class="col-xs-4">
            	<?php echo $form["date_papier"]->renderError() ?>
                <div class="input-group date-picker">
                    <?php echo $form["date_papier"]->render(array("class" => "form-control")); ?>
                    <div class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php foreach ($parcellaireIrrigue->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
	    <div class="row">
        <div class="col-xs-6">
            <h3><?php echo $commune; ?></h3>
        </div>
        <div class="col-xs-6">
           <p class="text-right" style="margin-top: 20px;"><a href="javascript:void(0)" class="bootstrap-switch-activeall" data-target="#parcelles_<?php echo $commune; ?>" style="display: none;"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette commune sont irriguées</a><a href="javascript:void(0)" class="bootstrap-switch-removeall" data-target="#parcelles_<?php echo $commune; ?>" style="display: none;"><span class='glyphicon glyphicon-remove'></span>&nbsp;Désélectionner toutes les parcelles de cette commune</a></p>
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
                <th class="col-xs-1">Type de matériel</th>
                <th class="col-xs-1">Type de ressource</th>
                <th class="col-xs-1">Irrigation?</th>
                <th class="col-xs-2">Date de déclaration d'irrigation</th>

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
            	<td><?php echo $parcelle->materiel; ?></td>
            	<td><?php echo $parcelle->ressource; ?></td>
            	<?php if($parcelle->irrigation && (!$parcellaireIrrigue->exist('papier') || !$parcellaireIrrigue->papier)): ?>
            	<td class="text-center text-success"><span class="glyphicon glyphicon-ok-sign"></span></td>
            	<td class="text-center"><?php echo format_date($parcelle->date_irrigation, "dd/MM/yyyy", "fr_FR"); ?></td>
            	<?php else: ?>
            	<td class="text-center">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['irrigation']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$produitKey][$parcelle->getKey()]['irrigation']->renderError() ?>
                        <div class="col-xs-12">
			            	<?php echo $form[$produitKey][$parcelle->getKey()]['irrigation']->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "<span class='glyphicon glyphicon-ok-sign'></span>", 'data-off-text' => "<span class='glyphicon'></span>", 'data-on-color' => "success")); ?>
                        </div>
                    </div>
            	</td>
            	<td></td>
            	<?php endif; ?>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
	</table>
    <?php  endforeach; ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaireIrrigue->identifiant)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <?php if($parcellaireIrrigue->isValidee()): ?>
                <a href="<?php echo url_for('parcellaireirrigue_export_pdf', $parcellaireIrrigue) ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
                </a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right"><button type="button" class="btn btn-primary btn-upper"  id="btn-validation-document" data-toggle="modal" data-target="#parcellaireirrigue-confirmation-validation">Valider <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
    <?php include_partial('parcellaireIrrigue/popupConfirmationValidation', array('form' => $form)); ?>
</form>
</div>

<?php if($form["signataire"]->hasError()): ?>
<script type="text/javascript">
$('#parcellaireirrigable-confirmation-validation').modal('show')
</script>
<?php endif; ?>
