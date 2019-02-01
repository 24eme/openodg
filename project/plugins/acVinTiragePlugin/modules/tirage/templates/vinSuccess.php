<?php include_partial('tirage/breadcrumb', array('tirage' => $tirage )); ?>
<?php include_partial('tirage/step', array('step' => 'vin', 'tirage' => $tirage)) ?>
<div class="page-header no-border">
    <h2>Caractéristiques du lot <small>Saisissez ici les informations liées au vin dont vous souhaitez déclarer le tirage</small></h2>
</div>

<form role="form" action="" method="post" id="tirage-vin-form" class="ajaxForm" >
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
                    <div data-toggle="buttons" data-selection-mode="auto" class="btn-group select">
                    <?php echo $form["cepages_actifs"]->render(); ?>
                    </div>
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
        <div class="col-xs-12 form-group" id="bloc_millesime_ventilation" data-condition-value="ASSEMBLE">
            <div>
                <?php echo $form["millesime_ventilation"]->renderError(); ?>
                <label class="col-xs-3 text-right">
                    <?php echo $form["millesime_ventilation"]->renderLabel(null,array('class' => 'control-label')); ?>
                </label>
                <div class="col-xs-8 text-left">
                    <?php
                    $lastYear = date('Y') - 1; $last3year = date('Y') - 3;
                    $placeHolder = 'Inscrivez ici le volume par millésime (par exemple : 15hl de '. $lastYear .', 13hl de '.  $last3year .')';?>
                    <?php echo $form["millesime_ventilation"]->render(array('placeholder' =>  $placeHolder)); ?>
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
                    <?php echo $form["fermentation_lactique"]->render(); ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row row-margin">
        <div class="col-xs-4"><a href="<?php echo url_for("tirage_exploitation", $tirage) ?>" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Retourner <small>à l'exploitation</small></a></div>

        <div class="col-xs-4"></div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper">Continuer vers les lots</button>
        </div>
    </div>
</form>
