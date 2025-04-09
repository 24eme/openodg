<?php include_partial('parcellaireAffectationCoop/breadcrumb', array('parcellaireAffectationCoop' => $parcellaireAffectationCoop)); ?>
<?php include_partial('parcellaireAffectationCoop/step', array('step' => 'saisies', 'parcellaireAffectationCoop' => $parcellaireAffectationCoop)) ?>

<div class="page-header no-border mt-04" style="display:flex; align-items: center; justify-content:space-between;">
    <h2 style="margin:0;">Déclarations des apporteurs - Campagne <?php echo str_replace('-', '/',$parcellaireAffectationCoop->getCampagne()) ?> </h2>

    <h3 class="pull-right" style="margin:0; font-size:20px;">
    Vous avez <?php echo count($parcellaireAffectationCoop->getApporteursChoisis()) ?> coopérateurs
    </h3>
</div>


<div class="row">
    <div class="form-group col-xs-12">
      <input id="hamzastyle" type="hidden" data-placeholder="Rechercher dans la liste par nom, cvi ou statut" data-hamzastyle-container=".table_affectations" data-hamzastyle-mininput="0" class="hamzastyle form-control">
    </div>
</div>

<form action="" method="post" class="form-horizontal">
    <table class="table table-condensed table-striped table-bordered table_affectations">
        <tr>
            <th class="col-xs-1">CVI</th>
            <th>Nom</th>
            <th class="col-xs-2 text-center">Affectation</th>
            <th class="col-xs-2 text-center">Manquant</th>
            <th class="col-xs-2 text-center">Irrigable</th>
        </tr>
    <?php $nb = 0; foreach ($parcellaireAffectationCoop->getApporteursChoisis() as $apporteur_id => $apporteur): if (isset($partial) && $partial && $nb++ > 10) {continue;}?>
        <tr class="hamzastyle-item <?php if($apporteur->getDeclarationStatut("ParcellaireAffectation") == ParcellaireAffectationCoopApporteur::STATUT_NON_IDENTIFIEE): ?>text-muted<?php endif; ?>" data-words='<?php echo json_encode(array($apporteur->nom, $apporteur->cvi), JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>' >
            <td><?php echo $apporteur->cvi; ?></td>
            <td><span class="text-primary"><?php echo $apporteur->nom; ?></span> <span class="text-muted"><?php echo $apporteur_id; ?></span></td>
            <?php foreach(["ParcellaireAffectation" => "parcellaireaffectation", "ParcellaireManquant" => "parcellairemanquant", "ParcellaireIrrigable" => "parcellaireirrigable"] as $type => $baseurl): ?>
            <td class="text-center <?php if($apporteur->getDeclarationStatut($type) == ParcellaireAffectationCoopApporteur::STATUT_VALIDE): ?>success text-success<?php endif; ?>">
                <?php if($apporteur->getDeclarationStatut($type) == ParcellaireAffectationCoopApporteur::STATUT_VALIDE): ?>
                    <a class="text-success" href="<?php echo url_for($baseurl.'_visualisation', array('id' => $apporteur->getDeclaration($type)->_id, 'coop' => $parcellaireAffectationCoop->_id)) ?>">Voir la déclaration</a><br/><span class="glyphicon glyphicon-ok-sign"></span>
                <?php elseif($apporteur->getDeclarationStatut($type) == ParcellaireAffectationCoopApporteur::STATUT_EN_COURS): ?>
                    <a href="<?php echo url_for($baseurl.'_edit', array('id' => $apporteur->getDeclaration($type)->_id, 'coop' => $parcellaireAffectationCoop->_id)) ?>">Continuer la déclaration</a><br/><br/>
                <?php elseif($type == ParcellaireAffectationClient::TYPE_MODEL && $apporteur->getDeclarationStatut($type) == ParcellaireAffectationCoopApporteur::STATUT_A_SAISIR): ?>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for($baseurl.'_create', array('identifiant' => $apporteur->getEtablissementIdentifiant(), 'periode' => $parcellaireAffectationCoop->periode, 'coop' => $parcellaireAffectationCoop->_id)) ?>">Saisir la déclaration</a>
                    <br/>
                    <a class="btn_saisie_affectation_parcellaire text-muted" href="<?php echo url_for('parcellaireaffectationcoop_switch', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => $apporteur->getEtablissementIdentifiant(), "sens" => "0")) ?>">Pas cette année</a>
                <?php elseif($type != ParcellaireAffectationClient::TYPE_MODEL && $apporteur->getDeclarationStatut($type) == ParcellaireAffectationCoopApporteur::STATUT_A_SAISIR && (!ParcellaireConfiguration::getInstance()->isParcellesFromAffectationparcellaire() || $apporteur->getDeclarationStatut(ParcellaireAffectationClient::TYPE_MODEL) == ParcellaireAffectationCoopApporteur::STATUT_VALIDE)): ?>
                    <a class="btn_saisie_affectation_parcellaire" href="<?php echo url_for($baseurl.'_create', array('identifiant' => $apporteur->getEtablissementIdentifiant(), 'periode' => $parcellaireAffectationCoop->periode, 'coop' => $parcellaireAffectationCoop->_id)) ?>">Saisir la déclaration</a>
                    <a class="btn btn-xs btn-default mt-2" href="<?php echo url_for('parcellaireaffectationcoop_'.strtolower($type).'_reconduction', ['id' => $parcellaireAffectationCoop->_id, 'apporteur' => $apporteur->getEtablissementIdentifiant()]) ?>">
                        <i class="glyphicon glyphicon-refresh"></i> Reconduire
                    </a>
                <?php elseif($type == ParcellaireAffectationClient::TYPE_MODEL): ?>
                    Aucune parcelle identifiée
                    <br/>
                    <a class="btn_saisie_affectation_parcellaire text-muted" href="<?php echo url_for('parcellaireaffectationcoop_switch', array('sf_subject' => $parcellaireAffectationCoop, 'apporteur' => $apporteur->getEtablissementIdentifiant(), "sens" => "1")) ?>">
                    <?php if ($apporteur->intention): ?>
                        MaJ
                    <?php else: ?>
                        Ré-Activer
                    <?php endif; ?>
                    </a>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
    <?php endforeach; ?>

    </table>
    <div class="row row-margin row-button">
        <div class="col-xs-4"><a href="<?php echo url_for("parcellaireaffectationcoop_apporteurs", $parcellaireAffectationCoop) ?>" id="bnt_affectation_retour_liste_coop" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
        <div class="col-xs-4 text-center">
            <a href="<?php echo url_for("parcellaireaffectationcoop_exportcsv", $parcellaireAffectationCoop) ?>" class="btn btn-primary">Export CSV des affectations validées</a>
        </div>
        <div class="col-xs-4 text-right">
        </div>
    </div>
</form>

<?php use_javascript('hamza_style.js'); ?>
