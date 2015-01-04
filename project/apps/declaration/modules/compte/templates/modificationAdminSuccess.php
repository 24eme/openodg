<div class="page-header">
    <h2>Création d'un nouveau compte</h2>
</div>

<form action="<?php echo url_for("compte_modification_admin", array('id' => $compte->identifiant)) ?>" method="post" class="form-horizontal">

    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div id="row_form_compte_modification" class="row col-xs-offset-1 col-xs-10">
            <div class="col-xs-6">
                <div class="form-group<?php if ($form["civilite"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["civilite"]->renderError(); ?>
                    <?php echo $form["civilite"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["civilite"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                  <div class="form-group<?php if ($form["nom"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["nom"]->renderError(); ?>
                    <?php echo $form["nom"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["nom"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["prenom"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["prenom"]->renderError(); ?>
                    <?php echo $form["prenom"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["prenom"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["adresse"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["adresse"]->renderError(); ?>
                    <?php echo $form["adresse"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["adresse"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["ville"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["ville"]->renderError(); ?>
                    <?php echo $form["ville"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["ville"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["code_postal"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["code_postal"]->renderError(); ?>
                    <?php echo $form["code_postal"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["code_postal"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>               
            </div>
            <div class="col-xs-6">              
                <div class="form-group<?php if ($form["telephone_bureau"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["telephone_bureau"]->renderError(); ?>
                    <?php echo $form["telephone_bureau"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["telephone_bureau"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["telephone_mobile"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["telephone_mobile"]->renderError(); ?>
                    <?php echo $form["telephone_mobile"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["telephone_mobile"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["telephone_prive"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["telephone_prive"]->renderError(); ?>
                    <?php echo $form["telephone_prive"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["telephone_prive"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group<?php if ($form["fax"]->hasError()): ?> has-error<?php endif; ?>">
                    <?php echo $form["fax"]->renderError(); ?>
                    <?php echo $form["fax"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["fax"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form["email"]->renderError(); ?>
                    <?php echo $form["email"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["email"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                 <div class="form-group">
                    <?php echo $form["siret"]->renderError(); ?>
                    <?php echo $form["siret"]->renderLabel("N°&nbsp;SIRET/SIREN", array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["siret"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                
            </div>
                 <div class="form-group">
                    <?php echo $form["attributs"]->renderError(); ?>
                    <?php echo $form["attributs"]->renderLabel("Attributs", array("class" => "col-xs-2 control-label")); ?>
                    <div class="col-xs-9">
                        <?php echo $form["attributs"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                    </div>
                </div>
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("home") ?>" class="btn btn-primary btn-lg btn-upper"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à mon espace</small></a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" class="btn btn-warning">Valider</button>
        </div>
    </div>
</form>