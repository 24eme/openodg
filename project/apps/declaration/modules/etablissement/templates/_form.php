<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>
<div class="row">
    <div id="row_form_exploitation" class="row col-xs-offset-1 col-xs-10 <?php if(!$form->isBound()): ?>hidden<?php endif; ?>">
        <div class="col-xs-5">
            <div class="form-group">
                <strong class="col-xs-3 text-right">N°&nbsp;CVI</strong>
                <span class="col-xs-9">
                   <?php echo $etablissement->cvi; ?>
                </span>
            </div>
            <div class="form-group">
                <?php echo $form["siret"]->renderError(); ?>
                <?php echo $form["siret"]->renderLabel("N°&nbsp;SIRET", array("class" => "col-xs-3 control-label")); ?>
                <div class="col-xs-9">
                    <?php echo $form["siret"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
        </div>
        <div class="col-xs-7">
            <div class="form-group">
                <?php echo $form["raison_sociale"]->renderError(); ?>
                <?php echo $form["raison_sociale"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["raison_sociale"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form["adresse"]->renderError(); ?>
                <?php echo $form["adresse"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["adresse"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form["commune"]->renderError(); ?>
                <?php echo $form["commune"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["commune"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form["code_postal"]->renderError(); ?>
                <?php echo $form["code_postal"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["code_postal"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form["telephone_bureau"]->renderError(); ?>
                <?php echo $form["telephone_bureau"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["telephone_bureau"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form["telephone_mobile"]->renderError(); ?>
                <?php echo $form["telephone_mobile"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["telephone_mobile"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form["telephone_prive"]->renderError(); ?>
                <?php echo $form["telephone_prive"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <?php echo $form["telephone_prive"]->render(array("class" => "form-control")); ?>
                </div>
            </div>
            <div class="form-group">
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
        </div>
    </div>
    <div id="row_info_exploitation" class="row col-xs-offset-1 col-xs-10 <?php if($form->isBound()): ?>hidden<?php endif; ?>">
        <div class="col-xs-5">
            <div class="form-group">
                <strong class="col-xs-3 text-right">N°&nbsp;CVI</strong>
                <span class="col-xs-9">
                   <?php echo $etablissement->cvi; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-3 text-right">N°&nbsp;SIRET</strong>
                <span class="col-xs-9">
                   <?php echo $etablissement->siret; ?>
                </span>
            </div>
        </div>
        <div class="col-xs-7">
            <div class="form-group">
                <strong class="col-xs-4 text-right">Raison sociale</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->raison_sociale; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Adresse</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->adresse; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Commune</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->commune; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Code Postal</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->code_postal; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Tél. Fixe</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->telephone_bureau; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Tél. Mobile</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->telephone_mobile; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Tél. Privé</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->telephone_prive; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Fax</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->fax; ?>
                </span>
            </div>
            <div class="form-group">
                <strong class="col-xs-4 text-right">Email</strong>
                <span class="col-xs-8">
                   <?php echo $etablissement->email; ?>
                </span>
            </div>
        </div>
    </div>
</div>