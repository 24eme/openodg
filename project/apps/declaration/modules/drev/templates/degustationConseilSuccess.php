<?php include_partial('drev/step', array('step' => 'degustation_conseil', 'drev' => $drev)) ?>

<a href="<?php echo url_for("drev_revendication", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
<a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg pull-right">Étape suivante</a>