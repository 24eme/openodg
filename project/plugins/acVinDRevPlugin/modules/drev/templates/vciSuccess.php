<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'vci', 'drev' => $drev, 'ajax' => true)) ?>

    <div class="page-header"><h2>Répartition du VCI <?php echo intval($drev->periode) - 1 ?></h2></div>

    <?php echo include_partial('global/flash'); ?>

    <form role="form" action="<?php echo url_for("drev_vci", $drev) ?>" method="post" class="ajaxForm" id="form_vci_drev_<?php echo $drev->_id; ?>">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-left"></th>
                <th class="text-center col-xs-1"></th>
                <th class="text-center col-xs-1"></th>
                <th class="text-center col-xs-1" colspan="4">Utilisation</th>
            </tr>
            <tr>
                <th class="text-left col-xs-3"><?php if (count($form['produits']) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
                <th style="position: relative;" class="text-center col-xs-1">Plafond <?php echo ($drev->periode) ?><br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'plafond_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Stock <?php echo ($drev->periode - 1) ?><br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'stock_vci_precedent') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Rafraichi<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'rafraichi_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Complément<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'complement_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">A détruire<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'destruction_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Substitué<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'substitution_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
            </tr>
        </thead>
        <tbody class="edit_vci">
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr class="produits vertical-center">
                    <td><?php echo $produit->getLibelleComplet() ?> <small class="text-muted">(<?php echoFloat($produit->recolte->superficie_total) ?> ha)</small></td>
                    <td class="text-right"><?php if($produit->getPlafondStockVci()): ?><?php echoFloat($produit->getPlafondStockVci()) ?> <small class="text-muted">hl</small><?php endif; ?></td>
                    <td><?php echo $formProduit['stock_precedent']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['rafraichi']->render(array('class' => 'form-control text-right input-float  sum_stock_final', 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['complement']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['destruction']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['substitution']->render(array( 'placeholder' => "hl")) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_revendication_superficie", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer</span>  <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>

  </form>
