<?php include_partial('tirage/step', array('step' => 'vin', 'tirage' => $tirage)) ?>
<div class="page-header no-border">
    <h2>Vin <small>Merci de saisir les informations liées au Crémant dont vous souhaitez déclarer le tirage</small></h2>
</div>

<form role="form" action="" method="post" id="tirage-vin-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row row-margin">
        <div class="col-xs-12"> 
            <div class="form-inline">
                <?php echo $form["couleur"]->renderError(); ?>
                <label class="col-xs-3 text-right">
                    <?php echo $form["couleur"]->renderLabel(); ?>
                </label>
                <div class="col-xs-9 text-left">
                    <?php echo $form["couleur"]->render(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="form-inline">
                <?php echo $form["cepages_actifs"]->renderError(); ?>
                <label class="col-xs-3 text-right">
                    <?php echo $form["cepages_actifs"]->renderLabel(); ?>
                </label>
                <div class="col-xs-9 text-left">              
                    <?php echo $form["cepages_actifs"]->render(array("class" => "")); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="form-inline bloc_condition" data-condition-cible="#bloc_millesime_ventilation">
                <?php echo $form["millesime"]->renderError(); ?>
                <label class="col-xs-3 text-right">
                    <?php echo $form["millesime"]->renderLabel(); ?>
                </label>
                <div class="col-xs-9 text-left">              
                    <?php echo $form["millesime"]->render(array("class" => "")); ?>
                </div>
            </div>

        </div>
    </div>


    <div class="row row-margin">
        <div class="col-xs-12" id="bloc_millesime_ventilation" data-condition-value="ASSEMBLE">
            <div>
                <?php echo $form["millesime_ventilation"]->renderError(); ?>
                <label class="col-xs-3 text-right">
                    <?php echo $form["millesime_ventilation"]->renderLabel(); ?>
                </label>
                <div class="col-xs-6 text-left">       
                    <?php echo $form["millesime_ventilation"]->render(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="form-group">
                <?php echo $form["fermentation_lactique"]->renderError(); ?>
                <label class="col-xs-3 text-right">
                    <?php echo $form["fermentation_lactique"]->renderLabel(); ?>
                </label>
                <div class="col-xs-9 text-left">       
                    <?php echo $form["fermentation_lactique"]->render(array("class" => "bsswitch")); ?>
                </div>
            </div>
        </div>
    </div>
   
    <div class="row row-margin">
        <div class="col-xs-4"><a href="<?php echo url_for("tirage_exploitation", $tirage) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'axploitation</small></a></div>
        
        <div class="col-xs-4"></div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer vers les lots</button>
        </div>
    </div>
</form>