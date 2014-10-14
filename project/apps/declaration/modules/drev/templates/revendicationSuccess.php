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
    <h2>Revendication</h2>
</div>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post" class="ajaxForm" id="form_revendication_drev_<?php echo $drev->_id; ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php if ($hasError): ?>
    <div class="alert alert-danger" role="alert"><?php echo $global_error_msg; ?></div>
    <?php endif; ?>
    <p>Les informations de revendication sont reprise depuis la déclaration de récolte quand cela est possible. 
    <br /><br />Veuillez vérifier leur cohérence et au besoin compléter les informations manquantes.</p>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>
    
    <table class="table table-striped">
        <thead>
            <tr>
                <th class="col-xs-4">Appellation revendiquée</th>
                <th class="col-xs-2 text-center">Superficie Totale<br /><small class="text-muted"> (ares)</small><a title="Cette superficie corrspond à la superficie totale de votre exploitation en production" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <th class="col-xs-2 text-center">Volume&nbsp;Revendiqué<br /><small class="text-muted">(hl)</small><a title="Le volume revendiqué corrspond au volume sur place de votre déclaration de récolte moins les usages industriels appliqués à votre exploitation" data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-md pull-right"><span class="glyphicon glyphicon-question-sign"></span></a></th>
                <?php if ($drev->hasDR()): ?>
                <th class="col-xs-1 text-center">Volume sur place</th>
                <th class="col-xs-2 text-center text-muted">Volume total <small>dont</small> Usages industriels</th>
                <?php endif; ?>
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
                <td class="text-center">              
                    <?php if (isset($embedForm['superficie_revendique'])): ?>
                    <?php
                    $global_error_class = ((($global_error_class == 'error_field_to_focused') && $appellation_field == 'surface') ||
                    ('drev_produits[produits]' . $global_error_id == $embedForm['superficie_revendique']->renderName())) ?
                    'error_field_to_focused' : '';
                    ?>
                    <div class="form-group <?php if ($global_error_class): ?>has-error<?php endif; ?>">
                        <?php echo $embedForm['superficie_revendique']->renderError() ?>
                        <div class="col-xs-8 col-xs-offset-2">
                            <?php echo $embedForm['superficie_revendique']->render(array('class' => 'form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "ares")) ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php echoFloat($produit->detail->superficie_total); ?> <small class="text-muted">ares</small>
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

                        <?php if($produit->detail->volume_sur_place): ?>
                        <a title="Afin de calculer le volume revendiqué il faut: <br />
                         1. Déterminer la partie du total des usages industriels dédiée au volume sur place. <br />
                         2. Déduire le volume obtenu au volume sur place" data-placement="auto" data-toggle="tooltip" data-html="true" class="btn-tooltip btn btn-md pull-right col-xs-2"><span class="glyphicon glyphicon-question-sign"></span></a>
                        <?php endif; ?>

                        <?php echo $embedForm['volume_revendique']->renderError() ?>
                        <div class="col-xs-8 col-xs-offset-2">
                            <?php $options = array('class' => 'form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "hl"); ?>

                            <?php echo $embedForm['volume_revendique']->render(array('class' => 'disabled form-control text-right input-rounded num_float ' . $global_error_class, 'placeholder' => "hl")) ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <?php echoFloat($produit->volume_revendique); ?> <small class="text-muted">hl</small>
                    <?php endif; ?>
                </td>
                <?php if ($drev->hasDR()): ?>
                    <?php if (!$produit->detail->volume_sur_place): ?>
                        <td class=""></td>
                        <td></td>
                    <?php else: ?>
                        <td class="text-right">
                          <?php echoFloat($produit->detail->volume_sur_place); ?>&nbsp;<small class="text-muted">hl</small>
                        </td>
                        <td class="text-right text-muted">
                          <?php echoFloat($produit->detail->volume_total); ?>&nbsp;<small class="text-muted">hl</small>
                          <small>dont</small>
                          <?php echoFloat($produit->detail->usages_industriels_total); ?>&nbsp;<small class="text-muted">hl</small>
                        </td>
                  <?php endif; ?>
                  <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php if ($ajoutForm->hasProduits()): ?>
            <tr>
                <td>
                    <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter une appellation</button>
                </td>
                <td></td><td></td>
                <?php if ($drev->hasDR()): ?>
                    <td></td><td></td>
                <?php endif; ?>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="row row-margin row-button">
        <div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'étape précédente</small></a></div>
        <div class="col-xs-6 text-right">
            <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span> Retourner <small>à la validation</small>&nbsp;&nbsp;</button>
                <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer <small>en saisissant les cépages</small>&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php endif; ?>

        </div>
    </div>
</form>

<?php include_partial('drev/popupAjoutForm', array('url' => url_for('drev_revendication_ajout', $drev), 'form' => $ajoutForm)); ?>
