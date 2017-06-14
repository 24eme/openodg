<?php use_helper('Date') ?>

<div class="page-header">
    <h2>Ajout de fichier</h2>
</div>

<form class="form-horizontal" role="form" action="<?php echo url_for("upload_fichier", array('fichier_id' => $fichier_id, 'sf_subject' => $etablissement)) ?>" method="post" enctype="multipart/form-data">
    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>

    <div class="row">
    	<div class="form-group <?php if($form['file']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['file']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['file']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<?php echo $form['file']->render() ?>
			</div>
			<?php if (!$fichier->isNew() && $fichier->hasAttachments()): ?>
			<div class="col-xs-2">
				<a href="<?php echo $fichier->generateUrlPiece() ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Voir le fichier</a>
			</div>
			<?php endif; ?>
		</div>
    </div>

    <div class="row">
    	<div class="form-group <?php if($form['libelle']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['libelle']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['libelle']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<?php echo $form['libelle']->render(array('class' => 'form-control input', 'placeholder' => "Libellé du document")) ?>
			</div>
		</div>
    </div>

    <div class="row">
    	<div class="form-group <?php if($form['date_depot']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['date_depot']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['date_depot']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<div class="input-group date-picker">
					<?php echo $form['date_depot']->render(array('class' => 'form-control', 'placeholder' => "Date du dépôt")); ?>
					<div class="input-group-addon">
						<span class="glyphicon-calendar glyphicon"></span>
					</div>
				</div>
			</div>
		</div>
    </div>

    <div class="row">
    	<div class="form-group <?php if($form['visibilite']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-8 col-xs-offset-4">
				<?php echo $form['visibilite']->renderError() ?>
			</div>
			<div class="col-xs-3 col-xs-offset-1">
				<?php echo $form['visibilite']->renderLabel() ?>
			</div>
			<div class="col-xs-6">
				<?php echo $form['visibilite']->render() ?>
			</div>
		</div>
    </div>

    <div class="row row-margin row-button">
        <div class="col-xs-6">
        	<a href="<?php echo url_for('declaration_etablissement', $etablissement) ?>" class="annuler btn btn-default btn-danger">Annuler</a>
        </div>
        <div class="col-xs-6 text-right">
        	<button type="submit" class="btn btn-default btn-lg btn-upper">Ajouter</button>
        </div>
    </div>
</form>
