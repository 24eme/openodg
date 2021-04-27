<?php use_helper('Float') ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<?php
if (isset($form) && $form):
  $global_error_with_infos = "";
  foreach ($form->getGlobalErrors() as $item):
    $global_error_with_infos = $item->getMessage();
  break;
  endforeach;
  $hasError = ($global_error_with_infos != "");
  $global_error_id = substr($global_error_with_infos, 0, strrpos($global_error_with_infos, ']') + 1);
  $global_error_msg = str_replace($global_error_id, '', $global_error_with_infos);
endif;
?>

<div class="page-header no-border">
    <h2>Revendication</h2>
</div>

<?php include_component('drev', 'stepRevendication', array('drev' => $drev, 'noeud' => $noeud, 'step' => 'vci')) ?>

<?php if(count($drev->getProduitsVCI())): ?>
<form role="form" action="<?php echo url_for("drev_revendication_cepage_vci", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php if ($hasError): ?>
    <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <p>Les informations de revendication sont reprises depuis votre Déclaration de Récolte si vous avez autorisé le transfert de vos données.
    <br /><br />Veuillez vérifier leur cohérence et au besoin compléter les informations manquantes.</p>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('error')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('error') ?></p>
    <?php endif; ?>
    <table class="table table-striped table-condensed" id="table-revendication">
        <thead>
        	<tr>
                <th colspan="2" class="text-center"></th>
                <th colspan="4" class="text-center">Revendication <?php echo $drev->campagne ?></th>
            </tr>
            <tr>
                <th class="col-xs-4">Appellation revendiquée</th>
                <th class="text-center manual-width small" style="width: 118px; padding: 0 !important;">Stock VCI <?php echo $drev->campagne-1 ?></th>
                <th class="text-center manual-width small" style="width: 118px; padding: 0 !important;">Destruction</th>
                <th class="text-center manual-width small" style="width: 118px; padding: 0 !important;">Complément de la récolte</th>
                <th class="text-center manual-width small" style="width: 118px; padding: 0 !important;">Substitution</th>
                <th class="text-center manual-width small" style="width: 118px; padding: 0 !important;">Rafraichissement</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form['cepages'] as $key => $embedForm): ?>
                <?php $produit = $drev->get($key)->getRawValue(); ?>
                <?php include_partial("drev/revendicationVCIForm", array('produit' => $produit, 'form' => $embedForm, 'drev' => $drev, 'appellation' => $appellation, 'global_error_id' => $global_error_id, 'vtsgn' => false)); ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: //pas de registrevci ?>
    <p>Aucun produit n'a été déclaré ayant du stock VCI.</p>
<?php endif; ?>

    <div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_revendication_cepage", $drev->declaration->getAppellations()->getLast()) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'appellation précédente</small></a></div>
    <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button  id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
            <?php else: ?>
            <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer <small>vers le récapitulatif</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        <?php endif; ?>
    </div>
</div>


</form>
