<?php use_helper('Float'); ?>
<?php use_helper('Float'); ?>
<?php use_javascript('facture.js'); ?>

<?php include_partial('admin/menu', array('active' => 'facturation')); ?>

<h2><?php if($facture->numero_ava): ?>Édition de <?php if($facture->isAvoir()): ?>l'<?php else: ?>la <?php endif; ?><?php else: ?>Création <?php if($facture->isAvoir()): ?>d'un<?php else: ?>d'une<?php endif; ?> <?php endif; ?><?php if($facture->isAvoir()): ?>Avoir <?php else: ?>Facture<?php endif; ?> <?php if($facture->numero_ava): ?>n°<?php echo $facture->numero_ava; ?><?php endif; ?> <small>(Daté du <?php $date = new DateTime($facture->date_facturation); echo $date->format('d/m/Y'); ?>)</small>
<br />
    <small><?php echo $facture->declarant->raison_sociale ?>
    (<?php echo $facture->declarant->adresse ?> <?php echo $facture->declarant->code_postal ?> <?php echo $facture->declarant->commune ?>)</small>
</h2>

<form action="" method="post" class="form-horizontal">

    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="col-xs-7">
                <div class="row">
                    <div class="col-xs-3 text-center lead text-muted">Quantité</div>
                    <div class="col-xs-6 text-center lead text-muted">Libellé / Code comptable</div>
                </div>
            </div>
            <div class="col-xs-3">
                <div class="row">
                    <div class="col-xs-5 text-center lead text-muted">Prix&nbsp;U.</div>
                    <div class="col-xs-7 text-center lead text-muted">Total</div>
                </div>
            </div>
            <div class="col-xs-2 text-center lead text-muted">Taux&nbsp;TVA</div>
        </div>
        <div class="col-xs-12">
            <?php foreach($form['lignes'] as $f_ligne): ?>
            <div id="<?php echo $f_ligne->renderId() ?>" class="form-group line" style="<?php echo (!$f_ligne['libelle']->getValue()) ? "opacity: 0.6" : null ?>">
                <div class="col-xs-7">
                    <div class="row">
                        <div class="col-xs-3">
                            
                        </div>
                        <div class="col-xs-6">
                        <?php echo $f_ligne['libelle']->renderError() ?>
                        <?php echo $f_ligne['libelle']->render(array('class' => 'form-control input-lg')); ?>
                        </div>
                        <div class="col-xs-3">
                            <?php echo $f_ligne['produit_identifiant_analytique']->renderError() ?>
                            <?php echo $f_ligne['produit_identifiant_analytique']->render(array('class' => 'form-control input-lg bg-info')); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-3 text-right">
                    <div class="row">
                        <div class="col-xs-7 col-xs-offset-5">
                <?php $ids_montant_ht = array(); ?>
                <?php foreach($f_ligne['details'] as $f_detail): $ids_montant_ht[] = "#".$f_detail['montant_ht']->renderId(); endforeach; ?>
                <?php echo $f_ligne['montant_ht']->renderError(); ?>
                <?php echo $f_ligne['montant_ht']->render(array('class' => 'form-control input-lg text-right data-sum-element', 'data-sum' => implode(" + ", $ids_montant_ht), "readonly" => "readonly", 'data-sum-element' => "#total_ht")); ?>
                <?php $ids_montant_tva = array(); ?>
                <?php foreach($f_ligne['details'] as $f_detail): $ids_montant_tva[] = "#".$f_detail['montant_tva']->renderId(); endforeach; ?>
                <?php echo $f_ligne['montant_tva']->renderError(); ?>
                <?php echo $f_ligne['montant_tva']->render(array('class' => 'form-control input-lg text-right data-sum-element', 'data-sum' => implode(" + ", $ids_montant_tva), "readonly" => "readonly", 'data-sum-element' => "#total_tva", 'readonly' => 'readonly', 'type' => 'hidden')); ?>
                        </div>
                    </div>
                </div>
                <div class="col-xs-2">
                    <!--<button type="button" class="btn btn-danger btn-lg hidden"><span class="glyphicon glyphicon-trash"></span></button>-->
                </div>
            </div>
                <div class="form-group">
                    <div class="col-xs-12">
                    <?php foreach($f_ligne['details'] as $f_detail): ?>
                        <?php echo $f_detail['quantite']->renderError() ?>
                        <div id="<?php echo $f_detail->renderId() ?>" class="form-group line" style="<?php echo (!$f_detail['libelle']->getValue()) ? "opacity: 0.6" : null ?>">
                            <div class="col-xs-7">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <?php echo $f_detail['quantite']->renderError() ?>
                                        <?php echo $f_detail['quantite']->render(array('class' => 'form-control text-right data-sum-element', 'data-sum-element' => "#".$f_detail['montant_ht']->renderId())); ?>
                                    </div>
                                    <div class="col-xs-9">
                                        <?php echo $f_detail['libelle']->renderError() ?>
                                        <?php echo $f_detail['libelle']->render(array('class' => 'form-control')); ?>
                                    </div>  
                                </div>
                            </div>
                            <div class="col-xs-3">
                                <div class="row">
                                    <div class="col-xs-5">
                                    <?php echo $f_detail['prix_unitaire']->renderError() ?>
                                    <?php echo $f_detail['prix_unitaire']->render(array('class' => 'form-control text-right data-sum-element', 'data-sum-element' => "#".$f_detail['montant_ht']->renderId())); ?>
                                    </div>
                                    <div class="col-xs-7">
                                    <?php echo $f_detail['montant_ht']->renderError() ?>
                                    <?php echo $f_detail['montant_ht']->render(
                                        array('class' => 'form-control text-right data-sum-element', 
                                              'data-sum' => sprintf("#%s * #%s", $f_detail['quantite']->renderId(), $f_detail['prix_unitaire']->renderId()), 
                                              'data-sum-element' => json_encode(array("#".$f_detail['montant_tva']->renderId(), "#".$f_ligne['montant_ht']->renderId())),
                                              "readonly" => "readonly")); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xs-2">
                                <div class="row">
                                    <div class="col-xs-7">
                                <?php echo $f_detail['taux_tva']->renderError() ?>
                                <?php echo $f_detail['taux_tva']->render(array('class' => 'form-control text-right data-sum-element', 'data-sum-element' => "#".$f_detail['montant_tva']->renderId())); ?>
                                <?php echo $f_detail['montant_tva']->renderError() ?>
                                <?php echo $f_detail['montant_tva']->render(array('class' => 'form-control text-right data-sum-element' , 'data-sum' => sprintf("#%s * #%s", $f_detail['montant_ht']->renderId(), $f_detail['taux_tva']->renderId()), 'data-sum-element' => '#'.$f_ligne['montant_tva']->renderId(), 'readonly' => 'readonly', 'type' => 'hidden')); ?>
                                    </div>
                                    <div class="col-xs-5">
                                        <button data-clean-line="#<?php echo $f_detail->renderId() ?>" type="button" class="btn btn-danger data-clean-line hidden"><span class="glyphicon glyphicon-trash"></span></button>
                                    </div>
                                </div>
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
                <div class="col-xs-4 text-center"></div>
                <div class="col-xs-2 text-center"><label class="control-label lead">Total HT</label></div>
                <?php $ids_total_ht = array(); ?>
                <?php foreach($form['lignes'] as $f_ligne): $ids_total_ht[] = "#".$f_ligne['montant_ht']->renderId(); endforeach; ?>
                <div class="col-xs-2 text-center"><strong><input id="total_ht" type="text" class="form-control input-lg text-right data-sum-element" data-sum="<?php echo implode(" + ", $ids_total_ht) ?>" data-sum-element="#total_ttc" readonly="readonly" value="<?php echo $facture->total_ht ?>" /></strong></div>
            </div>
            <div class="form-group form-group-lg">
                <div class="col-xs-2 text-center"></div>
                <div class="col-xs-4 text-center"></div>
                <div class="col-xs-2 text-center"><label class="control-label lead">Total TVA</label></div>
                <?php $ids_total_tva = array(); ?>
                <?php foreach($form['lignes'] as $f_ligne): $ids_total_tva[] = "#".$f_ligne['montant_tva']->renderId(); endforeach; ?>
                <div class="col-xs-2 text-center"><strong><input id="total_tva" type="text" class="form-control input-lg text-right data-sum-element"  data-sum="<?php echo implode(" + ", $ids_total_tva) ?>" data-sum-element="#total_ttc" readonly="readonly" value="<?php echo $facture->total_taxe ?>" /></strong></div>
            </div>
            <div class="form-group form-group-lg">
                <div class="col-xs-2 text-center"></div>
                <div class="col-xs-4 text-center"></div>
                <div class="col-xs-2 text-center"><label class="control-label lead">Total TTC</label></div>
                <div class="col-xs-2 text-center"><strong><input id="total_ttc" type="text" class="form-control input-lg text-right" data-sum="#total_ht + #total_tva" readonly="readonly" value="<?php echo $facture->total_ttc ?>" /></strong></div>
            </div>
        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-6 text-left">
            <a class="btn btn-danger btn-lg btn-upper" href="<?php echo url_for('facturation_declarant', $facture->getCompte()) ?>">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-default btn-lg btn-upper">Valider</a>
        </div>
    </div>

</form>
