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
    <li class=""><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_superficies", $drev) ?>">Superficies</a></li>
    <li class="active"><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_vci", $drev) ?>">Utilisation VCI</a></li>
    <li class=""><a role="tab" class="ajax" href="<?php echo url_for("drev_revendication_volumes", $drev) ?>">Volumes</a></li>
</ul>

<?php if (isset($registrevci) && $registrevci) : ?>

<form role="form" action="<?php echo url_for("drev_revendication_vci", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php if ($hasError): ?>
    <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('error')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('error') ?></p>
    <?php endif; ?>
    <p>Veuillez indiquer l'utilisation de votre VCI stocké en <?php echo($drev->campagne - 1) ?>. Les informations proviennent de votre registre VCI.</p>
    <table class="table table-striped table-condensed" id="table-revendication">
        <thead>
        	<tr>
                <th colspan="2" class="text-center"></th>
                <th colspan="4" class="text-center">Revendication <?php echo $drev->campagne ?></th>
            </tr>
            <tr>
                <th class="">Appellation revendiquée</th>
                <th class="text-center manual-width small">Stock VCI <?php echo $registrevci->campagne ?></th>
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
    <p>Pour le VCI constitué cette année, il sera automatiquement repris dans votre registre <?php echo($drev->campagne) ?>.</p>
<?php else: //pas de registrevci ?>
<p>Vous ne disposez pas de registre VCI, vous ne pouvez donc pas revendiquer de volume issu du VCI.</p><p>S'il s'agit d'une erreur, contactez l'AVA.</p>
<?php endif; ?>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
        	<?php if(!$drev->isNonRecoltant() && !$drev->hasDr()): ?>
				<a href="<?php echo url_for("drev_dr", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
			<?php else: ?>
        		<a href="<?php echo url_for("drev_revendication_superficies", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a>
        	<?php endif; ?>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
                <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small><?php if($drev->isNonRecoltant()): ?>en saisissant les cépages<?php else: ?>vers l'étape suivante de la revendication'<?php endif; ?></small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php endif; ?>

        </div>
    </div>
</form>
