<?php include_partial('drev/breadcrumb', array('drev' => $drev )); ?>

<?php include_partial('drev/step', array('step' => 'dr_douane', 'drev' => $drev)) ?>
<div class="page-header">
    <h2>Récupération des données de la Déclaration de Récolte</h2>
</div>
<form method="post" enctype="multipart/form-data">
<?php if ($form): ?>
	<?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
	<p>Les données de votre Déclaration de Récolte ne sont pas disponibles sur le site de Prodouane. Merci de bien vouloir nous fournir le fichier XLS de votre DR afin de poursuivre la saisie de vos revendications.</p>
    <div class="row" style="margin: 20px 0;">
    	<div class="form-group <?php if($form['file']->hasError()): ?>has-error<?php endif; ?>">
			<div class="col-xs-11 col-xs-offset-1">
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
	<p>Les données de votre Déclaration de Récolte sont disponibles sur le site de Prodouane. Celles ci seront récupérées automatiquement afin de pré-remplir la saisie de vos revendications.</p>
<?php endif; ?>
<div class="row row-margin row-button">
    <div class="col-xs-6"><a href="<?php echo url_for("drev_exploitation", $drev) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
    <div class="col-xs-6 text-right">
        <?php if ($drev->exist('etape') && $drev->etape == DrevEtapes::ETAPE_VALIDATION): ?>
            <button id="btn-validation" type="submit" class="btn btn-primary btn-upper">Retourner à la validation <span class="glyphicon glyphicon-check"></span></button>
        <?php else: ?>
            <button type="submit" class="btn btn-primary btn-upper">Continuer vers la revendication <span class="glyphicon glyphicon-chevron-right"></span></button>
        <?php endif; ?>
    </div>
</div>
</form>
