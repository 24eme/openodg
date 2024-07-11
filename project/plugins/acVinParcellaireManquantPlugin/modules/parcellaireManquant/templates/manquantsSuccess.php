<?php include_partial('parcellaireManquant/breadcrumb', array('parcellaireManquant' => $parcellaireManquant)); ?>

<?php include_partial('parcellaireManquant/step', array('step' => 'manquants', 'parcellaireManquant' => $parcellaireManquant)) ?>
<div>
    <h2>Pieds morts ou manquants sur votre exploitation</h2>
    <p class="pt-3">Merci d'indiquer la densité et le % de pied manquant</p>
    <div class="alert alert-info">
        <div style="display: inline-block; margin-right: 1rem;">
            <p><span class="glyphicon glyphicon-info-sign"></span></p>
        </div>
        <div style="display: inline-block; vertical-align: middle">
            Il n'est pas nécessaire d'indiquer les parcelles avec moins de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants.<br/>Si vous n'avez aucune parcelle concernée, vous pouvez aller directement à la <a href="<?php echo url_for('parcellairemanquant_validation', $parcellaireManquant) ?>">validation</a>.
        </div>
    </div>
</div>

<form action="<?php echo url_for("parcellairemanquant_manquants", $parcellaireManquant) ?>" method="post" class="form-inline">
	<?php include_partial('parcellaireManquant/formManquants', ['parcellaireManquant' => $parcellaireManquant, 'form' => $form]); ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellairemanquant_parcelles", $parcellaireManquant); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
