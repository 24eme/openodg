<?php echo $form->renderHiddenFields(); ?>
<?php echo $form->renderGlobalErrors(); ?>
<div id="row_form_compte_modification" class="row  col-xs-12">
    <div class="row">
        <div class="col-xs-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3>Identité</h3>
                </div>
                <div class="panel-body">
                    <?php
                    if (isset($form["civilite"]) && isset($form["prenom"]) && isset($form["nom"])):
                        ?>
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
                    <?php endif ?>
                    <?php if (isset($form['raison_sociale'])): ?>
                        <div class="form-group">
                            <?php echo $form["raison_sociale"]->renderError(); ?>
                            <?php echo $form["raison_sociale"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                            <div class="col-xs-8">
                                <?php echo $form["raison_sociale"]->render(array("class" => "form-control")); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($form['cvi'])): ?>
                        <div class="form-group">
                            <?php echo $form["cvi"]->renderError(); ?>
                            <?php echo $form["cvi"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                            <div class="col-xs-8">
                                <?php echo $form["cvi"]->render(array("class" => "form-control")); ?>
                            </div>
                        </div>
                    <?php elseif($form->getObject()->cvi): ?>
                        <div class="form-group">
                            <label class="col-xs-4 control-label">CVI</label>
                            <div class="col-xs-8">
                                <input disabled="disabled" value="<?php echo $form->getObject()->cvi; ?>" class="form-control" />
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
                    <?php if (isset($form["siret"])): ?>
                        <div class="form-group">
                            <?php echo $form["siret"]->renderError(); ?>
                            <?php echo $form["siret"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                            <div class="col-xs-8">
                                <?php echo $form["siret"]->render(array("class" => "form-control")); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-xs-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <h3>Coordonnées</h3>
                </div>
                <div class="panel-body">
                    <div class="form-group">
                        <label class="col-xs-4 control-label">Adresse</label>
                    </div>
                    <div class="form-group">
                        <?php echo $form["adresse_complement_destinataire"]->renderError(); ?>
                        <?php echo $form["adresse_complement_destinataire"]->renderLabel("Mention complémentaire", array("class" => "col-xs-4 control-label",  "style" => "font-weight: normal;")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["adresse_complement_destinataire"]->render(array("placeholder" => "Service, Batiment, etc.", "class" => "form-control",  "style" => "opacity: 0.7")); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form["adresse"]->renderError(); ?>
                        <?php echo $form["adresse"]->renderLabel("N° et nom de rue", array("class" => "col-xs-4 control-label")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["adresse"]->render(array("placeholder" => "N° et nom de rue", "class" => "form-control")); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form["adresse_complement_lieu"]->renderError(); ?>
                        <?php echo $form["adresse_complement_lieu"]->renderLabel("Complément<br />de lieu", array("class" => "col-xs-4 control-label", "style" => "font-weight: normal;")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["adresse_complement_lieu"]->render(array("placeholder" => "Boite postale, Lieu dit, etc.", "class" => "form-control", "style" => "opacity: 0.7")); ?>
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
                        <?php echo $form["commune"]->renderError(); ?>
                        <?php echo $form["commune"]->renderLabel(null, array("class" => "col-xs-4 control-label")); ?>
                        <div class="col-xs-8">
                            <?php echo $form["commune"]->render(array("class" => "form-control")); ?>
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
    </div>
    <div class="row">
        <div class="col-xs-12" >
            <div class=" panel panel-primary">
                <div class="panel-heading">
                    <h3>Informations complémentaire</h3>
                </div>
                <div class="panel-body">
                    <?php if (isset($form["attributs"])): ?>
                    <div class="form-group">
                        <?php echo $form["attributs"]->renderError(); ?>
                        <?php echo $form["attributs"]->renderLabel("Attributs", array("class" => "col-xs-3 control-label")); ?>
                        <div class="col-xs-9">
                            <?php echo $form["attributs"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Ajouter des attributs")); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (isset($form["produits"])): ?>
                        <div class="form-group">
                            <?php echo $form["produits"]->renderError(); ?>
                            <?php echo $form["produits"]->renderLabel("Produits", array("class" => "col-xs-3 control-label")); ?>
                            <div class="col-xs-9">
                                <?php echo $form["produits"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Ajouter des produits")); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($form["syndicats"])): ?>
                        <div class="form-group">
                            <?php echo $form["syndicats"]->renderError(); ?>
                            <?php echo $form["syndicats"]->renderLabel("Syndicats", array("class" => "col-xs-3 control-label")); ?>
                            <div class="col-xs-9">
                                <?php echo $form["syndicats"]->render(array("class" => "form-control select2 select2-offscreen select2autocomplete", "placeholder" => "Ajouter des syndicats")); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <?php echo $form["manuels"]->renderError(); ?>
                        <?php echo $form["manuels"]->renderLabel("Mots clés", array("class" => "col-xs-3 control-label")); ?>
                        <div class="col-xs-9">                        
                            <?php
                            echo $form["manuels"]->render(array("class" => "form-control select2 select2-offscreen select2autocompletepermissif",
                                "placeholder" => "Ajouter des mots clés (liste permissive)",
                                "data-url" => url_for('compte_tags_manuels'),
                                "data-initvalue" => $form->getObject()->getDefaultManuelsTagsFormatted()));
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (isset($form['chais'])): ?>
        <div class="row">
            <div class="col-xs-12" >        

                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3>Chais</h3>
                    </div>
                    <div class="panel-body">
                        <h4>Veuillez ajouter vos chais</h4>
                        <div id="formsChais">
                            <div class="row">
                                <div class="col-xs-2 text-center" ></div> 
                                <div class="col-xs-4 text-center" ><label class="control-label">Adresse</label></div> 
                                <div class="col-xs-3 text-center" ><label class="control-label">Commune</label></div> 
                                <div class="col-xs-3 text-center" ><label class="control-label">Code postal</label></div> 
                            </div>
                            <br/>
                            <?php foreach ($form['chais'] as $key => $chaiform): ?>
                                <?php include_partial('form_chai_item', array('partial' => 'form_chai_item', 'form' => $chaiform, 'indice' => $key, 'existChai' => true)); ?>
                            <?php endforeach; ?>
                            <?php include_partial('form_collection_template', array('partial' => 'form_chai_item', 'form' => $form['chais'][count($form['chais'])-1], 'indice' => count($form['chais']))); ?>

                        </div>
                        <div class="col-xs-12 text-right">
                        <a class="btn btn-plus btn_ajouter_chai_template"
                           data-container="#formsChais" data-template="#template_form_chai_item" href="#">+ Ajouter un chai</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-xs-12">
            <div class="form-group">
                <?php echo $form["commentaires"]->renderError(); ?>
                <?php echo $form["commentaires"]->renderLabel(null, array("class" => "col-xs-12")); ?>
                <div class="col-xs-12">
                    <?php echo $form["commentaires"]->render(array("class" => "form-control", "rows" => 3)); ?>
                </div>
            </div>
        </div>
    </div>
</div>