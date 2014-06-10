<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<a href="" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
<a href="<?php echo url_for("drev_degustation_conseil", $drev) ?>" class="btn btn-primary btn-lg pull-right">Étape suivante</a>