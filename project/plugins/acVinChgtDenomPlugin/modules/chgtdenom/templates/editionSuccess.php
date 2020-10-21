<?php use_helper('Float') ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>
<?php include_partial('chgtdenom/step', array('step' => 'edition', 'chgtDenom' => $chgtDenom)) ?>
<div class="page-header">
    <h2>Changement de dénomination / Déclassement</h2>

    <form role="form" action="<?php echo url_for("chgtdenom_edition", array("sf_subject" => $chgtDenom, 'key' => $key)) ?>" method="post" class="form-horizontal">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <div class="row">
              <div class="col-md-6">
                  <div class="form-group">
                      <span class="error text-danger"><?php echo $form['changement_type']->renderError() ?></span>
                      <?php echo $form['changement_type']->renderLabel("Type de modification", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-8">
                            <?php echo $form['changement_type']->render(); ?>
                      </div>
                  </div>
              </div>
        </div>

        <div class="row">
              <div class="col-md-6">
                  <div class="form-group">
                      <span class="error text-danger"><?php echo $form['changement_produit']->renderError() ?></span>
                      <?php echo $form['changement_produit']->renderLabel("Nouveau produit", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-8">
                            <?php echo $form['changement_produit']->render(array("data-placeholder" => "Sélectionnez un nouveau produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                      </div>
                  </div>
              </div>
        </div>

        <div class="row">
              <div class="col-md-6">
                  <div class="form-group">
                      <span class="error text-danger"><?php echo $form['changement_quantite']->renderError() ?></span>
                      <?php echo $form['changement_quantite']->renderLabel("Quantité modifiée", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-8">
                            <?php echo $form['changement_quantite']->render(); ?>
                      </div>
                  </div>
              </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <span class="error text-danger"><?php echo $form['changement_volume']->renderError() ?></span>
                    <?php echo $form['changement_volume']->renderLabel("Nouveau volume", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-8">
                        <div class="input-group">
                            <?php echo $form['changement_volume']->render(); ?>
                            <div class="input-group-addon">hl</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px;" class="row row-margin row-button">
            <div class="col-xs-4">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_lots', $chgtDenom) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
            </div>
            <div class="col-xs-4 text-right">
                <button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </form>
</div>
