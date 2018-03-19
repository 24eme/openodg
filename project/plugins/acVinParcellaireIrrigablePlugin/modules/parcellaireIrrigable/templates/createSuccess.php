<?php use_helper('Float'); ?>

<?php include_partial('parcellaireIrrigable/breadcrumb', array('parcellaire' => null )); ?>

<?php include_partial('parcellaireIrrigable/step', array('step' => 'exploitation', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Exploitation <small>Données administratives, merci de les modifier en cas de changement</small></h2>
</div>


<form action="#" method="post" class="form-horizontal">

    <div class="row">
        <div class="col-xs-12">
                
        </div>
    </div>
    <div class="row row-margin row-button">
        <div class="col-xs-6">
                <a href="#" class="btn btn-primary btn-lg btn-upper btn-primary-step"><span class="eleganticon arrow_carrot-left"></span>&nbsp;&nbsp;Précédent</a>
        </div>
        <div class="col-xs-6 text-right">
              <button type="submit" class="btn btn-default btn-lg btn-upper btn-default-step">Continuer&nbsp;&nbsp;<span class="eleganticon arrow_carrot-right"></span></button>
        </div>
    </div>
</form>
