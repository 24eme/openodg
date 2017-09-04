<?php use_helper('Float') ?>
<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>
<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<?php
$global_error_with_infos = "";
foreach ($form->getGlobalErrors() as $item):
$global_error_with_infos = $item->getMessage();
break;
endforeach;

$hasError = ($global_error_with_infos != "");
$global_error_id = substr($global_error_with_infos, 0, strrpos($global_error_with_infos, ']') + 1);
$global_error_msg = str_replace($global_error_id, '', $global_error_with_infos);
?>
<div class="page-header">
    <?php if($drev->hasDR()): ?>
        <a class="btn btn-sm btn-default-step pull-right" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;Recharger les données de la Déclaration de Récolte</a>
    <?php endif; ?>
    <?php if(!$drev->isNonRecoltant() && !$drev->hasDR()): ?>
        <a class="btn btn-warning btn-sm pull-right" href="<?php echo url_for("drev_dr_recuperation", $drev) ?>"><span class="glyphicon glyphicon-upload"></span>&nbsp;&nbsp;Récupérer les données de la Déclaration de Récolte</a>
    <?php endif; ?>
    <h2>Revendication</h2>
</div>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php if ($hasError): ?>
    <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <p>Les informations de revendication sont reprises depuis votre Déclaration de Récolte si vous avez autorisé le transfert de vos données.
    <br /><br />Veuillez vérifier leur cohérence et au besoin compléter les informations manquantes.</p>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>
    <table class="table table-bordered table-striped table-condensed" id="table-revendication">
        <thead>
            <?php if ($drev->hasDR()): ?>
            <tr>
                <th class="text-center col-xs-3"></th>
                <th colspan="4" class="text-center info">Déclaration de Récolte</th>
                <th colspan="3" class="text-center">Déclaration de Revendication</th>
                <th class="text-center col-xs-1">VCI</th>
            </tr>
            <?php endif; ?>
            <tr>
                <th>Appellation revendiquée</th>
                <!--<?php if ($drev->hasDR()): ?>
                <th class="col-xs-1 text-center info">Volume sur place<br /><small class="text-muted">(hl)</small></th>
                <th class="col-xs-1 text-center info">Volume total<br /><small class="text-muted">(hl)</small></th>
                <th class="col-xs-1 text-center info">Usages industriels<br /><small class="text-muted">(hl)</small></th>
                <?php endif; ?>
                <th class="col-xs-2 text-center" style="position: relative;">Superficie<br />Totale<br /><small class="text-muted">(ares)</small><a title="Cette superficie correspond à la superficie totale en production de votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-2 text-center" style="position: relative;">Superficie<br />Vinifiée<br /><small class="text-muted">(ares)</small><a style="position: absolute; bottom: 0; right: 0px;" title="Cette superficie correspond à la superficie vinifiée en production de votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-2 text-center" style="position: relative;">Volume<br />Revendiqué<br /><small class="text-muted">(hl)</small><a title="Le volume revendiqué correspond au volume sur place de votre Déclaration de Récolte moins les usages industriels appliqués à votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>-->
                <th class="text-center info col-xs-1">Superficie totale (L4)<br /><small class="text-muted">(ares)</small></th>
                <th class="text-center info col-xs-1">Volume récolté (L5)<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center info col-xs-1">Usages industriels (L16)<br /><small class="text-muted">(hl)</small></th>
                <th class="text-center info col-xs-1">Volume sur place (L9)<br /><small class="text-muted">(hl)</small></th>
                <th class="col-xs-1 text-center" style="position: relative;">Superficie revendiqué<br /><small class="text-muted">(ares)</small><a style="position: absolute; bottom: 0; right: 0px;" title="Cette superficie correspond à la superficie vinifiée en production de votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Volume net revendiqué sans VCI<br /><small class="text-muted">(hl)</small><a title="Le volume revendiqué correspond au volume sur place de votre Déclaration de Récolte moins les usages industriels appliqués à votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Volume net revendiqué avec VCI<br /><small class="text-muted">(hl)</small><a title="Le volume revendiqué correspond au volume sur place de votre Déclaration de Récolte moins les usages industriels appliqués à votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-1 text-center" style="position: relative;">Stock VCI avant récolte<br /><small class="text-muted">(hl)</small><a title="Le volume revendiqué correspond au volume sur place de votre Déclaration de Récolte moins les usages industriels appliqués à votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md" style="position: absolute; bottom: 0; right: 0px;"><span class="glyphicon glyphicon-question-sign"></span></a></th>

            </tr>
        </thead>
        <tbody>
            <?php foreach ($form['produits'] as $key => $embedForm): ?>
                <?php $produit = $drev->get($key); ?>
                <?php include_partial("drev/revendicationForm", array('produit' => $produit, 'form' => $embedForm, 'drev' => $drev, 'appellation' => $appellation, 'global_error_id' => $global_error_id, 'vtsgn' => false)); ?>
                <?php if(isset($embedForm['superficie_revendique_vtsgn']) || isset($embedForm['volume_revendique_vtsgn'])): ?>
                    <?php include_partial("drev/revendicationForm", array('produit' => $produit, 'form' => $embedForm, 'drev' => $drev, 'appellation' => $appellation, 'global_error_id' => $global_error_id, 'vtsgn' => true)); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($ajoutForm->hasProduits()): ?>
        <button class="btn btn-sm btn-default ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une appellation</button>
    <?php endif; ?>

    <div style="margin-top: 20px;" class="row row-margin row-button">
        <div class="col-xs-6">
			<a href="<?php if(!$drev->isNonRecoltant() && !$drev->hasDr()): ?><?php echo url_for("drev_dr", $drev) ?><?php else: ?><?php echo url_for("drev_exploitation", $drev) ?><?php endif; ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
                <?php else: ?>
                <button type="submit" class="btn btn-primary btn-upper">Continuer <?php if($drev->isNonRecoltant()): ?>en saisissant les cépages<?php else: ?>vers la répartition du vci<?php endif; ?> <span class="glyphicon glyphicon-chevron-right"></span></button>
            <?php endif; ?>

        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
