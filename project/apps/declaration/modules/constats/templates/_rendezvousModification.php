<form id="form_operateur_rendezvous" action="<?php echo url_for('rendezvous_modification', $rendezvous); ?>" method="post" class="form-horizontal" name="<?php echo $form->getName(); ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>


    <div class="row">
        <div class="col-xs-5">
            <div class="col-xs-12">CHAI <?php echo $rendezvous->idchai ?> <small>(modification)</small></div>
            <div class="col-xs-12"><?php echo $chai->adresse ?></div>
            <div class="col-xs-6 text-left"><?php echo $chai->commune ?></div>
            <div class="col-xs-6 text-left"><?php echo $chai->code_postal ?></div>
        </div>

        <div class="col-xs-7">
            <div class="row">
                <div class="col-xs-7">
                    <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form['date']->renderError(); ?>
                        <div class="input-group date-picker" >
                            <?php echo $form['date']->render(array('class' => 'form-control')); ?>
                            <div class="input-group-addon">
                                <span class="glyphicon-calendar glyphicon"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xs-5">
                    <div class="form-group <?php if ($form["heure"]->hasError()): ?>has-error<?php endif; ?>">
                        <?php echo $form["heure"]->renderError(); ?>

                        <div class="input-group date-picker-time">
                            <?php echo $form["heure"]->render(array("class" => "form-control")); ?>
                            <div class="input-group-addon">
                                <span class="glyphicon glyphicon-time"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group">
                        <?php echo $form["commentaire"]->render(array("class" => "form-control")); ?>

                    </div>
                </div>
            </div>
            <div class="row row-margin row-button">    
                <div class="col-xs-3 text-right">
                    <button type="submit" class="btn btn-default btn-lg btn-upper">Ajouter</button>
                </div>
            </div>
        </div>
    </div>

</form>