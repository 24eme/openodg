<?php use_helper('Float'); ?>
<?php use_javascript('facture.js'); ?>

<?php include_partial('admin/menu', array('active' => 'facturation')); ?>

<form action="" method="post" class="form-horizontal">

    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <div class="col-xs-2 text-center"><strong>Quantité</strong></div>
                <div class="col-xs-5 text-center"><strong>Libellé</strong></div>
                <div class="col-xs-2 text-center"><strong>Prix unitaire</strong></div>
                <div class="col-xs-2 text-center"><strong>Montant HT</strong></div>
                <div class="col-xs-1 text-center"><strong>Taux TVA</strong></div>
            </div>
        </div>
        <div class="col-xs-12">
            <?php foreach($form['lignes'] as $f_ligne): ?>
            <div class="form-group" style="<?php echo (!$f_ligne['libelle']->getValue()) ? "opacity: 0.6" : null ?>">
                <div class="col-xs-5 col-xs-offset-2">
                <?php echo $f_ligne['libelle']->renderError() ?>
                <?php echo $f_ligne['libelle']->render(array('class' => 'form-control input-lg')); ?>
                </div>
                <div class="col-xs-2 col-xs-offset-2 text-right">
                <?php $ids_montant_ht = array(); ?>
                <?php foreach($f_ligne['details'] as $f_detail): $ids_montant_ht[] = "#".$f_detail['montant_ht']->renderId(); endforeach; ?>
                <?php echo $f_ligne['montant_ht']->renderError(); ?>
                <?php echo $f_ligne['montant_ht']->render(array('class' => 'form-control input-lg text-right data-sum-element', 'data-sum' => implode(" + ", $ids_montant_ht), "readonly" => "readonly", 'data-sum-element' => "#montant_ht")); ?>
                <?php $ids_montant_tva = array(); ?>
                <?php foreach($f_ligne['details'] as $f_detail): $ids_montant_tva[] = "#".$f_detail['montant_tva']->renderId(); endforeach; ?>
                <?php echo $f_ligne['montant_tva']->renderError(); ?>
                <?php echo $f_ligne['montant_tva']->render(array('class' => 'form-control input-lg text-right data-sum-element', 'data-sum' => implode(" + ", $ids_montant_tva), "readonly" => "readonly", 'data-sum-element' => "#montant_tva", 'readonly' => 'readonly', 'type' => 'hidden')); ?>
                </div>
            </div>
                <div class="form-group">
                    <div class="col-xs-12">
                    <?php foreach($f_ligne['details'] as $f_detail): ?>
                        <div class="form-group" style="<?php echo (!$f_detail['libelle']->getValue()) ? "opacity: 0.6" : null ?>">
                            <div class="col-xs-2">
                                <?php echo $f_detail['quantite']->renderError() ?>
                                <?php echo $f_detail['quantite']->render(array('class' => 'form-control text-right data-sum-element', 'data-sum-element' => "#".$f_detail['montant_ht']->renderId())); ?>
                            </div>
                            <div class="col-xs-5">
                                <?php echo $f_detail['libelle']->renderError() ?>
                                <?php echo $f_detail['libelle']->render(array('class' => 'form-control')); ?>
                            </div>
                            <div class="col-xs-2">
                                <?php echo $f_detail['prix_unitaire']->renderError() ?>
                                <?php echo $f_detail['prix_unitaire']->render(array('class' => 'form-control text-right data-sum-element', 'data-sum-element' => "#".$f_detail['montant_ht']->renderId())); ?>
                            </div>
                            <div class="col-xs-2 text-right">
                                <?php echo $f_detail['montant_ht']->renderError() ?>
                                <?php echo $f_detail['montant_ht']->render(
                                    array('class' => 'form-control text-right data-sum-element', 
                                          'data-sum' => sprintf("#%s * #%s", $f_detail['quantite']->renderId(), $f_detail['prix_unitaire']->renderId()), 
                                          'data-sum-element' => json_encode(array("#".$f_detail['montant_tva']->renderId(), "#".$f_ligne['montant_ht']->renderId())),
                                          "readonly" => "readonly")); ?>
                            </div>
                            <div class="col-xs-1">
                                <?php echo $f_detail['taux_tva']->renderError() ?>
                                <?php echo $f_detail['taux_tva']->render(array('class' => 'form-control text-right data-sum-element', 'data-sum-element' => "#".$f_detail['montant_tva']->renderId())); ?>
                                <?php echo $f_detail['montant_tva']->renderError() ?>
                                <?php echo $f_detail['montant_tva']->render(array('class' => 'form-control text-right data-sum-element' , 'data-sum' => sprintf("#%s * #%s", $f_detail['montant_ht']->renderId(), $f_detail['taux_tva']->renderId()), 'data-sum-element' => '#'.$f_ligne['montant_tva']->renderId(), 'readonly' => 'readonly', 'type' => 'hidden')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-xs-12">
            <div class="form-group form-group-lg">
                <div class="col-xs-2 text-center"></div>
                <div class="col-xs-5 text-center"></div>
                <div class="col-xs-2 text-center"><label class="control-label">Montant HT</label></div>
                <?php $ids_total_ht = array(); ?>
                <?php foreach($form['lignes'] as $f_ligne): $ids_total_ht[] = "#".$f_ligne['montant_ht']->renderId(); endforeach; ?>
                <div class="col-xs-2 text-center"><strong><input id="montant_ht" type="text" class="form-control input-lg text-right" data-sum="<?php echo implode(" + ", $ids_total_ht) ?>" readonly="readonly" value="<?php echo $facture->total_ht ?>" /></strong></div>
            </div>
            <div class="form-group form-group-lg">
                <div class="col-xs-2 text-center"></div>
                <div class="col-xs-5 text-center"></div>
                <div class="col-xs-2 text-center"><label class="control-label">Montant TVA</label></div>
                <?php $ids_total_tva = array(); ?>
                <?php foreach($form['lignes'] as $f_ligne): $ids_total_tva[] = "#".$f_ligne['montant_tva']->renderId(); endforeach; ?>
                <div class="col-xs-2 text-center"><strong><input id="montant_tva" type="text" class="form-control input-lg text-right" data-sum="<?php echo implode(" + ", $ids_total_tva) ?>" readonly="readonly" value="<?php echo $facture->total_taxe ?>" /></strong></div>
            </div>
            <div class="form-group form-group-lg">
                <div class="col-xs-2 text-center"></div>
                <div class="col-xs-5 text-center"></div>
                <div class="col-xs-2 text-center"><label class="control-label">Montant TTC</label></div>
                <div class="col-xs-2 text-center"><strong><input id="total_ttc" type="text" class="form-control input-lg text-right" readonly="readonly" value="<?php echo $facture->total_ttc ?>" /></strong></div>
            </div>
        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-6 text-left">
            <a class="btn btn-danger btn-lg btn-upper" href="<?php echo url_for('facturation') ?>">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</a>
        </div>
    </div>

</form>
