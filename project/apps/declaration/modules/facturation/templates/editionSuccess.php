<?php include_partial('admin/menu', array('active' => 'facturation')); ?>

<form action="" method="post" class="form-horizontal">

    <?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
    
    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <div class="col-xs-1"><strong>Quantité</strong></div>
                <div class="col-xs-5"><strong>Libellé</strong></div>
                <div class="col-xs-1"><strong>Prix unitaire</strong></div>
                <div class="col-xs-2"><strong>Montant HT</strong></div>
                <div class="col-xs-1"><strong>Taux TVA</strong></div>
                <div class="col-xs-2"><strong>Montant TVA</strong></div>
            </div>
        </div>
        <div class="col-xs-12">
            <?php foreach($form['lignes'] as $f_ligne): ?>
            <div class="form-group">
                <div class="col-xs-5 col-xs-offset-1">
                <?php echo $f_ligne['libelle']->renderError() ?>
                <?php echo $f_ligne['libelle']->render(array('class' => 'form-control input-lg')); ?>
                </div>
                <div class="col-xs-2 col-xs-offset-1">
                <?php echo $f_ligne['montant_ht']->renderError() ?>
                <?php echo $f_ligne['montant_ht']->render(array('class' => 'form-control input-lg text-right')); ?>
                </div>
                <div class="col-xs-2 col-xs-offset-1">
                <?php echo $f_ligne['montant_tva']->renderError() ?>
                <?php echo $f_ligne['montant_tva']->render(array('class' => 'form-control input-lg text-right')); ?>
                </div>
            </div>
                <div class="form-group">
                    <div class="col-xs-12">
                    <?php foreach($f_ligne['details'] as $f_detail): ?>
                        <div class="form-group">
                            <div class="col-xs-1">
                                <?php echo $f_detail['quantite']->renderError() ?>
                                <?php echo $f_detail['quantite']->render(array('class' => 'form-control text-right')); ?>
                            </div>
                            <div class="col-xs-5">
                                <?php echo $f_detail['libelle']->renderError() ?>
                                <?php echo $f_detail['libelle']->render(array('class' => 'form-control')); ?>
                            </div>
                            <div class="col-xs-1">
                                <?php echo $f_detail['prix_unitaire']->renderError() ?>
                                <?php echo $f_detail['prix_unitaire']->render(array('class' => 'form-control text-right')); ?>
                            </div>
                            <div class="col-xs-2">
                                <?php echo $f_detail['montant_ht']->renderError() ?>
                                <?php echo $f_detail['montant_ht']->render(array('class' => 'form-control text-right')); ?>
                            </div>
                            <div class="col-xs-1">
                                <?php echo $f_detail['taux_tva']->renderError() ?>
                                <?php echo $f_detail['taux_tva']->render(array('class' => 'form-control text-right')); ?>
                            </div>
                            <div class="col-xs-2">
                                <?php echo $f_detail['montant_tva']->renderError() ?>
                                <?php echo $f_detail['montant_tva']->render(array('class' => 'form-control text-right')); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
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
