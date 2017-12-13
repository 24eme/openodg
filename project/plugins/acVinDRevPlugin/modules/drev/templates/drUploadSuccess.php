<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'dr_douane', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération de la <?php echo strtolower($drev->getDocumentDouanierTypeLibelle()) ?> <?php if(!$drev->hasDocumentDouanier()): ?><a href="<?php echo url_for('drev_dr', $drev) ?>" class="pull-right btn btn-warning btn-xs">Récupérer sur Prodouane, si disponible</a><?php endif; ?></h2>
</div>
<form method="post" enctype="multipart/form-data">
<?php if ($form): ?>
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
	<p>Les données de votre <?php echo $drev->getDocumentDouanierTypeLibelle() ?> ne sont pas disponibles sur le site de Prodouane. Merci de bien vouloir nous fournir le fichier XLS de votre <?php echo $drev->getDocumentDouanierTypeLibelle() ?> afin de poursuivre la saisie de vos revendications.</p>
    <div class="row" style="margin: 20px 0;">
    	<div class="form-group <?php if($form['file']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-11 col-xs-offset-1 text-danger">
				<?php echo $form['file']->renderError() ?>
			</div>
			<div class="col-xs-1">
				<?php echo $form['file']->renderLabel() ?>
			</div>
			<div class="col-xs-11">
				<?php echo $form['file']->render() ?>
			</div>
		</div>
    </div>
<?php else: ?>
	<p>Les données de votre <?php echo $drev->getDocumentDouanierTypeLibelle() ?> sont disponibles sur le site de Prodouane. Celles ci seront récupérées automatiquement afin de pré-remplir la saisie de vos revendications.</p>
<?php endif; ?>
<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
    <div class="col-xs-6 text-right">
        <button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button>
    </div>
</div>
</form>
