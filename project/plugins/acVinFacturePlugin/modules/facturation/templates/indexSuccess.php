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

        <?php include_partial('facturation/generationForm', array('form' => $formFacturationMassive, 'massive' => true)); ?>
    </div>
</div>
<br/>

<h3>Historique des générations</h3>
<?php include_partial('generation/list', array('generations' => $generations)); ?>
