<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'apporteurs', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Liste complète de tous vos adhérents</h2>
</div>

<?php if($sf_user->isAdmin()): ?><a class="pull-right" href="<?php echo url_for("parcellaireaffectationcoop_recap", $parcellaireAffectationCoop) ?>">Voir les changements</a><?php endif; ?>
<p>Vous pouvez mettre à jour la liste compléte de tous vos adhérents. <br /><br />
Ceux qui ne vous ont rien apportés cette année mais qui reste adhérent doivent resté cochés.</p>

<form action="" method="post" class="form-horizontal">
    <table class="table table-condensed table-striped table-bordered">
        <tr>
            <th style="width: 0;"></th>
            <th class="text-right col-xs-1">Provenance</th>
            <th class="col-xs-1">CVI</th>
            <th>Nom</th>
        </tr>


    <?php echo $form->renderHiddenFields(); ?>
    <?php echo $form->renderGlobalErrors(); ?>
    <?php foreach ($parcellaireAffectationCoop->apporteurs as $idApporteur => $apporteur): ?>
        <tr class="vertical-center cursor-pointer">
            <td>
                <div style="margin-bottom: 0;" class="form-group">
                    <div class="col-xs-12">
                        <?php echo $form[$idApporteur]->render(array('class' => "bsswitch", 'data-size' => 'small', 'data-on-text' => "Adhérent", 'data-off-text' => "Démissionnaire", 'data-on-color' => "success", 'data-off-color' => "danger")); ?>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <?php echo $parcellaireAffectationCoop->apporteurs->get($idApporteur)->provenance; ?>
            </td>
            <td class="">
                <?php echo $parcellaireAffectationCoop->apporteurs->get($idApporteur)->cvi; ?>
            </td>
            <td class="">
                <?php echo $parcellaireAffectationCoop->apporteurs->get($idApporteur)->nom; ?>
            </td>

        </tr>
    <?php endforeach; ?>

    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("declaration_etablissement", array('identifiant' => $etablissement->identifiant)); ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
        </div>
        <div class="col-xs-4 text-right"><button type="submit" class="btn btn-primary btn-upper">Valider et continuer<span class="glyphicon glyphicon-chevron-right"></span></button></div>
    </div>
</form>
