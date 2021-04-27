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

<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_superficies", $drev) ?>">Superficies</a></li>
    <li class=""><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_vci", $drev) ?>">Utilisation VCI</a></li>
    <li class=""><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_volumes", $drev) ?>">Volumes</a></li>
</ul>

<form role="form" action="<?php echo url_for("drev_revendication_superficies", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
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
            <?php if ($drev->hasDR()): ?>
            <tr>
                <th class="text-center col-xs-<?php if ($drev->hasDR()): ?>4<?php else: ?>6<?php endif; ?>"></th>
                <th colspan="<?php if($drev->declaration->hasVciRecolteConstitue()): ?>4<?php else: ?>3<?php endif; ?>" class="text-center striped-success small">Déclaration de Récolte</th>
                <th colspan="3" class="text-center">Déclaration de Revendication</th>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="col-xs-<?php if ($drev->hasDR()): ?>4<?php else: ?>6<?php endif; ?>">Appellation revendiquée</th>
                <?php if ($drev->hasDR()): ?>
                <th class="col-xs-1 text-center striped-success small">Volume sur place</th>
                <th class="col-xs-1 text-center striped-success small">Volume total</th>
                <th class="col-xs-1 text-center striped-success small">Usages industriels</th>
                    <?php if($drev->declaration->hasVciRecolteConstitue()): ?>
                    <th class="col-xs-1 text-center striped-success small">VCI constitué</th>
                    <?php endif ?>
                <?php endif; ?>
                <th class="<?php if ($drev->hasDR()): ?>manual-width small<?php else: ?>col-xs-2<?php endif; ?> text-center">Superficie&nbsp;Totale<br /><small class="text-muted">(ares)</small><a title="Cette superficie correspond à la superficie totale en production de votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="<?php if ($drev->hasDR()): ?>manual-width small<?php else: ?>col-xs-2<?php endif; ?> text-center">Superficie&nbsp;Vinifiée<br /><small class="text-muted">(ares)</small><a title="Cette superficie correspond à la superficie vinifiée en production de votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($form['produits'] as $key => $embedForm): ?>
                <?php $produit = $drev->get($key); ?>
                <?php include_partial("drev/revendicationSuperficieForm", array('produit' => $produit, 'form' => $embedForm, 'drev' => $drev, 'appellation' => $appellation, 'global_error_id' => $global_error_id, 'vtsgn' => false)); ?>
                <?php if(isset($embedForm['superficie_vinifiee_vtsgn'])): ?>
                    <?php include_partial("drev/revendicationSuperficieForm", array('produit' => $produit, 'form' => $embedForm, 'drev' => $drev, 'appellation' => $appellation, 'global_error_id' => $global_error_id, 'vtsgn' => true)); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($ajoutForm->hasProduits()): ?>
        <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une appellation</button>
    <?php endif; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
        	<?php if(!$drev->isNonRecoltant() && !$drev->hasDr()): ?>
				<a href="<?php echo url_for("drev_dr", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
			<?php else: ?>
        		<a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        	<?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
                <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small><?php if($drev->isNonRecoltant()): ?>en saisissant les cépages<?php else: ?>vers l'étape suivante de la revendication<?php endif; ?></small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php endif; ?>

        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
