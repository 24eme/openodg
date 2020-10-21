<?php use_helper('Float') ?>
<?php use_helper('Date') ?>
<?php include_partial('chgtdenom/breadcrumb', array('chgtDenom' => $chgtDenom )); ?>
<?php include_partial('chgtdenom/step', array('step' => 'edition', 'chgtDenom' => $chgtDenom)) ?>


    <div class="page-header no-border">
      <h2>Changement de dénomination / Déclassement</h2>
      <h3><small></small></h3>
    </div>

    <div class="alert alert-info" role="alert">
      <h4>Modification du lot n° <strong><?php echo $lot->numero; ?></strong></h4>
      <table class="table table-condensed" style="margin: 0;">
        <tbody>
          <tr>
            <td style="border: none;">Date : <strong><?php echo format_date($lot->date, 'dd/MM/yyyy'); ?></strong></td>
          </tr>
          <tr>
            <td style="border: none;">Produit : <strong><?php echo $lot->produit_libelle; ?></strong>&nbsp;<small class="text-muted"><?php echo $lot->details; ?></small></td>
          </tr>
          <tr>
            <td style="border: none;">Volume : <strong><?php echo echoFloat($lot->volume); ?></strong>&nbsp;<small class="text-muted">hl</small></td>
          </tr>
        </tbody>
      </table>
    </div>

    <form role="form" action="<?php echo url_for("chgtdenom_edition", array("sf_subject" => $chgtDenom, 'key' => $key)) ?>" method="post" class="form-horizontal">

        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>

        <div class="row">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_type']->renderLabel("Type de modification", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-8 bloc_condition" data-condition-cible="#bloc_changement_produit">
                            <span class="error text-danger"><?php echo $form['changement_type']->renderError() ?></span>
                            <?php echo $form['changement_type']->render(); ?>
                      </div>
                  </div>
              </div>
        </div>

        <div class="row" id="bloc_changement_produit" data-condition-value="CHGT">
          <div class="col-md-8">
              <div class="form-group">
                  <?php echo $form['changement_produit']->renderLabel("Nouveau produit", array('class' => "col-sm-4 control-label")); ?>
                  <div class="col-sm-8">
                      <span class="error text-danger"><?php echo $form['changement_produit']->renderError() ?></span>
                      <?php echo $form['changement_produit']->render(array("data-placeholder" => "Sélectionnez un nouveau produit", "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                  </div>
              </div>
          </div>
        </div>

        <div class="row">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_quantite']->renderLabel("Quantité modifiée", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-8 bloc_condition" data-condition-cible="#bloc_changement_volume">
                            <span class="error text-danger"><?php echo $form['changement_quantite']->renderError() ?></span>
                            <?php echo $form['changement_quantite']->render(); ?>
                      </div>
                  </div>
              </div>
        </div>

        <div class="row" id="bloc_changement_volume" data-condition-value="PART">
              <div class="col-md-8">
                  <div class="form-group">
                      <?php echo $form['changement_produit']->renderLabel("Nouveau volume", array('class' => "col-sm-4 control-label")); ?>
                      <div class="col-sm-5">
                          <span class="error text-danger"><?php echo $form['changement_volume']->renderError() ?></span>
                          <div class="input-group">
                              <?php echo $form['changement_volume']->render(array("placeholder" => "Précisez un volume")); ?>
                              <div class="input-group-addon">hl</div>
                          </div>
                      </div>
                  </div>
              </div>
        </div>

        <div style="margin-top: 20px;" class="row row-margin row-button">
            <div class="col-xs-6">
                <a tabindex="-1" href="<?php echo url_for('chgtdenom_lots', $chgtDenom) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a>
            </div>
            <div class="col-xs-6 text-right">
                <button type="submit" class="btn btn-primary btn-upper">Valider <span class="glyphicon glyphicon-chevron-right"></span></button>
            </div>
        </div>
    </form>
