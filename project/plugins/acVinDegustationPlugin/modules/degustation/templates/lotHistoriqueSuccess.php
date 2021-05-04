<?php use_helper('Date'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Float') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" ><?php echo $lot->campagne ?></a>
  <li><a href="" class="active" >N° dossier : <?php echo $lot->numero_dossier ?> - N° archive : <?php echo $lot->numero_archive ?></a></li>
</ol>

<h2><?php echo $etablissement->getNom(); ?> - Historique du lot n° <?php echo $lot->numero_archive; ?></h2>

<div class="row">
    <div class="col-xs-5" style="padding-top: 30px;">
<?php include_partial('chgtdenom/infoLotOrigine', array('lot' => $lot, 'opacity' => false)); ?>
    </div>
    <div class="col-xs-7">
<?php if (count($mouvements)): ?>
    <table class="table table-condensed table-striped">
      <thead>
          <th class="col-sm-1">Document</th>
        <th class="col-sm-1">Date</th>
        <th class="col-sm-8">Étape / Détail</th>
        <th class="col-sm-1"></th>
      </thead>
      <tbody>
        <?php $lastiddate = ''; ?>
        <?php foreach($mouvements as $lotKey => $mouvement): if (isset(Lot::$libellesStatuts[$mouvement->value->statut])): ?>
          <?php $url = url_for(strtolower($mouvement->value->document_type).'_visualisation', array('id' => $mouvement->value->document_id)); ?>
          <?php $class = ($lastiddate == preg_replace("/ .*$/", "", $mouvement->value->document_id.$mouvement->value->date)) ? "text-muted": null ; ?>
              <tr>
                  <td>
                      <a href="<?php echo $url; ?>" class="<?php echo $class; ?>">
                      <?php echo $mouvement->value->document_type;  ?>
                      </a>
                  </td>
                <td class="<?php echo $class; ?>">
                    <?php echo format_date($mouvement->value->date, "dd/MM/yyyy", "fr_FR");  ?>
                </td>

                <td><?php echo showLotStatusCartouche($mouvement->value->statut, $mouvement->value->detail); ?></td>

                <td class="text-right">
                    <a href="<?php echo $url; ?>" class="btn btn-default btn-xs<?php echo " ".$class; ?>">accéder&nbsp;<span class="glyphicon glyphicon-chevron-right <?php echo $class; ?>"></span></a>
                    <?php $lastiddate = $mouvement->value->document_id.preg_replace("/ .*$/", "", $mouvement->value->date) ; ?>
                </td>
            </tr>
            <?php endif; endforeach; ?>
            <tr>
              <td colspan="3">
              </td>
              <td class="text-right">
                  <div class="dropdown" style="display: inline-block">
                      <button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                          Traiter / Modifier
                          <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                  <?php if ($mouvement->value->statut == Lot::STATUT_MANQUEMENT_EN_ATTENTE): ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_redeguster', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id, 'back' => 'degustation_manquements')) ?>" onclick="return confirm('Confirmez vous de rendre dégustable à nouveau ce lot ?')">Redéguster</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_lot', array('identifiant' => $mouvement->value->declarant_identifiant, 'lot' => $mouvement->value->document_id.':'.$mouvement->value->lot_unique_id)) ?>">Déclassement / Chgmt denom.</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_recours_oc', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous le recours à l\'OC ?')">Recours OC</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_conforme_appel', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous la mise en conformité de ce lot en appel ?')" >Conforme en appel</a></li>
                  <?php elseif ($mouvement->value->statut == Lot::STATUT_RECOURS_OC): ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_conforme_appel', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous la mise en conformité de ce lot en appel ?')" >Conforme en appel</a></li>
                  <?php elseif ($mouvement->value->statut == Lot::STATUT_ELEVAGE_EN_ATTENTE): ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('drev_eleve', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onclick="return confirm('Confirmez vous de rendre dégustable ce lot ?')">Permettre la dégustation</a></li>
                 <?php elseif ($mouvement->value->statut == Lot::STATUT_AFFECTABLE): ?>
                       <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_reputeconforme', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Changer en réputé conforme</a></li>
                 <?php elseif ($mouvement->value->statut == Lot::STATUT_NONAFFECTABLE): ?>
                       <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_affectable', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Retirer le "réputé conforme"</a></li>
                       <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_lot', array('identifiant' => $mouvement->value->declarant_identifiant, 'lot' => $mouvement->value->document_id.':'.$mouvement->value->lot_unique_id)) ?>">Déclassement / Chgmt denom.</a></li>
                <?php endif; ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_modification', array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Modifier les informations du lot</a></li>
                </ul>
                </div>
              </td>
          </tr>
          <tbody>
          </table>
          <?php endif; ?>
      </div>

  </div>

    <div>
        <a href="<?php echo url_for('degustation'); ?>" class=" btn btn-default" alt="Retour"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
