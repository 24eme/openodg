<?php use_helper('Float') ?>
<?php include_partial('degustation/breadcrumb', array('degustation' => $degustation)); ?>
<?php include_partial('degustation/step', array('degustation' => $degustation, 'active' => DegustationEtapes::ETAPE_CONVOCATIONS)); ?>
<?php $degustateur = null; $college = null; ?>

<div class="page-header no-border">
  <h2>Convocations des dégustateurs</h2>
</div>

<div class="row">
  <div class="col-xs-12">
    <div class="panel panel-default" style="min-height: 160px">
      <div class="panel-heading">
        <h2 class="panel-title">
          <div class="row">
            <div class="col-xs-12">Convocations</div>
          </div>
        </h2>
      </div>
      <div class="panel-body">
        <div class="row">
              <div class="col-xs-12">
                Envoyer une convocation <strong>aux dégustateurs</strong> suivants :
                <br/>
            </div>
            <div class="col-xs-6" >
            <br/>
            <?php echo include_partial('global/flash'); ?>
            </div>
          <div class="col-xs-6 text-right">
            <br/>
              <br/>
              <a class="btn btn-default btn-sm" style="" href="<?php echo url_for('degustation_convocations_mails', $degustation); ?>" onclick="return confirm('Voulez-vous envoyer les emails aux dégustateurs?')" >&nbsp;Convoquer les dégustateurs&nbsp;<span class="glyphicon glyphicon-send"></span></a>
          </div>
        </div>
        <br/>
        <div class="row">
              <div class="col-xs-12">
                  <div class="row row-condensed">
                  	<div class="col-xs-12">
                          <table class="table table-bordered table-condensed table-striped">
                          	<thead>
                              	<tr>
                                    <th class="col-xs-2">Collège</th>
                                    <th class="col-xs-7">Membre</th>
                                    <th class="col-xs-3">Email</th>
                                  </tr>
                          	</thead>
                          	<tbody>
                            <?php foreach ($degustation->degustateurs as $college => $degustateurs): ?>
                                <?php foreach ($degustateurs as $identifiant => $degustateur): ?>
                                <tr>
                                <td><?php echo DegustationConfiguration::getInstance()->getLibelleCollege($college) ?></td>
                                <td><a href="<?php echo url_for('compte_visualisation', array('identifiant' => $identifiant)) ?>" target="_blank"><?php echo $degustateur->get('libelle','') ?></a></td>
                                <td><?php echo (CompteClient::getInstance()->find($identifiant)->email); ?></td>
                                </tr>
                                <?php endforeach;?>
                            <?php endforeach; ?>
                          	</tbody>
                          </table>
                  	</div>
                  </div>

            </div>
        </div>
        <div class="row">
            <div class="col-xs-4 col-xs-offset-4 text-center">
                <button class="btn btn-sm btn-default" data-toggle="modal" data-target="#popupMailDegustateurs" type="button">&nbsp;Prévisualiser le mail&nbsp;</button>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row row-button">
  <div class="col-xs-4">
    <a href="<?php echo url_for('degustation_selection_degustateurs', $degustation) ?>" class="btn btn-default btn-upper"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
  </div>
  <div class="col-xs-4 text-center">
  </div>
  <div class="col-xs-4 text-right">
    <a id="btn_suivant" class="btn btn-primary btn-upper" href="<?php echo url_for('degustation_prelevements_etape', $degustation) ?>" >&nbsp;Valider&nbsp;<span class="glyphicon glyphicon-chevron-right"></span></a>
  </div>
</div>
<?php if($college && $degustateur): ?>
    <?php include_partial('degustation/popupMailDegustateurs',array('degustation' => $degustation, 'identifiant' => $identifiant, 'college' => $college)); ?>
<?php endif; ?>
