<?php ?>

<div class="page-header no-border">
    <h2>Vin</h2>
</div>

<form role="form" action="<?php echo url_for('tirage_lots', $tirage) ?>" method="post" id="validation-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>



    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="form-group">
                <?php echo $form["couleur"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $form["couleur"]->renderLabel(); ?>
                    <?php echo $form["couleur"]->render(array("class" => "")); ?>
                </label>
            </div>

        </div>
    </div>


    <div class="row row-margin">
        <div class="col-xs-12">
 <div class="form-group">
                <?php echo $form["cepage"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $form["cepage"]->renderLabel(); ?>
                    <?php echo $form["cepage"]->render(array("class" => "")); ?>
                </label>
            </div>
        </div>
    </div>


    <div class="row row-margin">
        <div class="col-xs-12">
<div class="form-group">
                <?php echo $form["millesime"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $form["millesime"]->renderLabel(); ?>
                    <?php echo $form["millesime"]->render(array("class" => "")); ?>
                </label>
            </div>
 
        </div>
    </div>


    <div class="row row-margin">
        <div class="col-xs-12">
<div class="form-group">
                <?php echo $form["volume_ventile"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $form["volume_ventile"]->renderLabel(); ?>
                    <?php echo $form["volume_ventile"]->render(array("class" => "")); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
<div class="form-group">
                <?php echo $form["fermentation_lactique"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $form["fermentation_lactique"]->renderLabel(); ?>
                    <?php echo $form["fermentation_lactique"]->render(array("class" => "")); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right">
            <button type="button" id="btn-validation-document" data-toggle="modal" data-target="#drev-confirmation-validation" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider la déclaration</button>
        </div>
    </div>
</form>