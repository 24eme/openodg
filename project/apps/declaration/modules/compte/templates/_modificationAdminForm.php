<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>
<div id="row_form_compte_modification" class="row col-xs-offset-1 col-xs-10">
    <div class="row">
        <div class="col-xs-6 panel panel-primary">
            <div class="panel-heading">
                <h3>Identité</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <?php echo $form["civilite"]->renderError(); ?>
                    <?php echo $form["civilite"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["civilite"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form["prenom"]->renderError(); ?>
                    <?php echo $form["prenom"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["prenom"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form["nom"]->renderError(); ?>
                    <?php echo $form["nom"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["nom"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form["raison_sociale"]->renderError(); ?>
                    <?php echo $form["raison_sociale"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                    <div class="col-xs-8">
                        <?php echo $form["raison_sociale"]->render(array("class" => "form-control")); ?>
                    </div>
                </div>
                <?php if (isset($form['cvi'])): ?>
                    <div class="form-group">
                        <?php echo $form["cvi"]->renderError(); ?>
                        <?php echo $form["cvi"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["cvi"]->render(array("class" => "form-control")); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($form['code_insee'])): ?>
                    <div class="form-group">
                        <?php echo $form["code_insee"]->renderError(); ?>
                        <?php echo $form["code_insee"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["code_insee"]->render(array("class" => "form-control")); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($form['siren'])): ?>
                    <div class="form-group">
                        <?php echo $form["siren"]->renderError(); ?>
                        <?php echo $form["siren"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["siren"]->render(array("class" => "form-control")); ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (isset($form["siret"])): ?>
                    <div class="form-group">
                        <?php echo $form["siret"]->renderError(); ?>
                        <?php echo $form["siret"]->renderLabel("N°&nbsp;SIRET/SIREN", array("class" => "col-xs-4 control-label")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["siret"]->render(array("class" => "form-control")); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-xs-6 panel panel-primary">
            <div class="panel-heading">
                <h3>Coordonnées</h3>
            </div>
            <div class="panel-body">
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
    </div>
    <div class="row">
        <div class="col-xs-12 panel panel-primary">
            <div class="panel-heading">
                <h3>Informations complémentaire</h3>
            </div>
            <div class="panel-body">
                <div class="form-group">
                    <?php echo $form["attributs"]->renderError(); ?>
                    <?php echo $form["attributs"]->renderLabel("Attributs", array("class" => "col-xs-3 control-label")); ?>
                    <div class="col-xs-9">
                        <?php echo $form["attributs"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                    </div>
                </div>
                <?php if (isset($form["produits"])): ?>
                    <div class="form-group">
                        <?php echo $form["produits"]->renderError(); ?>
                        <?php echo $form["produits"]->renderLabel("Produits", array("class" => "col-xs-3 control-label")); ?>
                        <div class="col-xs-9">
                            <?php echo $form["produits"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>