<form action ="<?php echo url_for("registrevci_ajout_mouvement", array('id' => $registre->_id)) ?>" method="post" class="form-horizontal">
    <div class="row">
        <div class="col-xs-10">
            <h3>Produits</h3>
            <div class="form-group">
                <?php echo $form['produit']->renderLabel("Produit", array('class' => "col-sm-3 control-label")); ?>
                <div class="col-sm-7">
                      <?php echo $form['produit']->render(array("placeholder" => "Selectionner un produit", "required" => true, "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                </div>
            </div>

                <div class="form-group">
                    <?php echo $form['date']->renderLabel("Date du mouvement", array('class' => "col-sm-3 control-label")); ?>
                    <div class="col-sm-7">
                        <div class="input-group date-picker-week">
                            <?php echo $form['date']->render(array("class" => "form-control", "required" => true)); ?>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <?php echo $form['lieu']->renderLabel("Lieu de stockage", array('class' => "col-sm-3 control-label")); ?>
                    <div class="col-sm-7">
                          <?php echo $form['lieu']->render(array("placeholder" => "Selectionner un lieu", "required" => true, "class" => "form-control select2 select2-offscreen select2autocomplete")); ?>
                    </div>
                </div>
        </div>
    </div>
    
</form>