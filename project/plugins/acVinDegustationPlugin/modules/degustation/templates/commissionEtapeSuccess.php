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
            <div class="col-xs-12">Pdf et documents nécessaires à l'organisation d'une commission</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
        <div class="row">
            <?php if($degustation->isAnonymized()): ?>
              <div class="col-xs-12">
                <br/>
                <a href="<?php echo url_for('degustation_fiche_echantillons_preleves_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche lots ventilés (Anonymisés)</a>
                <a href="<?php echo url_for('degustation_fiche_echantillons_preleves_table_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche lots ventilés (Anonymisés par table)</a>
                <a href="<?php echo url_for('degustation_etiquette_anonymes_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Tableau des étiquettes (Anonymisés)</a>
                <br/>
            </div>
          <?php else: ?>
            <div class="col-xs-12">
              La dégustation n'est <strong>pas encore anonymisée</strong> actuellement.<br/>
            </div>
          <?php endif; ?>
          <div class="col-xs-12">
            <a href="<?php echo url_for('degustation_fiche_individuelle_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche individuelle des degustateurs (<?php echo $infosDegustation["nbDegustateursConfirmes"] ?>)</a>
            <a href="<?php echo url_for('degustation_fiche_recap_tables_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche résultats par table (<?php echo $infosDegustation["nbTables"] ?>)</a>
            <a href="<?php echo url_for('degustation_proces_verbal_degustation_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de procès verbal</a>
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
  <div class="col-xs-4 text-right"><a class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_resultats_etape', $degustation) ?>" >Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a></div>
</div>
