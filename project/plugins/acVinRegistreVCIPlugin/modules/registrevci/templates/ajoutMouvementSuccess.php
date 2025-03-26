<?php use_helper('Date'); use_helper('Float');
 include_partial('registrevci/breadcrumb', array('registre' => $registre )); ?>
<form action ="<?php echo url_for("registrevci_ajout_mouvement", array('id' => $registre->_id)) ?>" method="post" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row" style="display:flex; flex-direction:column; align-items:center;">
        <div class="col-xs-10">
            <h3>Ajout d'un mouvement </h3>
             <div class="form-group">
                <?php echo $form['produit']->renderLabel("Produit", array('class' => "col-sm-3 control-label")); ?>
                <div class="col-sm-7">
                      <?php echo $form['produit']->render(array("required" => true, "class" => "form-control")); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form['mouvement_type']->renderLabel("Type de mouvement", array('class' => "col-sm-3 control-label")); ?>
                <div class="col-sm-7">
                      <?php echo $form['mouvement_type']->render(array("required" => true, "class" => "form-control")); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form['volume']->renderLabel("Volume", array('class' => "col-sm-3 control-label")); ?>
                <div class="col-sm-7">
                      <?php echo $form['volume']->render(array("required" => true, "class" => "form-control input num_float text-left")); ?>
                </div>
            </div>

            <div class="col text-center">
                <button type="submit" class="btn btn-success btn">Valider</button>
            </div>

        </div>
    </div>
    
</form>