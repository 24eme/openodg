<?php use_helper('Float'); ?>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/headerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireManquant]); ?>
<?php else: ?>
    <?php include_partial('parcellaireManquant/breadcrumb', array('parcellaireManquant' => $parcellaireManquant)); ?>
<?php endif; ?>

<?php include_partial('parcellaireManquant/step', array('step' => 'manquants', 'parcellaireManquant' => $parcellaireManquant)) ?>
<div>
    <h2>Pieds morts ou manquants sur votre exploitation</h2>
    <p class="pt-3">Merci d'indiquer la densité et le % de pied manquant</p>
    <div class="alert alert-info">
        <div style="display: inline-block; margin-right: 1rem;">
            <p><span class="glyphicon glyphicon-info-sign"></span></p>
        </div>
        <div style="display: inline-block; vertical-align: middle">
            Il n'est pas nécessaire d'indiquer les parcelles avec moins de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants.<br/>Si vous n'avez aucune parcelle concernée, vous pouvez aller directement à la <a href="<?php echo url_for('parcellairemanquant_validation', $parcellaireManquant) ?>">validation</a>.
        </div>
    </div>
</div>

<form action="<?php echo url_for("parcellairemanquant_manquants", $parcellaireManquant) ?>" method="post" class="form-inline">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <?php foreach ($parcellaireManquant->declaration->getParcellesByCommune() as $commune => $parcelles): ?>
    <h3><?php echo $commune; ?></h3>
    <table class="table table-bordered table-condensed table-striped duplicateChoicesTable tableParcellaire">
        <thead>
            <tr>
                <th class="col-xs-2">Lieu-dit</th>
                <th class="col-xs-1">Section /<br />N° parcelle</th>
                <th class="col-xs-4">Produit</th>
                <th class="col-xs-1" style="text-align: center;">Année plantat°</th>
                <th class="col-xs-1" style="text-align: right;">Surf. <span class="text-muted small">(ha)</span></th>
                <th class="col-xs-1" style="text-align: center;">Densité</th>
                <th class="col-xs-2" style="text-align: center;">% de pieds<br/>manquants</th>
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
                <td><span class="text-muted"><?php echo $parcelle->getProduitLibelle(); ?></span> <?php echo $parcelle->cepage; ?></td>
                <td style="text-align: center;"><?php echo $parcelle->campagne_plantation; ?></td>
                <td class="text-right"><?php echoFloatFr($parcelle->superficie, 4); ?></td>
                <td style="text-align: center;">
                    <div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['densite']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form[$produitKey][$parcelle->getKey()]['densite']->renderError() ?>
                            <?php echo $form[$produitKey][$parcelle->getKey()]['densite']->render(array('class' => 'form-control text-right input-integer', 'maxlength' => 4, 'size' => 4)) ?>
                        </div>
                </td>
                <td style="text-align: center;">
                    <div style="margin-bottom: 0;" class="form-group <?php if($form[$produitKey][$parcelle->getKey()]['pourcentage']->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form[$produitKey][$parcelle->getKey()]['pourcentage']->renderError() ?>
                        <div class="input-group">
                                <?php echo $form[$produitKey][$parcelle->getKey()]['pourcentage']->render(array('class' => 'form-control input-float text-right', "maxlength" => 5, "size" => 5)) ?>
                            <div class="input-group-addon">%</div>
                        </div>
                    </div>
                </td>
            </tr>
        <?php  endif; endforeach; ?>
        </tbody>
    </table>
    <?php  endforeach; ?>

	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellairemanquant_parcelles", $parcellaireManquant); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>

<?php if(isset($coop)): ?>
    <?php include_partial('parcellaireAffectationCoop/footerDeclaration', ['coop' => $coop, 'declaration' => $parcellaireManquant]); ?>
<?php endif; ?>
