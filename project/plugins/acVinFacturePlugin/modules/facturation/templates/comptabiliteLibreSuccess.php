<ol class="breadcrumb">
  <li class="visited"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
  <li class="visited"><a href="<?php echo url_for('facturation_libre'); ?>">Facturation libre</a></li>
  <li class="active"><a href="<?php echo url_for('facturation_libre_comptabilite'); ?>">Comptabilité</a></li>
</ol>

<div class="col-xs-12">
    <h3>Données comptables</h3>


    <form id="form_comptabilite_edition" action="" method="post" class="form-horizontal">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>



        <?php if ($form->hasErrors()): ?>
            <div class="alert alert-danger" role="alert">
                Veuillez compléter ou corriger les erreurs
            </div>
        <?php endif; ?>

        <div class="row row-margin">
            <div class="col-xs-12" style="border-bottom: 1px dotted #d2d2d2;" >
                <div class="row">
                    <div class="col-xs-2 text-center">Compte</div>
                    <div class="col-xs-2 text-center">Code analytique</div>
                    <div class="col-xs-1 text-center">Taux TVA</div>
                    <div class="col-xs-7">Libellé compta</div>
                </div>

            </div>
        </div>
        <?php $tabIndex = 1; ?>
        <?php foreach ($form->getObject()->getOrAdd('identifiants_analytiques') as $iakey => $identifiant_analytique) : ?>
            <?php $autofocus = ($iakey == 'nouvelle')? array('autofocus' => 'autofocus') : array(); ?>
                <div class="row row-margin"  style="border-bottom: 1px dotted #d2d2d2; padding: 5px;">
                    <div class="col-xs-2">
                        <div class="row">
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_numero_compte_' . $iakey]->renderError(); ?>  </div>
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_numero_compte_' . $iakey]->render(array_merge(array('class' => 'form-control input-sm text-right','tabindex' => $tabIndex),$autofocus)); ?>  </div>
                        </div>
                    </div>
                    <?php $tabIndex++; ?>
                    <div class="col-xs-2">
                        <div class="row">
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_' . $iakey]->renderError(); ?>  </div>
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_' . $iakey]->render(array('class' => 'form-control input-sm text-right','tabindex' => $tabIndex)); ?>  </div>
                        </div>
                    </div>
                    <?php $tabIndex++; ?>
                    <div class="col-xs-1">
                        <div class="row">
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_taux_tva_' . $iakey]->renderError(); ?>  </div>
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_taux_tva_' . $iakey]->render(array('class' => 'form-control input-sm text-right','tabindex' => $tabIndex)); ?>  </div>
                        </div>
                    </div>
                      <?php $tabIndex++; ?>
                    <div class="col-xs-7">
                        <div class="row">
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_libelle_compta_' . $iakey]->renderError(); ?>  </div>
                            <div class="col-xs-12"><?php echo $form['identifiant_analytique_libelle_compta_' . $iakey]->render(array('class' => 'form-control input-sm','tabindex' => $tabIndex)); ?>  </div>
                        </div>
                    </div>
                      <?php $tabIndex++; ?>
                </div>
        <?php endforeach; ?>
        <br/>


        <div class="row row-margin">
            <div class="col-xs-6 text-left">
                <a class="btn btn-default btn-lg btn-upper" href="<?php echo url_for('facturation_libre') ?>">Retour</a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-success btn-lg btn-upper" tabindex="<?php echo $tabIndex; ?>">Valider</button>
            </div>
        </div>

    </form>
</div>
