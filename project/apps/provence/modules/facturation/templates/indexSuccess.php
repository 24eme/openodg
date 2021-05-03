<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-12">
        <form method="post" action="" role="form" class="form-horizontal">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="form-group">
                <?php echo $form["login"]->renderError(); ?>
                <div class="col-xs-12">
                  <?php if ($sf_user->isAdmin() && class_exists("EtablissementChoiceForm")): ?>
                          <?php include_partial('etablissement/formChoice', array('form' => $formSociete, 'action' => url_for('facturation'), 'noautofocus' => true)); ?>
                  <?php endif; ?>
                </div>
            </div>

        </form>
    </div>
</div>
<div class="row row-margin">
    <div class="col-xs-12">
      <form method="post" action="<?php echo url_for("facturation_massive"); ?>" role="form" class="form-horizontal">
          <?php echo $formFacturationMassive->renderHiddenFields(); ?>
          <?php echo $formFacturationMassive->renderGlobalErrors(); ?>
          <h3>Génération massive de factures</h3>
          <div class="form-group <?php if($formFacturationMassive["date_facturation"]->hasError()): ?>has-error<?php endif; ?>">
          <?php echo $formFacturationMassive["date_facturation"]->renderError(); ?>
          <?php echo $formFacturationMassive["date_facturation"]->renderLabel("Date de facturation", array("class" => "col-xs-2 control-label")); ?>
          <div class="col-xs-8">
                  <div class="input-group date-picker-week">
                      <?php echo $formFacturationMassive["date_facturation"]->render(array("class" => "form-control", "placeholder" => "Date de facturation")); ?>
                      <div class="input-group-addon">
                          <span class="glyphicon-calendar glyphicon"></span>
                      </div>
                  </div>
          </div>
              <div class="col-xs-2">
                  <div class="form-group">
                      <button class="btn btn-default btn-upper" type="submit">Générer</button>
                  </div>
              </div>
            </div>
      </form>
    </div>
</div>
<?php include_partial('generation/list', array('generations' => $generations)); ?>
