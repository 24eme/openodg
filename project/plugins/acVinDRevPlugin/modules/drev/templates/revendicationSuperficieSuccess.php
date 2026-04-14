<?php use_helper('Float'); ?>
<?php use_helper('PointsAides'); ?>

<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => DrevEtapes::ETAPE_REVENDICATION_SUPERFICIE, 'drev' => $drev, 'ajax' => true)) ?>
    <div class="page-header"><h2><?php if(DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>Revendication <?php echo $drev->periode; ?> de la superficie<?php else: ?>Produits à revendiquer<?php endif; ?></h2></div>

    <?php echo include_partial('global/flash'); ?>

    <form role="form" action="<?php echo url_for("drev_revendication_superficie", $drev) ?>" method="post" class="ajaxForm" id="form_drev_revendication_vci">

    <?php
          $can_have_vci = !($drev->getDocumentDouanierType() == SV12CsvFile::CSV_TYPE_SV12);

          echo $form->renderHiddenFields();
          echo $form->renderGlobalErrors(); ?>

	<?php if (count($form['produits']) > 0): ?>
    <table class="table table-bordered table-striped table-condensed">
        <thead>
            <tr class="<?php if(!DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>only_produit hidden<?php endif; ?>">
                <th class="text-center col-xs-2"></th>
                <th class="text-center info"><?php echo $drev->getDocumentDouanierTypeLibelle(); ?></th>
                <th colspan="<?php echo ($can_have_vci) ? 2 : 1 ; ?>" class="text-center">Déclaration de Revendication</th>
            </tr>
            <tr>
                <th class="text-left <?php if(DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>col-xs-4<?php else: ?>col-xs-8<?php endif; ?>"><?php if (count($form['produits']) > 1): ?>Produits revendiqués<?php else: ?>Produit revendiqué<?php endif; ?> <?php if(!DrevConfiguration::getInstance()->isSaisieSuperficieRevendique() && $sf_user->isAdmin()): ?><button type="button" class="btn btn-sm btn-link pull-right" style="opacity: 0.25;" onclick="document.querySelectorAll('.only_produit').forEach(function(item) { item.classList.toggle('hidden'); });"><span class="glyphicon glyphicon-eye-open"></span></button><?php endif; ?></th>

                <th class="text-center col-xs-2 info <?php if(!DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>only_produit hidden<?php endif; ?>"><div style="position: relative;">Superficie récoltée totale<br /><small class="text-muted">(ha)</small> &nbsp;<a title="<?php echo getPointAideText('drev', 'superficie_totale') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: -8px; right: -8px;"><span class="glyphicon glyphicon-question-sign"></span></a></div></th>
                <th style="position: relative;" class="text-center col-xs-2 <?php if(!DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>only_produit hidden<?php endif; ?>">Superficie revendiquée<br /><small class="text-muted">(ha)</small>&nbsp;<a title="<?php echo getPointAideText('drev', 'superficie_revendique') ?>" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
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
                        <?php echo $produit->getRawValue()->getLibelleCompletHTML() ?>
                        <?php if(DrevConfiguration::getInstance()->hasDenominationAuto()): ?>
                            <?php if(!$produit->denomination_complementaire): ?>
                            <span class="text-muted">Conventionnel</span>
                            <?php endif; ?>
                            <a title="Définir ou modifier la certification" class="text-muted transparence-md show-on-tr-hover ajax" href="<?php echo url_for('drev_revendication_produit_denomination_auto', array('id' => $drev->_id, 'hash' => str_replace('/', '__', $produit->getHash()))) ?>"><small><span class="glyphicon glyphicon-pencil"></span></small></a>
                        <?php endif; ?>
                        <a class="text-muted transparence-lg show-on-tr-hover pull-right ajax" href="<?php echo url_for('drev_revendication_cepage_suppression', array('id' => $drev->_id, 'hash' => str_replace('/', '__', $produit->getParent()->getHash()))) ?>" onclick='return confirm("Êtes vous sûr de vouloir supprimer le produit <?php echo $produit->getLibelleComplet() ?> de votre DRev <?php echo $drev->periode ?> ?");'><span class="glyphicon glyphicon-remove"></span></a>
                    </td>
                    <td class="info <?php if(!DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>only_produit hidden<?php endif; ?>"><?php echo $formProduit['recolte']['superficie_total']->render(array( 'placeholder' => "ha")) ?></td>
                    <td class="<?php if(!DrevConfiguration::getInstance()->isSaisieSuperficieRevendique()): ?>only_produit hidden<?php endif; ?>"><?php echo $formProduit['superficie_revendique']->render(array( 'placeholder' => "ha")) ?></td>
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
            <a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-4 text-center">
        <?php if ($sf_user->hasDrevAdmin() && $drev->getDocumentDouanier()): ?>
          <a href="<?php echo url_for('drev_document_douanier', $drev); ?>" class="btn btn-default" >
              <span class="glyphicon glyphicon-file"></span>&nbsp;&nbsp;<?php echo $drev->getDocumentDouanierType() ?>
          </a>
        <?php endif; ?>
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
        </div>
    </div>
  </form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
