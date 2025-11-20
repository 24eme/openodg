<?php use_helper('Float'); ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireManquant]); ?>
<?php else: ?>
    <?php include_partial('parcellaireManquant/breadcrumb', array('parcellaireManquant' => $parcellaireManquant)); ?>
<?php endif; ?>

<?php include_partial('parcellaireManquant/step', array('step' => 'parcelles', 'parcellaireManquant' => $parcellaireManquant)) ?>
<div>
    <h2>Parcelles de votre exploitation</h2>
    <p class="pt-3">Merci d'indiquer vos parcelles ayant des pieds manquants ou morts en cliquant sur la ligne de la parcelle concernée.</p>
    <?php if(!ParcellaireConfiguration::getInstance()->isManquantAllPourcentageAllowed()): ?>
    <div class="alert alert-info">
        <p style="margin:10px 0;">
            <span class="glyphicon glyphicon-info-sign"></span> Il n'est pas nécessaire d'indiquer les parcelles avec moins de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants.
            <a style="margin-top:-5px;" href="<?php echo url_for('parcellairemanquant_validation', $parcellaireManquant) ?>" class="btn btn-sm btn-default pull-right">
                <span class="glyphicon glyphicon-unchecked"></span>
                Je n'ai pas de parcelle avec plus de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants
            </a>
        </p>
    </div>
    <?php endif; ?>
</div>

<form action="<?php echo url_for("parcellairemanquant_parcelles", $parcellaireManquant) ?>" method="post" class="form-horizontal">
    <?php
    $parcellaire = $parcellaireManquant->getParcellaire2Reference();
    $previousParcelles = $parcellaireManquant->getDeclarationParcelles()->getRawValue();
    ?>
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

                    <th class="col-xs-2 text-center">Pieds morts ou manquants ?<?php if(!ParcellaireConfiguration::getInstance()->isManquantAllPourcentageAllowed()): ?><span class="text-muted">(si&nbsp;+&nbsp;de&nbsp;<?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>%)</span><?php endif; ?></th>
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
                    <td class="text-right"><?php echoFloatFr($parcelle->superficie, 4); ?></td>
    				<td class="text-center"><input <?php if (array_key_exists($parcelle->getParcelleId(), $previousParcelles)): ?>checked="checked"<?php endif; ?> type="checkbox" name="parcelles[]" value="<?php echo $parcelle->getParcelleId() ?>" class="bsswitch" data-size='small' data-on-text="<span class='glyphicon glyphicon-ok-sign'></span>" data-off-text="<span class='glyphicon'></span>" data-on-color="success" /></td>
                </tr>
            <?php  endforeach; ?>
            </tbody>
    	</table>
    <?php  endforeach; ?>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellairemanquant_exploitation", $parcellaireManquant); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireManquant]); ?>
<?php endif; ?>
