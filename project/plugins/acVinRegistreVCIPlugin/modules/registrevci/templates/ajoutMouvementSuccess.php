<?php use_helper('Date'); use_helper('Float');
 include_partial('registrevci/breadcrumb', array('registre' => $registre )); ?>
<form action ="<?php echo url_for("registrevci_ajout_mouvement", array('id' => $registre->_id)) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row" style="display:flex; flex-direction:column; align-items:center;">
        <div class="col-xs-11">
            <h3 style="margin-bottom:25px; text-align:center;">Ajouter un mouvement </h3>
            <div class="form-group-block col-sm-11" style="display:flex; flex-direction:column; align-items:center;">
                <div class="form-group col-sm-9">
                    <?php echo $form['produit']->renderLabel("Produit", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-7">
                        <?php echo $form['produit']->render(array("required" => true,
                        "class" => "form-control select2 select2-offscreen select2autocomplete",
                        "placeholder" => "Sélectionner produit")); ?>
                    </div>
                </div>

                <div class="form-group col-sm-9">
                    <?php echo $form['lieu']->renderLabel("Choisir un lieu", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-7">
                        <?php echo $form['lieu']->render(array("class" => "form-control input-xs select2 select2-offscreen select2autocompleteremote",
                        "placeholder" => "Cave particulière",
                        "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                        ));?>
                    </div>
                </div>

                <div class="form-group col-sm-9">
                    <?php echo $form['mouvement_type']->renderLabel("Type de mouvement", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-7">
                        <?php echo $form['mouvement_type']->render(array("required" => true,
                        "class" => "form-control select2 select2-offscreen select2autocomplete",
                        "placeholder" => "Sélectionner un type de mouvement")); ?>
                    </div>
                </div>

                <div class="form-group col-sm-9">
                    <?php echo $form['volume']->renderLabel("Volume", array('class' => "col-sm-4 control-label")); ?>
                    <div class="col-sm-7">
                        <div class="input-group">
                            <?php echo $form['volume']->render(array("required" => true, "placeholder" => "Saisir un volume", 'data-allow-negative' => true)); ?>
                            <div class="input-group-addon">hl</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row row-margin row-button col-sm-12">
                <div class="col-sm-6 text-center">
                    <a href="<?php echo url_for("registrevci_visualisation", array('id' => $registre->_id)) ?>" class="btn btn-primary"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retour</a>
                </div>
                <div class="col-sm-6 text-center">
                    <button type="submit" class="btn btn-success btn ">Valider</button>
                </div>
            </div>

        </div>
    </div>
    
</form>