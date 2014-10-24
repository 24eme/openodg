<?php use_helper('Float') ?>

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
    <table class="table table-striped">
        <thead>
            <?php if ($drev->hasDR()): ?>
            <tr>
                <th class="text-center"></th>
                <th colspan="3" class="text-center striped-success small">Déclaration de Récolte</th>
                <th colspan="2" class="text-center">Déclaration de Revendication</th>
            </tr>
            <?php endif; ?>
            <tr>
                <th class="col-xs-3">Appellation revendiquée</th>
                <?php if ($drev->hasDR()): ?>
                <th class="col-xs-1 text-center striped-success small">Volume sur place</th>
                <th class="col-xs-1 text-center striped-success small">Volume total</th>
                <th class="col-xs-1 text-center striped-success small">Usages industriels</th>
                <?php endif; ?>
                <th class="col-xs-2 text-center"><a title="Cette superficie correspond à la superficie totale de votre exploitation en production" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a>Superficie Totale<br /><small class="text-muted">(ares)</small></th>
                <th class="col-xs-2 text-center"><a title="Le volume revendiqué correspond au volume sur place de votre Déclaration de Récolte moins les usages industriels appliqués à votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a>Volume Revendiqué<br /><small class="text-muted">(hl)</small></th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($form['produits'] as $key => $embedForm) :
            $produit = $drev->get($key);
            $global_error_class = ($appellation && ($appellation_hash == $key))? 'error_field_to_focused' : '';
            ?>
            <tr class="<?php echo (isset($embedForm['superficie_revendique'])) ? 'with_superficie' : ''; ?>" >
                <td><?php echo $produit->getLibelleComplet() ?></td>
                <?php if ($drev->hasDR()): ?>
                    <?php if (!$produit->detail->superficie_total): ?>
                        <td class="striped-success"></td>
                        <td class="striped-success"></td>
                        <td class="striped-success"></td>
                    <?php else: ?>
                        <td class="text-right striped-success small">
                          <?php echoFloat($produit->detail->volume_sur_place); ?>&nbsp;<small>hl</small>
                        </td>
                        <td class="text-right striped-success small">
                          <?php echoFloat($produit->detail->volume_total); ?>&nbsp;<small >hl</small>
                        </td>
                        <td class="text-right striped-success small">
                          <?php echoFloat($produit->detail->usages_industriels_total); ?>&nbsp;<small>hl</small>
                        </td>
                <?php endif; ?>
                <?php endif; ?>
                <td class="text-center">              
                    <?php if (isset($embedForm['superficie_revendique'])): ?>
                    <?php
                    $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'surface') ||
                    ('drev_produits[produits]' . $global_error_id == $embedForm['superficie_revendique']->renderName())) ?
                    'error_field_to_focused' : '';
                    ?>
                    <div class="form-group <?php if ($global_error_class): ?>has-error<?php endif; ?>">
                        <?php echo $embedForm['superficie_revendique']->renderError() ?>
                        <div class="col-xs-10 col-xs-offset-1">
                            <?php echo $embedForm['superficie_revendique']->render(array('class' => 'form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "ares")) ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="col-xs-10 text-right">
                        <?php echoFloat($produit->detail->superficie_total); ?>
                        </div>
                    <?php endif; ?>
                </td>   
                <td class="text-center">
                    <?php if (isset($embedForm['volume_revendique'])): ?>
                    <?php
                    $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'volume') ||
                    ('drev_produits[produits]' . $global_error_id == $embedForm['volume_revendique']->renderName())) ?
                    'error_field_to_focused' : '';
                    ?>
                    <div class="form-group <?php if ($global_error_class): ?>has-error<?php endif; ?>">
                        <?php echo $embedForm['volume_revendique']->renderError() ?>
                        <div class="col-xs-10 col-xs-offset-1">
                            <?php echo $embedForm['volume_revendique']->render(array('class' => 'disabled form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "hl")) ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php echoFloat($produit->volume_revendique); ?> <small class="text-muted">hl</small>
                    <?php endif; ?>
                </td>
            </tr>
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
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small><?php if($drev->isNonRecoltant()): ?>en saisissant les cépages<?php else: ?>vers la dégustation conseil<?php endif; ?></small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php endif; ?>

        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
