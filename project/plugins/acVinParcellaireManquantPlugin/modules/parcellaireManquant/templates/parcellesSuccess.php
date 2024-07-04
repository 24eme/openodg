<?php include_partial('parcellaireManquant/breadcrumb', array('parcellaireManquant' => $parcellaireManquant)); ?>
<?php include_partial('parcellaireManquant/step', array('step' => 'parcelles', 'parcellaireManquant' => $parcellaireManquant)) ?>
<div>
    <h2>Parcelles de votre exploitation</h2>
    <p class="pt-3">Merci d'indiquer vos parcelles ayant des pieds manquants ou morts en cliquant sur la ligne de la parcelle concernée.</p>
    <div class="alert alert-info">
        <p style="margin:10px 0;">
            <span class="glyphicon glyphicon-info-sign"></span> Il n'est pas nécessaire d'indiquer les parcelles avec moins de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants.
            <a style="margin-top:-5px;" href="<?php echo url_for('parcellairemanquant_validation', $parcellaireManquant) ?>" class="btn btn-sm btn-default pull-right">
                <span class="glyphicon glyphicon-check"></span>
                Je n'ai pas de parcelle avec plus de <?php echo ParcellaireConfiguration::getInstance()->getManquantPCMin(); ?>% de pieds manquants
            </a>
        </p>
    </div>
</div>

<form action="<?php echo url_for("parcellairemanquant_parcelles", $parcellaireManquant) ?>" method="post" class="form-horizontal">
    <?php include_partial('parcellaireManquant/formParcelles', ['parcellaireManquant' => $parcellaireManquant]); ?>
	<div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellairemanquant_exploitation", $parcellaireManquant); ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retourner à l'étape précédente</a></div>
        <div class="col-xs-4 text-center">
            <button type="submit" name="saveandquit" value="1" class="btn btn-default">Enregistrer en brouillon</button>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer <span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
