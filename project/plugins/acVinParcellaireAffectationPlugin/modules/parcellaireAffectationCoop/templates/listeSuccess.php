<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border">
    <h2>Saisie des affectations parcellaires par apporteur</h2>
</div>

<div class="row">
    <div class="form-group col-xs-12">
      <input id="hamzastyle" type="hidden" data-placeholder="Rechercher dans la liste par nom, cvi ou statut" data-hamzastyle-container=".table_affectations" data-hamzastyle-mininput="0" class="select2autocomplete hamzastyle form-control">
    </div>
</div>

<form action="" method="post" class="form-horizontal">
    <table class="table table-condensed table-striped table-bordered table_affectations">
        <tr>
            <th class="col-xs-1">CVI</th>
            <th>Nom</th>
            <th class="col-xs-2 text-center">Statut</th>
            <th class="col-xs-2"></th>
        </tr>
    <?php foreach ($parcellaireAffectationCoop->getApporteursChoisis() as $apporteur): ?>
        <tr class="hamzastyle-item <?php if($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_NON_IDENTIFIEE): ?>text-muted<?php endif; ?>" data-words='<?php echo json_encode(array($apporteur->getStatutLibelle(), $apporteur->nom, $apporteur->cvi), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>' >
            <td><?php echo $apporteur->cvi; ?></td>
            <td><?php echo $apporteur->nom; ?></td>
            <td style="<?php if($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_VALIDE): ?>background-color: rgba(169, 197, 50, 0.4) ;<?php endif; ?>" class="text-center <?php if($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_VALIDE): ?>bg-success text-success<?php elseif($apporteur->intention) :?>text-primary<?php endif; ?>">
                <?php if($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_VALIDE): ?><span class="glyphicon glyphicon-ok-sign"></span><?php endif; ?> <?php echo $apporteur->getStatutLibelle(); ?>
            </td>
            <td class="text-center">
                <?php if($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_VALIDE): ?>
                    <a class="text-success" href="<?php echo url_for('parcellaireaffectationcoop_visualisation', array('sf_subject' => $parcellaireAffectationCoop, 'id_document' => $apporteur->getAffectationParcellaire()->_id)) ?>">Voir la déclaration</a>
                <?php elseif($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_EN_COURS): ?>
                    <a href="<?php echo url_for('parcellaireaffectationcoop_saisie', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => $apporteur->getEtablissementIdentifiant())) ?>">Continuer la déclaration</a>
                <?php elseif($apporteur->getStatut() == ParcellaireAffectationCoopApporteur::STATUT_A_SAISIR): ?>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for('parcellaireaffectationcoop_saisie', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => $apporteur->getEtablissementIdentifiant())) ?>">Saisir la déclaration</a>
                    -<br/>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for('parcellaireaffectationcoop_switch', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => $apporteur->getEtablissementIdentifiant(), "sens" => "0")) ?>">Pas cette année</a>
                <?php else: ?>
                    <span class="glyphicon glyphicon-ban-circle transparence-md"></span>
                    <br/>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for('parcellaireaffectationcoop_switch', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => $apporteur->getEtablissementIdentifiant(), "sens" => "1")) ?>">Ré-Activer</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>

    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_apporteurs", $parcellaireAffectationCoop) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaireaffectationcoop_exportcsv", $parcellaireAffectationCoop) ?>" class="btn btn-primary">Export CSV des affectations validées</a>
        </div>
        <div class="col-xs-4 text-right">
        </div>
    </div>
</form>

<?php use_javascript('hamza_style.js'); ?>
