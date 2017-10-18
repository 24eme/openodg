<?php use_helper('Float'); ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'revendication_superficie', 'drev' => $drev)) ?>

    <div class="page-header"><h2>Revendication de la superficie</h2></div>

    <form role="form" action="<?php echo url_for("drev_revendication_superficie", $drev) ?>" method="post" class="ajaxForm" id="form_drev_revendication_vci">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr>
                <th class="text-left col-xs-4">Appellation revendiquée</th>
                <th style="position: relative;" class="text-center col-xs-2 info">Superficie récolté totale<br />(L4 sur la DR) &nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-2">Superficie revendiqué<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-2">VCI<br />&nbsp;<a title="A définir" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
            </tr>
        </thead>
        <tbody class="edit_vci">
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr class="produits vertical-center">
                    <td><?php echo $produit->getLibelleComplet() ?></td>
                    <td class="info"><?php echo $formProduit['recolte']['superficie_total']->render(array( 'placeholder' => "hl")) ?></td>
                    <td><?php echo $formProduit['superficie_revendique']->render(array( 'placeholder' => "hl")) ?></td>
                    <td class="text-center pointer_checkbox"><?php echo $formProduit['has_stock_vci']->render() ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($ajoutForm->hasProduits()): ?>
        <button class="btn btn-sm btn-default" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une appellation</button>
    <?php endif; ?>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Valider et retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
            <?php else: ?>
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer</span>  <span class="glyphicon glyphicon-chevron-right"></span></button>
        <?php endif; ?>
    </div>
    </form>
</div>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
