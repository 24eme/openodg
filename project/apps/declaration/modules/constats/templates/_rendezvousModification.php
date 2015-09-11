<?php
$url = ($creation) ? url_for('rendezvous_creation', array('id' => 'COMPTE-' . $rendezvous->identifiant, 'idchai' => $rendezvous->idchai)) : url_for('rendezvous_modification', array('id' => $rendezvous->_id, 'retour' => $retour));
?>
<form id="form_operateur_rendezvous_<?php echo $rendezvous->idchai; ?>" action="<?php echo $url; ?>" method="post" class="form-horizontal form_operateur_rendezvous" name="<?php echo $form->getName(); ?>">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">
        <div class="col-xs-4">
            <div class="col-xs-12"><h4>CHAI n°<?php echo $rendezvous->idchai + 1 ?> <small>(<a class="text-muted-alt" href="">modifier</a>)</small></h4></div>
            <div class="col-xs-12"><?php echo $chai->adresse ?></div>
            <div class="col-xs-12 text-left"><?php echo $chai->code_postal ?> <?php echo $chai->commune ?></div>
        </div>

        <div class="col-xs-8">
            <div class="col-xs-6" >
                <div class="form-group <?php if ($form["date"]->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $form['date']->renderError(); ?>
                    <div class="input-group date-picker " >
                        <?php echo $form['date']->render(array('class' => 'form-control', 'placeholder' => "Date du rendez-vous",)); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon-calendar glyphicon"></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xs-5 col-xs-offset-1">
                <div class="form-group <?php if ($form["heure"]->hasError()): ?>has-error<?php endif; ?>">
                    <?php echo $form["heure"]->renderError(); ?>

                    <div class="input-group date-picker-time">
                        <?php echo $form["heure"]->render(array("class" => "form-control", "placeholder" => "Heure souhaité")); ?>
                        <div class="input-group-addon">
                            <span class="glyphicon glyphicon-time"></span>
                        </div>
                    </div>
                </div>
            </div>
            <!--<div class="col-xs-12">
                
            </div>-->
            <div class="row">
                <div class="col-xs-8">
                    <?php echo $form["commentaire"]->render(array("class" => "form-control", "placeholder" => "Information éventuelle pour le rendez-vous")); ?>
                </div>
                <div class="col-xs-4 text-right">
                    <button type="submit" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;<?php echo ($creation)? 'Ajouter' : 'Modifier'?> le RDV</button>
                </div>
            </div>
        </div>
    </div>

</form>