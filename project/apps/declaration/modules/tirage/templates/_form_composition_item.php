<div class="row compositionBouteilles" style="margin-bottom: 10px;">
	<div class="col-xs-2 col-xs-offset-3 <?php if ($form["nombre"]->hasError()): ?>has-error<?php endif; ?>">
		<?php if ($form["nombre"]->hasError()): ?>                            
			<div class="alert alert-danger" role="alert"><?php echo $form["nombre"]->getError(); ?></div>
        <?php endif; ?> 
        <?php echo $form['nombre']->render() ?>
	</div>
	<div class="col-xs-3 text-left" style="padding-top: 7px;">bouteille(s) de</div>
	<div class="col-xs-3 <?php if ($form["contenance"]->hasError()): ?>has-error<?php endif; ?>">
		<?php if ($form["contenance"]->hasError()): ?>                            
			<div class="alert alert-danger" role="alert"><?php echo $form["contenance"]->getError(); ?></div>
        <?php endif; ?> 
		<?php echo $form['contenance']->render(array('class' => 'form-control')) ?>
	</div>
	<div class="col-xs-1" style="padding-top: 5px;">
		<a href="javascript:void(0)" data-container="div.compositionBouteilles" role="button" class="text-danger btn_rm_ligne_template" style="font-size: 20px;"><span class="glyphicon glyphicon-remove-sign"></span></a>
	</div>
</div>
