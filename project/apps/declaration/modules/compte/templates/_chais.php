<div class="chai" id="chai<?php echo $indice ?>">
	<div class="ligne_form">
		<?php echo $form['adresse']->renderError() ?>
		<?php echo $form['adresse']->renderLabel() ?>
		<?php echo $form['adresse']->render() ?>
	</div>
	<div class="ligne_form">
		<?php echo $form['commune']->renderError() ?>
		<?php echo $form['commune']->renderLabel() ?>
		<?php echo $form['commune']->render() ?>
	</div>
	<div class="ligne_form">
		<?php echo $form['code_postal']->renderError() ?>
		<?php echo $form['code_postal']->renderLabel() ?>
		<?php echo $form['code_postal']->render() ?>
	</div>
	<a href="#" class="supprimer">Supprimer</a>
</div>