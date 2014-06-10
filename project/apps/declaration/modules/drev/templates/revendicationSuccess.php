<?php include_partial('drev/step', array('step' => 'revendication', 'drev' => $drev)) ?>

<form role="form" action="<?php echo url_for("drev_revendication", $drev) ?>" method="post">
	<?php echo $form->renderHiddenFields() ?>
    <?php echo $form->renderGlobalErrors() ?>
	<div class="row">
		<div class="col-md-8">
			<?php include_partial('drev/revendicationForm', array('drev' => $drev, 'form' => $form)); ?>
		</div>
		<div class="col-md-4"></div>
	</div>
	<a href="" class="btn btn-primary btn-lg pull-left disabled">Étape précedente</a>
	<button type="submit" class="btn btn-primary btn-lg pull-right">Étape suivante</button>
</form>