<?php use_helper('Float') ?>

<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => $active)); ?>


<div class="page-header no-border">
  <h2>Prélévements des lots/ Convocations des dégustateurs</h2>
</div>
<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Prélèvements</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
          <?php if(!intval($infosDegustation["nbLotsPrelevesSansLeurre"])): ?>
          <div class="row">
              <div class="col-xs-12 text">
                <p class="alert alert-warning">
                    <span class="glyphicon glyphicon-warning-sign"></span>&nbsp;Vous n'avez aucun prélèvements effectués
                </p>
              </div>
          </div>
      <?php endif; ?>
        <div class="row">
            <div class="col-xs-12">
                <strong>Organisation des prélèvements</strong>
                <br/>
                <br/>
            </div>
          <div class="col-xs-12">
              <a id="btn_pdf_fiche_tournee_prelevement" href="<?php echo url_for('degustation_fiche_lots_a_prelever_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche tournée prélevement</a>
              <a id="btn_pdf_fiche_individuelle_lots_a_prelever" href="<?php echo url_for('degustation_fiche_individuelle_lots_a_prelever_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-file"></span>&nbsp;Fiche individuelle des lots à prélever</a>
              <?php if(DegustationConfiguration::getInstance()->hasAnonymat4labo()) : ?>
                  <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', ['id' => $degustation->_id, 'anonymat4labo' => true]) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement (avec anonymat labo)</a>
              <?php else : ?>
                  <a id="btn_pdf_etiquettes_de_prelevement" href="<?php echo url_for('degustation_etiquette_pdf', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-th"></span>&nbsp;Étiquettes de prélèvement</a>
              <?php endif ?>
              <a id="btn_csv_etiquette" href="<?php echo url_for('degustation_etiquette_csv', $degustation) ?>" class="btn btn-default btn-xs"><span class="glyphicon glyphicon-list"></span>&nbsp;Tableur des lots pour labo</a>
              <br/>
              <br/>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-3">
              <table class="table table-condensed table-bordered table-striped">
                  <tr>
                      <td class="col-xs-1 text-right"><?php echo count($degustation->getAdherentsByLotsWithoutStatut(Lot::STATUT_PRELEVE)); ?></td>
                      <td>opérateurs à prélever</td>
                  </tr>
                  <tr>
                      <td class="col-xs-1 text-right"><?php echo count($degustation->getAdherentsPreleves()); ?></td>
                      <td>opérateurs prélévés</td>
                  </tr>
                  <tr>
                      <th class="col-xs-1 text-right"><?php echo count($degustation->getAdherentsPreleves()) + count($degustation->getAdherentsByLotsWithoutStatut(Lot::STATUT_PRELEVE)); ?></th>
                      <th>opérateurs au total</th>
                  </tr>

                <tr>
                    <td class="col-xs-1 text-right"><?php echo count($degustation->getLotsPrelevables()) -  count($degustation->getLotsPreleves()) ?></td>
                    <td>lots restant à prélever</td>
                </tr>
                <tr>
                    <td class="col-xs-1 text-right"><?php echo count($degustation->getLotsPreleves()) ?></td>
                </tr>
                <tr>
                    <td class="col-xs-1 text-right"><?php echo count($degustation->getLotsSansVolume()); ?></td>
                    <td>lots annulés</td>
                </tr>
                <tr>
                    <th class="col-xs-1 text-right"><?php echo count($degustation->getLots()) ?></th>
                    <th>lots au total</th>
                </tr>
            </table>
        </div>
        <div class="col-xs-3">
            <table class="table-condensed">
                <tr><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td></tr>
                <tr><td>&nbsp;</td></tr>
            </table>
          </div>
          <div class="col-xs-12 text-right">
              <a id="btn_suivi_prelevement" class="btn btn-default btn-sm" href="<?php echo url_for('degustation_preleve', $degustation) ?>" >&nbsp;Saisir les prélévements effectués&nbsp;<span class="glyphicon glyphicon-pencil"></span></a>
         </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="row">
  <div class="col-xs-12">
        <?php include_partial('degustation/convocationDegustateurs', array('degustation' => $degustation, 'infosDegustation' => $infosDegustation)) ?>
  </div>
</div>

<?php include_partial('degustation/pagination', array('degustation' => $degustation, 'active' => $active, 'is_enabled' => $infosDegustation["nbLotsPrelevesSansLeurre"])); ?>
