<?php use_helper('Float') ?>
<?php use_helper('Lot') ?>
<?php use_javascript('hamza_style.js'); ?>
<?php use_javascript('degustation.js'); ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES)); ?>

<div class="page-header no-border">
    <h2>Documents pour la tournée</h2>
</div>

<h4>Voici la liste des documents téléchargeables pour cette tournée :</h4>
<div class="row" style="padding-top: 30px; padding-bottom: 50px;">
  <div class="col-md-4 col-md-offset-4">
      <ul style="list-style-type:none;">
          <li style="padding-bottom: 10px;"><a id="btn_pdf_fiche_tournee_prelevement" href="<?php echo url_for('degustation_fiche_lots_a_prelever_pdf', array('sf_subject' => $degustation)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche tournée</a></li>
          <?php if ($secteurs = array_keys($degustation->getLotsBySecteur(false)->getRawValue())): ?>
          <ul style="list-style-type:none; padding-bottom: 10px;">
              <?php foreach($secteurs as $secteur): ?>
              <li>
                  <a href="<?php echo url_for('degustation_fiche_lots_a_prelever_pdf', array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>"><?php echo $secteur ?></a>
              </li>
              <?php endforeach ?>
          </ul>
          <?php endif; ?>
          <li style="padding-bottom: 10px;"><a id="btn_pdf_fiche_individuelle_lots_a_prelever" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', array('sf_subject' => $degustation)) ?>"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche de prélèvements</a></li>
          <?php if ($secteurs = array_keys($degustation->getLotsBySecteur(false)->getRawValue())): ?>
          <ul style="list-style-type:none; padding-bottom: 10px;">
              <?php foreach($secteurs as $secteur): ?>
              <li>
                  <a href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', array('sf_subject' => $degustation, 'secteur' => $secteur)) ?>"><?php echo $secteur ?></a>
              </li>
              <?php endforeach ?>
          </ul>
          <?php endif; ?>
          <li style="padding-bottom: 10px;">
              <?php if(DegustationConfiguration::getInstance()->hasAnonymat4labo()) : ?>
                  <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['id' => $degustation->_id, 'anonymat4labo' => true]) ?>"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement (avec anonymat labo)</a>
              <?php else : ?>
                  <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['sf_subject' => $degustation]) ?>"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement</a>
              <?php endif ?>
          </li>
          <?php if ($secteurs = array_keys($degustation->getLotsBySecteur(false)->getRawValue())): ?>
          <ul style="list-style-type:none; padding-bottom: 10px;">
              <?php foreach($secteurs as $secteur): ?>
              <li>
                  <a href="<?php echo url_for('degustation_etiquette_pdf', array('sf_subject' => $degustation, 'secteur' => $secteur, 'anonymat4labo' => DegustationConfiguration::getInstance()->hasAnonymat4labo())) ?>"><?php echo $secteur ?></a>
              </li>
              <?php endforeach ?>
          </ul>
          <?php endif; ?>
          <li style="padding-bottom: 10px;"><a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des étiquettes</a></li>
          <li style="padding-bottom: 10px;"><a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>?labo=1"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur pour labo</a></li>
      </ul>
  </div>
</div>

<?php include_partial('degustation/pagination', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_TOURNEES, 'is_enabled' => true)); ?>
