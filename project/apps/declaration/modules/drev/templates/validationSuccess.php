<?php include_partial('drev/step', array('step' => 'validation', 'drev' => $drev)) ?>

<?php 
	if($validation->hasPoints()) {
		include_partial('drev/pointsAttentions', array('drev' => $drev, 'validation' => $validation)); 
	}
?>

<?php include_partial('drev/recap', array('drev' => $drev)); ?>

<?php include_partial('drev/engagements', array('drev' => $drev)); ?>

<p class="clearfix">
    <a href="<?php echo url_for("drev_controle_externe", $drev) ?>" class="btn btn-primary btn-lg pull-left">Étape précedente</a>
    <button type="button" href="" class="btn btn-success btn-lg pull-right">Valider</button>
</p>