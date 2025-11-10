<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'apporteurs', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Liste complète de tous vos adhérents</h2>
</div>

<?php if($sf_user->isAdmin()): ?><a class="pull-right" href="<?php echo url_for("parcellaireaffectationcoop_recap", $parcellaireAffectationCoop) ?>">Voir les changements</a><?php endif; ?>
<p>Vous pouvez mettre à jour la liste compléte de tous vos adhérents. <br /><br />
Ceux qui ne vous ont rien apportés cette année mais qui reste adhérent doivent resté cochés.</p>

<a class="btn btn-secondary" href="<?php echo url_for("parcellaireaffectationcoop_ajout_apporteurs", $parcellaireAffectationCoop); ?>">Ajouter un apporteur</a>

<form action="" method="post" class="form-horizontal">
    <table class="table table-condensed table-striped table-bordered table-apporteursCoop">
        <tr>
            <th class="text-right col-xs-2">Statut</th>
            <th class="text-right col-xs-1">Provenance</th>
            <th class="col-xs-1">CVI</th>
            <th>Nom</th>
        </tr>


    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php foreach ($parcellaireAffectationCoop->apporteurs as $idApporteur => $apporteur): ?>
        <tr class="vertical-center cursor-pointer rows">
            <td class="apporteurStatut" style="display: flex; justify-content: space-between;">
                <div style="margin-bottom: 0;" class="form-group">
                    <div class="col-xs-12">
                        <label class="switch-xl">
                            <?php echo $form[$idApporteur]->render(array('class' => "switch")); ?>
                        <span class="slider-xl round"></span>
                        </label>
                    </div>
                </div>
                <div class="texteStatut"></div>
            </td>
            <td class="text-center">
                <?php echo $parcellaireAffectationCoop->apporteurs->get($idApporteur)->provenance; ?>
            </td>
            <td class="">
                <?php echo $parcellaireAffectationCoop->apporteurs->get($idApporteur)->cvi; ?>
            </td>
            <td class="">
                <?php echo $parcellaireAffectationCoop->apporteurs->get($idApporteur)->nom; ?> <span class="text-muted"><?php echo $idApporteur; ?></span>
            </td>

        </tr>
    <?php endforeach; ?>

    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaireaffectationcoop_exportapporteurcsv", $parcellaireAffectationCoop) ?>" class="btn btn-default">Export CSV des apporteurs</a>
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer<span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
