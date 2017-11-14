<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE, 'drev' => $drev, 'ajax' => true)) ?>

    <div class="page-header"><h2>Revendication <?php echo $drev->campagne; ?> de la superficie</h2></div>

    <?php echo include_partial('global/flash'); ?>

    <form role="form" action="<?php echo url_for("drev_revendication_superficie", $drev) ?>" method="post" class="ajaxForm" id="form_drev_revendication_vci">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <table class="table table-bordered table-striped table-condensed">
        <thead>
        	<tr>
                <th class="text-center col-xs-2"></th>
                <th class="text-center info"><?php echo $drev->getDocumentDouanierTypeLibelle(); ?></th>
                <th colspan="2" class="text-center">Déclaration de Revendication</th>
            </tr>
            <tr>
                <th class="text-left col-xs-4">Appellation revendiquée</th>
                <th style="position: relative;" class="text-center col-xs-2 info">Superficie récoltée totale (L4)<br /><small class="text-muted">(ha)</small> &nbsp;<a title="<?php echo getPointAideText('drev', 'superficie_totale') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-2">Superficie revendiquée<br /><small class="text-muted">(ha)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'superficie_revendique') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th style="position: relative;" class="text-center col-xs-2">Possède du stock VCI<br />&nbsp;<a title="<?php echo getPointAideText('drev', 'possede_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
            </tr>
        </thead>
        <tbody class="edit_vci">
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr class="produits vertical-center">
                    <td>
                    	<a href="<?php echo url_for('drev_revendication_cepage_suppression', array('id' => $drev->_id, 'hash' => str_replace('/', '_', $produit->getHash()))) ?>" onclick='return confirm("Êtes vous sûr de vouloir supprimer le produit <?php echo $produit->getLibelleComplet() ?> de votre DRev <?php echo $drev->campagne ?> ?");'><span class="glyphicon glyphicon-remove-sign text-muted"></span></a>
                    	<?php echo $produit->getLibelleComplet() ?>
                    </td>
                    <td class="info"><?php echo $formProduit['recolte']['superficie_total']->render(array( 'placeholder' => "ha")) ?></td>
                    <td><?php echo $formProduit['superficie_revendique']->render(array( 'placeholder' => "ha")) ?></td>
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
            <a href="<?php echo url_for("drev_scrape_dr", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer</span>  <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
  </form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
