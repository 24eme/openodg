<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaire' => $parcellaire )); ?>
<?php include_partial('step', array('parcellaire' => $parcellaire, 'step' => 'acheteurs', 'identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Répartition des acheteurs&nbsp;<small>Les parcelles sont à ventiler en fonction de vos acheteurs.</small></h2>
</div>

<ul class="nav nav-tabs">
    <li class="active"><a href="<?php echo url_for('parcellaire_acheteurs', array('id' => $parcellaire->_id)) ?>" class="ajax">Répartition par produits</a></li>
    <li <?php if(!$parcellaire->hasProduitWithMultipleAcheteur()): ?>class="disabled" style="opacity: 0.4;"<?php endif; ?>><a href="<?php echo url_for('parcellaire_acheteurs_parcelles', array('id' => $parcellaire->_id)) ?>" class="ajax">Répartition par parcelles</a></li>
</ul>

<p class="text-muted">Par soucis de simplification de saisie nous vous proposons de répartir vos acheteurs par produit, ainsi toutes les parcelles du produit seront affectées à la destination indiquée. En cas de destination plurielle vous serez invité à préciser ces destinations par parcelle dans un écran suivant.</p>

<form action="<?php echo url_for("parcellaire_acheteurs", $parcellaire) ?>" method="post" class="ajaxForm">
    <?php echo $form->renderHiddenFields() ?>
    <?php if($form->hasGlobalErrors()): ?><div class="alert alert-danger"><?php echo $form->renderGlobalErrors(array("class" => "text-left")) ?></div><?php endif; ?>
    <div class="row">
        <div class="col-xs-12">
            <?php if($form->hasProduits()): ?>
            <div id="listes_cepages" class="list-group">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th class="col-xs-5"></th>
                            <?php foreach($form->getAcheteurs() as $libelle): ?>
                            <th class="text-center"><?php echo $libelle ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach($form as $key => $field) : ?>
                    <?php if($field->isHidden()) { continue; } ?>
                    <tr>
                        <td>
                            <?php echo $field->renderLabel(null, array('style' => 'font-weight: normal')) ?>
                            <?php echo $field->renderError() ?>
                        </td>
                        <?php foreach($field->getWidget()->getChoices() as $key => $option): ?>
                        <td class="text-center tdAcheteur" >
                            <input class="acheteur_checkbox" type="checkbox" id="<?php echo $field->renderId() ?>_<?php echo $key ?>" name="<?php echo $field->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($field->getValue()) && in_array($key, $field->getValue())): ?>checked="checked"<?php endif; ?> />
                        </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p class="text-muted">Vous n'avez affecté aucune parcelle, vous n'avez donc aucune répartition à effectuer, vous pouvez ignorer cet étape.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
            <a href="<?php echo url_for("parcellaire_parcelles", array('sf_subject' => $parcellaire, 'appellation' => ParcellaireAffectationClient::getInstance()->getFirstAppellation($parcellaire->getTypeParcellaire()))) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
            <?php if ($parcellaire->exist('etape') && $parcellaire->etape == ParcellaireAffectationEtapes::ETAPE_VALIDATION): ?>
                <button id="btn-validation" type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Retourner <small>à la validation</small></button>
            <?php else: ?>
                <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
            <?php endif; ?>
        </div>
    </div>
</form>
