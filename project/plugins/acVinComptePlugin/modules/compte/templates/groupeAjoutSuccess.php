<?php use_helper('Compte'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li>Groupes</li>
    <li class="active"><a href="<?php echo url_for('compte_groupe', array("groupeName" => $groupeName)); ?>"><?php echo str_replace('_',' ',$groupeName); ?></a></li>
</ol>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default">
          <div class="panel-heading">
              <div class="row">
                  <div class="col-xs-12 ">
                      <h4>Ajout d'un compte au groupe « <?php echo str_replace('_',' ',$groupeName); ?> »</h4>
                  </div>
              </div>
            </div>
            <div class="panel-body">
              <div class="list-group" id="list-item">
                <h3>Sélection d'un opérateur</h3>
                <form method="post" class="form-horizontal" action="<?php echo url_for('compte_groupe_ajout',array('groupeName' => $groupeName)); ?>">
                    <?php echo $form->renderHiddenFields() ?>
                    <?php echo $form->renderGlobalErrors() ?>
                    <div class="col-xs-7">
                      <div class="form-group <?php if($form['id_etablissement']->hasError()): ?> has-error<?php endif; ?>">
                          <?php echo $form['id_etablissement']->renderError(); ?>
                          <?php echo $form['id_etablissement']->render(array('class' => 'form-control select2autocompleteAjax input-md', 'placeholder' => 'Rechercher', "autofocus" => "autofocus")); ?>
                      </div>
                    </div>
                    <div class="col-xs-2">
                      <div class="form-group <?php if($form['fonction']->hasError()): ?> has-error<?php endif; ?>">
                          <?php echo $form["fonction"]->renderError(); ?>
                              <?php echo $form["fonction"]->render(array("class" => "form-control select2 input-md",
                                  "placeholder" => "Fonction"));
                              ?>
                      </div>
                    </div>
                    <div class="col-xs-2">
                    <button class="btn btn-default btn-md" type="submit" id="btn_rechercher">Ajouter</button>
                    </div>
                </form>


            </div>
        </div>
  </div>
</div>
