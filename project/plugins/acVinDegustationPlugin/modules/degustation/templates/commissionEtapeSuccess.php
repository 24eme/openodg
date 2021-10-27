<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_COMMISSION)); ?>


<div class="page-header no-border">
  <h2>Commission</h2>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Documents nécessaires à l'organisation d'une commission</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
          <div class="row">
            <div class="col-xs-12">
              <strong>Pdf des étiquettes pour l'organisation des tables</strong>
              <br/>
            </div>
          </div>
          <div class="row">
            <?php if($degustation->isAnonymized()): ?>
              <div class="col-xs-12">
                <br/>
                <ul class="list-group">
                  <li class="list-group-item"><a id="btn_degustation_fiche_tables_echantillons_par_dossier_pdf" href="<?php echo url_for('degustation_fiche_tables_echantillons_par_dossier_pdf', $degustation) ?>" ><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche des lots ventilés (triés par Numéro de dossier)</a></li>
                  <li class="list-group-item"><a id="btn_pdf_degustation_fiche_tables_echantillons_par_anonymat_pdf" href="<?php echo url_for('degustation_fiche_tables_echantillons_par_anonymat_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche des lots ventilés (triés par Numéro d'anonymat)</a></li>
                  <li class="list-group-item">
                      <span class="glyphicon glyphicon-file"></span>&nbsp;Étiquettes pour tables (
                      <a id="btn_pdf_degustation_etiquettes_tables_echantillons_par_anonymat_pdf" href="<?php echo url_for('degustation_etiquettes_tables_echantillons_par_anonymat_pdf', $degustation) ?>">triées par Numéro d'anonymat </a>
                       -
                      <a id="btn_pdf_degustation_etiquettes_tables_echantillons_par_anonymat_pdf" href="<?php echo url_for('degustation_etiquettes_tables_echantillons_par_unique_id_pdf', $degustation) ?>">triées par Numéro de dossier</a>
                      )
                  </li>
                </ul>
                <br/>
            </div>
          <?php else: ?>
            <div class="col-xs-12">
              La dégustation n'est <strong>pas encore anonymisée</strong> actuellement.<br/>
            </div>
          <?php endif; ?>
          </div>
          <div class="row">
            <div class="col-xs-12">
              <br/>
              <strong>Pdf des présences et notations de la commissions</strong>
              <br/>
            </div>
          </div>
          <div class="row">
          <div class="col-xs-12">
            <br/>
            <ul class="list-group">
              <li class="list-group-item"><a id="btn_pdf_presence_degustateurs" href="<?php echo url_for('degustation_fiche_presence_degustateurs_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Feuille de présence des dégustateurs</a></li>
              <li class="list-group-item"><a id="btn_pdf_fiche_individuelle_degustateurs" href="<?php echo url_for('degustation_fiche_individuelle_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche individuelle des dégustateurs (<?php echo $infosDegustation["nbDegustateursConfirmes"] ?>)</a></li>
              <li class="list-group-item"><a id="btn_pdf_fiche_resultats_table" href="<?php echo url_for('degustation_fiche_recap_tables_pdf', $degustation) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche résultats par table (<?php echo $infosDegustation["nbTables"] ?>)</a></li>
            </ul>
            <br/>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12">
            <br/>
            <strong>Documents tiers</strong>
            <br/>
          </div>
        </div>
        <div class="row">
        <div class="col-xs-12">
          <br/>
          <ul class="list-group">
            <li class="list-group-item">
                <a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des lots pour labo</a>
            </li>
          </ul>
          <br/>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>

<div class="row row-button">
  <div class="col-xs-4"><a href="<?php echo url_for('degustation_tables_etape', $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a></div>
  <div class="col-xs-4 text-center">
  </div>
  <div class="col-xs-4 text-right"><a id="btn_suivant" class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_resultats_etape', $degustation) ?>" >Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></div>
</div>
