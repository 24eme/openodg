<?php include_partial('drevmarc/step', array('step' => 'revendication', 'drevmarc' => $drevmarc)) ?>

<form role="form" action="<?php echo url_for("drevmarc_revendication", $drevmarc) ?>" method="post">
    <div class="frame">	
        <?php echo $form->renderHiddenFields() ?>
        <?php echo $form->renderGlobalErrors() ?>

        <p></p>
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <label class="col-xs-4 control-label" for="">Période de distillation :</label>
                    <div class="col-xs-4">
                        <?php echo $form['debut_distillation']->renderLabel(); ?>
                        <div class="input-group date-picker">
                            <?php echo $form['debut_distillation']->render(); ?>
                        </div>
                    </div>
                    <div class="col-xs-4">
                        <?php echo $form['fin_distillation']->renderLabel(); ?>
                        <div class="input-group date-picker">
                            <?php echo $form['fin_distillation']->render(); ?>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form['qte_marc']->renderLabel(null,array('class' => 'col-xs-4 control-label')); ?>
                    <div class="col-xs-8">
                         <?php echo $form['qte_marc']->render(); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form['volume_obtenu']->renderLabel(null,array('class' => 'col-xs-4 control-label')); ?>
                    <div class="col-xs-8">
                         <?php echo $form['volume_obtenu']->render(); ?>
                    </div>
                </div>
                <div class="form-group">
                    
                    <?php echo $form['titre_alcool_vol']->renderLabel(null,array('class' => 'col-xs-4 control-label')); ?>
                    <div class="col-xs-8">
                         <?php echo $form['titre_alcool_vol']->render(); ?>
                    </div>
                </div>
            </div>
            <br/>

            <div class="row row-margin">
                <div class="col-xs-4"><a href="<?php echo url_for("drevmarc_exploitation", $drevmarc) ?>" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-left pull-left"></span>Étape précédente</a></div>
                <div class="col-xs-4 col-xs-offset-4"><button type="submit" class="btn btn-primary btn-lg btn-block"><span class="eleganticon arrow_carrot-right pull-right"></span>Étape suivante</button></div>
            </div>
        </div>
</form>