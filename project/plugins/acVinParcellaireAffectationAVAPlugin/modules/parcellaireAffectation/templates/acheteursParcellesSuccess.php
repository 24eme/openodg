<?php include_partial('parcellaireAffectation/breadcrumb', array('parcellaire' => $parcellaire )); ?>
<?php include_partial('step', array('parcellaire' => $parcellaire, 'step' => 'acheteurs', 'identifiant' => 'XXX')); ?>

<div class="page-header">
    <h2>Répartition des acheteurs&nbsp;<small>Les parcelles sont à ventiler en fonction de vos acheteurs.</small></h2>
</div>

<ul class="nav nav-tabs">
    <li><a href="<?php echo url_for('parcellaire_acheteurs', array('id' => $parcellaire->_id)) ?>" class="ajax">Répartition par produits</a></li>
    <li class="active"><a href="<?php echo url_for('parcellaire_acheteurs_parcelles', array('id' => $parcellaire->_id)) ?>" class="ajax">Répartition par parcelles</a></li>
</ul>

<p class="text-muted">Vous pouvez dans cet écran affiner les destinations de vos parcelles, dans le cas où un même produit à plusieurs destinations</p>

<form action="<?php echo url_for("parcellaire_acheteurs_parcelles", $parcellaire) ?>" method="post" class="ajaxForm">
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
                    <?php $categorieField = null; ?>
                    <?php foreach($form as $key => $field) : ?>
                    <?php if($field->isHidden()) { continue; } ?>
                    <?php $isCategorie = false; ?>
                    <?php if(!preg_match('|/detail/|', $key)): ?>
                        <?php $isCategorie = true; ?>
                        <?php $categorieField = $field; ?>
                    <?php endif; ?>
                    <tr style="<?php if ($isCategorie): ?>border-top: 1px solid #cbcbcb;<?php endif; ?>">
                        <td>
                            <?php if ($isCategorie): ?>
                                <?php echo $field->renderLabel(null) ?>
                            <?php else: ?>
                                <?php echo $field->renderLabel(null, array('style' => 'font-weight: normal; padding-left: 8px;')) ?>
                            <?php endif; ?>
                            <?php echo $field->renderError() ?>
                        </td>
                        <?php foreach($field->getWidget()->getChoices() as $key => $option): ?>
                        <td class="text-center tdAcheteur">
                            <input class="acheteur_checkbox" <?php if($categorieField->getValue() && !in_array($key, $categorieField->getValue()) || $isCategorie): ?>disabled="disabled"<?php endif; ?> type="checkbox" id="<?php echo $field->renderId() ?>_<?php echo $key ?>" name="<?php echo $field->renderName() ?>[]" value="<?php echo $key ?>" <?php if(is_array($field->getValue()) && in_array($key, $field->getValue())): ?>checked="checked"<?php endif; ?> style="<?php if (!in_array($key, $categorieField->getValue())): ?>opacity: 0.5;<?php endif; ?>" />
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
            <a href="<?php echo url_for("parcellaire_acheteurs", array('sf_subject' => $parcellaire)) ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
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
