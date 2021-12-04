<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_COMMISSION)); ?>

<div class="page-header no-border">
    <h2>Commission</h2>
</div>

<div class="panel panel-default" style="min-height: 160px">
    <div class="panel-heading">
        <h2 class="panel-title">Documents nécessaires à l'organisation d'une commission</h2>
    </div>
    <div class="panel-body">
        <h4>Organisation des tables</h4>
        <ul class="list-group">
            <li class="list-group-item">
                <span class="glyphicon glyphicon-file"></span>&nbsp;Fiche des lots ventilés (
                <a id="btn_pdf_degustation_fiche_tables_echantillons_par_anonymat_pdf" href="<?php echo url_for('degustation_fiche_tables_echantillons_par_anonymat_pdf', $degustation) ?>">triées par Numéro d'anonymat </a>
                -
                <a id="btn_degustation_fiche_tables_echantillons_par_dossier_pdf" href="<?php echo url_for('degustation_fiche_tables_echantillons_par_dossier_pdf', $degustation) ?>">triées par Numéro de dossier</a>
                )
            </li>
            <li class="list-group-item">
                <span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes pour tables (
                <a id="btn_pdf_degustation_etiquettes_tables_echantillons_par_anonymat_pdf" href="<?php echo url_for('degustation_etiquettes_tables_echantillons_par_anonymat_pdf', $degustation) ?>">triées par Numéro d'anonymat </a>
                -
                <a id="btn_pdf_degustation_etiquettes_tables_echantillons_par_unique_id_pdf" href="<?php echo url_for('degustation_etiquettes_tables_echantillons_par_unique_id_pdf', $degustation) ?>">triées par Numéro de dossier</a>
                )
            </li>
        </ul>

        <h4>Présences et notations de la commissions</h4>
        <ul class="list-group">
            <li class="list-group-item"><a id="btn_pdf_presence_degustateurs" href="<?php echo url_for('degustation_fiche_presence_degustateurs_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Feuille de présence des dégustateurs</a></li>
            <li class="list-group-item"><a id="btn_pdf_fiche_individuelle_degustateurs" href="<?php echo url_for('degustation_fiche_individuelle_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche individuelle des dégustateurs</a></li>
            <li class="list-group-item"><a id="btn_pdf_fiche_resultats_table" href="<?php echo url_for('degustation_fiche_recap_tables_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche des résultats par table</a></li>
        </ul>

        <h4>Tiers</h4>
        <ul class="list-group">
            <li class="list-group-item">
                <a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des lots pour les laboratoires</a>
            </li>
        </ul>
    </div>
</div>

<?php include_partial('degustation/convocationDegustateurs', array('degustation' => $degustation, 'infosDegustation' => $infosDegustation)) ?>

<div class="row row-button">
    <div class="col-xs-4"><a href="<?php echo url_for('degustation_tables_etape', $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
    <div class="col-xs-4 text-center">
    </div>
    <div class="col-xs-4 text-right"><a id="btn_suivant" class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_resultats_etape', $degustation) ?>" >Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></div>
</div>
