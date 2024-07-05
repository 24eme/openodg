<?php use_helper('Float'); ?>
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
