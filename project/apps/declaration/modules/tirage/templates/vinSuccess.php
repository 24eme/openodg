<?php use_javascript("tirage.js", "last") ?>
<?php include_partial('tirage/step', array('step' => 'vin', 'tirage' => $tirage)) ?>
<div class="page-header no-border">
    <h2>Vin</h2>
</div>

<form role="form" action="<?php echo url_for('tirage_lots', $tirage) ?>" method="post" id="tirage-vin-form">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row row-margin">
        <div class="col-xs-12"> 
            <div class="form-inline">
                <?php echo $form["couleur"]->renderError(); ?>
                <label class="col-xs-6 text-right">
                    <?php echo $form["couleur"]->renderLabel(); ?>
                </label>
                <?php echo $form["couleur"]->render(); ?>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
          <div class="form-inline">
                <?php echo $form["cepage"]->renderError(); ?>
                <label class="col-xs-6 text-right">
                    <?php echo $form["cepage"]->renderLabel(); ?>
                </label>
              <div class="checkbox">                  
                    <?php echo $form["cepage"]->render(array("class" => "")); ?>
              </div>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
           <div class="form-inline">
                <?php echo $form["millesime"]->renderError(); ?>
               <label class="col-xs-6 text-right">
                    <?php echo $form["millesime"]->renderLabel(); ?>
               </label>
                    <?php echo $form["millesime"]->render(array("class" => "")); ?>
               
            </div>

        </div>
    </div>


    <div class="row row-margin">
        <div class="col-xs-12">
           <div class="form-inline">
                <?php echo $form["volume_ventile"]->renderError(); ?>
               <label class="col-xs-6 text-right">
                    <?php echo $form["volume_ventile"]->renderLabel(); ?>
               </label>
                    <?php echo $form["volume_ventile"]->render(array("class" => "")); ?>
              
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-12">
            <div class="form-group">
                <?php echo $form["fermentation_lactique"]->renderError(); ?>
                <label class="col-xs-12">
                    <?php echo $form["fermentation_lactique"]->renderLabel(); ?>
                    <?php echo $form["fermentation_lactique"]->render(array("class" => "bsswitch")); ?>
                </label>
            </div>
        </div>
    </div>
    <div class="row row-margin">
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right">
            <button type="submit" class="btn btn-default btn-lg btn-upper"><span class="glyphicon glyphicon-check"></span>&nbsp;&nbsp;Valider</button>
        </div>
    </div>
</form>