
<h3><?php if($massive): ?>Génération massive <small> (<a href="<?php echo url_for('facturation_en_attente'); ?>">mvt en attente</a>)</small><?php else: ?>Génération de facture<?php endif; ?></h3>
<form method="post" action="" role="form" class="form-horizontal">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <div class="row">
        <div class="col-sm-8 col-xs-12">
          <?php if(isset($form["modele"])): ?>
            <div class="form-group <?php if($form["modele"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["modele"]->renderError() ?>
                <?php echo $form["modele"]->renderLabel("Type de génération", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                <?php echo $form["modele"]->render(array("class" => "control-label")); ?>
                </div>
            </div>
          <?php endif; ?>
            <?php if(isset($form["date_facturation"])): ?>
            <div class="form-group <?php if($form["date_facturation"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_facturation"]->renderError(); ?>
                <?php echo $form["date_facturation"]->renderLabel("Date de facturation", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="input-group date-picker-week">
                        <?php echo $form["date_facturation"]->render(array("class" => "form-control", "placeholder" => "Date de facturation")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($form["date_mouvement"])): ?>
            <div class="form-group <?php if($form["date_mouvement"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["date_mouvement"]->renderError(); ?>
                <?php echo $form["date_mouvement"]->renderLabel("Date de mouvements", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="input-group date-picker-week">
                        <?php echo $form["date_mouvement"]->render(array("class" => "form-control", "placeholder" => "Date de prise en compte des mouvements")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(false && isset($form["message_communication"])): ?>
            <div class="form-group <?php if($form["message_communication"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["message_communication"]->renderError(); ?>
                <?php echo $form["message_communication"]->renderLabel("Message de communication", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="">
                        <?php echo $form["message_communication"]->render(array("class" => "form-control", "placeholder" => "Message")); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if(isset($form["type_document"])): ?>
            <div class="form-group <?php if($form["type_document"]->hasError()): ?>has-error<?php endif; ?>">
                <?php echo $form["type_document"]->renderError(); ?>
                <?php echo $form["type_document"]->renderLabel("Type de document", array("class" => "col-xs-4 control-label")); ?>
                <div class="col-xs-8">
                    <div class="">
                        <?php echo $form["type_document"]->render(array("class" => "form-control", "placeholder" => "Message")); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="form-group text-right">
                <div class="col-xs-6 col-xs-offset-6">
                    <button class="btn btn-default btn-block btn-upper" type="submit"><?php if($massive): ?>Générer<?php else: ?>Générer la facture<?php endif; ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
