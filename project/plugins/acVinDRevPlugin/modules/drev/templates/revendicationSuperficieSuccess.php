<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE, 'drev' => $drev, 'ajax' => true)) ?>

    <div class="page-header"><h2>Revendication <?php echo $drev->periode; ?> de la superficie</h2></div>

    <?php echo include_partial('global/flash'); ?>

    <form role="form" action="<?php echo url_for("drev_revendication_superficie", $drev) ?>" method="post" class="ajaxForm" id="form_drev_revendication_vci">

    <?php
          $can_have_vci = !($drev->getDocumentDouanierType() == SV12CsvFile::CSV_TYPE_SV12);

          echo $form->renderHiddenFields();
          echo $form->renderGlobalErrors(); ?>

	<?php if (count($form['produits']) > 0): ?>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
        	<tr>
                <th class="text-center col-xs-2"></th>
                <th class="text-center info"><?php echo $drev->getDocumentDouanierTypeLibelle(); ?></th>
                <th colspan="<?php echo ($can_have_vci) ? 2 : 1 ; ?>" class="text-center">Déclaration de Revendication</th>
            </tr>
            <tr>
                <th class="text-left col-xs-4"><?php if (count($form['produits']) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?></th>
                <th class="text-center col-xs-2 info"><div style="position: relative;">Superficie récoltée totale<br /><small class="text-muted">(ha)</small> &nbsp;<a title="<?php echo getPointAideText('drev', 'superficie_totale') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: -8px; right: -8px;"><span class="glyphicon glyphicon-question-sign"></span></a></div></th>
                <th style="position: relative;" class="text-center col-xs-2">Superficie revendiquée<br /><small class="text-muted">(ha)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'superficie_revendique') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php if ($can_have_vci): ?>
                <th style="position: relative;" class="text-center col-xs-2">Possède du stock VCI<br />&nbsp;<a title="<?php echo getPointAideText('drev', 'possede_vci') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="edit_vci">
            <?php foreach($form['produits'] as $hash => $formProduit): ?>
                <?php $produit = $drev->get($hash); ?>
                <tr class="produits vertical-center">
                    <td>
                    	<a href="<?php echo url_for('drev_revendication_cepage_suppression', array('id' => $drev->_id, 'hash' => str_replace('/', '__', $produit->getParent()->getHash()))) ?>" onclick='return confirm("Êtes vous sûr de vouloir supprimer le produit <?php echo $produit->getLibelleComplet() ?> de votre DRev <?php echo $drev->periode ?> ?");'><span class="glyphicon glyphicon-remove-sign text-muted"></span></a>
                    	<?php echo $produit->getLibelleComplet() ?>
                    </td>
                    <td class="info"><?php echo $formProduit['recolte']['superficie_total']->render(array( 'placeholder' => "ha")) ?></td>
                    <td><?php echo $formProduit['superficie_revendique']->render(array( 'placeholder' => "ha")) ?></td>
                    <?php if ($can_have_vci): ?>
                    <td class="text-center pointer_checkbox"><?php echo (isset($formProduit['has_stock_vci'])) ? $formProduit['has_stock_vci']->render() : ""; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
    <p>Aucun produit a revendiquer</p>
    <?php endif; ?>

    <?php if ($ajoutForm->hasProduits()): ?>
        <button class="btn btn-sm btn-default ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit</button>
    <?php endif; ?>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-4">
            <a href="<?php echo url_for("drev_dr", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
        <?php if ($sf_user->hasDrevAdmin() && $drev->hasDocumentDouanier()): ?>
          <a href="<?php echo url_for('drev_document_douanier', $drev); ?>" class="btn btn-default <?php if(!$drev->hasDocumentDouanier()): ?>disabled<?php endif; ?>" >
              <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
          </a>
        <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer</span>  <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
  </form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
