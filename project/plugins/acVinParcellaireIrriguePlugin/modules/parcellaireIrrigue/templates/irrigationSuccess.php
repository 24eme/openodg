<?php use_helper('Date') ?>
<?php use_helper('Float') ?>

<?php include_partial('parcellaireIrrigue/breadcrumb', array('parcellaireIrrigue' => $parcellaireIrrigue)); ?>

<div class="page-header no-border">
    <h2>Identification des parcelles irriguées
    <?php if($parcellaireIrrigue->isPapier()): ?>
    <small class="pull-right"><span class="glyphicon glyphicon-file"></span> Déclaration papier</small>
    <?php endif; ?>
    </h2>
</div>

<?php include_partial('global/flash'); ?>

<form id="validation-form" action="<?php echo url_for("parcellaireirrigue_edit", array('sf_subject' => $etablissement, 'periode' => $periode, 'papier' => $papier)) ?>" method="post" class="form-horizontal">
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
            <p class="text-right" style="margin-top: 20px;"><a id="btn-switchactive-all" href="javascript:void(0)" data-status="affecter" data-terme="sont irriguées" data-target="#parcelles_<?php echo $commune; ?>"><span class='glyphicon glyphicon-check'></span>&nbsp;Toutes les parcelles de cette commune sont irriguées</a></p>
        </div>
    </div>
    <table id="parcelles_<?php echo $commune; ?>" class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
		<thead>
        	<tr>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-2">Cépage</th>
                <th class="col-xs-1">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(<?php echo ParcellaireConfiguration::getInstance()->isAres() ? 'ares' : 'ha' ?>)</span></th>
                <?php if (ParcellaireConfiguration::getInstance()->hasIrrigableMaterielRessource()): ?>
                    <th class="col-xs-1">Type de matériel</th>
                    <th class="col-xs-1">Type de ressource</th>
                <?php endif; ?>
                <th class="col-xs-1">Irrigation?</th>
                <th class="col-xs-2">Date de déclaration d'irrigation</th>

            </tr>
		</thead>
		<tbody>
		<?php
			foreach ($parcelles as $parcelle):
                $produitKey = str_replace('/declaration/', '', $parcelle->getProduit()->getHash());
			if (isset($form[$parcelle->getParcelleId()])):
		?>
			<tr style="cursor: pointer;" class="vertical-center" id="tr_<?php $parcelle->getParcelleId();?>">
                <td><?php echo $parcelle->lieu; ?></td>
                <td style="text-align: center;"><?php echo $parcelle->section; ?> <span class="text-muted">/</span> <?php echo $parcelle->numero_parcelle; ?></td>
                <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
                <td><?php echo $parcelle->campagne_plantation; ?></td>
                <?php if (ParcellaireConfiguration::getInstance()->isAres()): ?>
                    <td class="text-right"><?php echoFloatFr($parcelle->getSuperficie(ParcellaireClient::PARCELLAIRE_SUPERFICIE_UNIT_ARE)); ?></td>
                <?php else: ?>
                    <td class="text-right"><?php echoFloatFr($parcelle->getSuperficie()); ?></td>
                <?php endif ?>
                <?php if (ParcellaireConfiguration::getInstance()->hasIrrigableMaterielRessource() ): ?>
                    <td><?php echo $parcelle->materiel; ?></td>
                    <td><?php echo $parcelle->ressource; ?></td>
                <?php endif; ?>
            	<?php if($parcelle->irrigation && (!$parcellaireIrrigue->exist('papier') || !$parcellaireIrrigue->papier)): ?>
            	<td class="text-center text-success"><span class="glyphicon glyphicon-ok-sign"></span></td>
            	<td class="text-center"><?php echo format_date($parcelle->date_irrigation, "dd/MM/yyyy", "fr_FR"); ?></td>
            	<?php else: ?>
            	<td class="text-center">
                	<div style="margin-bottom: 0;" class="form-group <?php if($form[$parcelle->getParcelleId()]['irrigation']->hasError()): ?>has-error<?php endif; ?>">
                    	<?php echo $form[$parcelle->getParcelleId()]['irrigation']->renderError() ?>
                        <div class="col-xs-12">
                            <label class="switch-xl">
                                <?php echo $form[$parcelle->getParcelleId()]['irrigation']->render(array('class' => "switch")); ?>
                                <span class="slider-xl round"></span>
                            </label>
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
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $parcellaireIrrigue->identifiant, 'campagne' => $parcellaireIrrigue->campagne)); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <?php if($parcellaireIrrigue->isValidee()): ?>
                <a href="<?php echo url_for('parcellaireirrigue_export_pdf_last',array('identifiant' => $parcellaireIrrigue->identifiant, 'periode' => $parcellaireIrrigue->periode)) ?>" class="btn btn-success">
                    <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;Visualiser
                </a>
            <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right"><button type="button" class="btn btn-primary btn-upper transparence-lg"  id="btn-validation-document" data-toggle="modal" data-target="#parcellaireirrigue-confirmation-validation">Valider</button></div>
    </div>
    <?php include_partial('parcellaireIrrigue/popupConfirmationValidation', array('form' => $form)); ?>
</form>

<?php if(isset($form["signataire"]) && $form["signataire"]->hasError()): ?>
<script type="text/javascript">
$('#parcellaireirrigable-confirmation-validation').modal('show')
</script>
<?php endif; ?>
<script>
    document.querySelectorAll('form .switch').forEach(function(item) {
        item.addEventListener('change-native', function(e) {
            document.querySelector('#btn-validation-document').classList.remove('transparence-lg');
        });
    });
</script>
