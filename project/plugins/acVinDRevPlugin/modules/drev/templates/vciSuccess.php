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
                <th style="position: relative;" class="text-center col-xs-1">Plafond <?php echo ($drev->periode) ?><br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'plafond_vci', "C'est le plafond de volume maximum autorisé de  VCI en hl cette année. Il correspond au résultat de la surface de cette année* volume autorisé de VCI (hl) fixé par décret.") ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Stock <?php echo ($drev->periode - 1) ?><br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'stock_vci_precedent', "Il s'agit du nombre d'hl total de VCI en stock de l'année précédente. Vous devez répartir l'intégralité de ce volume parmi : Rafraîchi, Complément, Destruction et Substitution. Plusieurs possibilités sont possibles.") ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Rafraichi<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'rafraichi_vci', "Si vous n'utilisez pas votre VCI en complément de récolte ou en substitution, et que vous ne souhaitez pas le décruire, vous allez revendiquer en millésime de l'année précédente ce volume et vous mettez en VCI le même volume de l'année en cours. Vous utilisez votre VCI en le rafraîchissant avec le nouveau millésime.") ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Complément<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'complement_vci', "Vous utilisez ce volume de VCI en complément de votre récolte. Attention cette utilisation vous permet d'atteindre le rendement de base de l'appellation mais pas de le dépasser.") ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">A détruire<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'destruction_vci', "Ce volume est calculé automatiquement en cas de dépassement de votre stock de VCI par rapport au plafond de l'année en cours. Vous pouvez choisir de le détruire. Le VCI est à détruire avant le 15 décembre de l'année qui suit l'année de sa production (conservez le justificatif de destruction).") ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-1">Substitué<br /><small class="text-muted">(hl)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'substitution_vci', "Vous utilisez ce volume pour remplacer de la récolte du nouveau millésime pour cause de déficit qualitatif du nouveau millésime. En contre partie vous devez détruire le même volume du nouveau millésime non qualitatif (conservez le justificatif de destruction).") ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
            </tr>
        </thead>
        <tbody class="edit_vci">
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr class="produits vertical-center">
                    <td><?php echo $produit->getRawValue()->getLibelleCompletHTML() ?> <small class="pull-right" style="margin-top: 3px;"><?php echoFloat(round($produit->recolte->superficie_total, 2)) ?> ha</small></td>
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
