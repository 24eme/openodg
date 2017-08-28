<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'vci', 'drev' => $drev)) ?>

    <div class="page-header"><h2>Répartition du VCI</h2></div>

    <form role="form" action="<?php echo url_for("drev_vci", $drev) ?>" method="post" class="ajaxForm" id="form_vci_drev_<?php echo $drev->_id; ?>">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-left"></th>
                <th class="text-center col-xs-1">À utiliser</th>
                <th class="text-center col-xs-1" colspan="4">Utilisation</th>
                <th class="text-center col-xs-1">Constitué</th>
                <th class="text-center col-xs-1">Cumul</th>
            </tr>
            <tr>
                <th class="text-left col-xs-3">Appellation revendiquée</th>
                <th class="text-center col-xs-1">Stock avant récolte</th>
                <th class="text-center col-xs-1">Complément</th>
                <th class="text-center col-xs-1">Substitution</th>
                <th class="text-center col-xs-1">À détruire</th>
                <th class="text-center col-xs-1">Rafraichi</th>
                <th class="text-center col-xs-1">Cette année</th>
                <th class="text-center col-xs-1">Stock après récolte</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr>
                    <td><?php echo $produit->getLibelleComplet() ?></td>
                    <td><?php echo $formProduit['vci_stock_initial']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['vci']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl")) ?></td>
                    <td class="text-right"><?php echo $formProduit['vci_complement_dr']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['vci_substitution']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['vci_destruction']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['vci']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl")) ?></td>
                    <td class="text-right"><?php echo $formProduit['vci']->render(array('class' => 'form-control text-right num_float', 'placeholder' => "hl", "readonly" => "readonly")) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
            <?php else: ?>
            <button type="submit" class="btn btn-primary btn-upper">Continuer vers la validation</span></button>
        <?php endif; ?>
    </div>
    </form>
</div>
