<?php use_helper('Date'); ?>
<?php use_helper('Lot'); ?>
<?php use_helper('Float') ?>

<ol class="breadcrumb hidden-print">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" ><?php echo $lot->campagne ?></a>
  <li><a href="" class="active" >N° dossier : <?php echo $lot->numero_dossier ?> - N° archive : <?php echo $lot->numero_archive ?></a></li>
</ol>

<h2><?php echo $etablissement->getNom(); ?> - Historique du lot n° <?php echo $lot->numero_archive; ?></h2>

<?php include_partial('global/flash'); ?>

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
        <th class="col-sm-1 hidden-print"></th>
      </thead>
      <tbody>
        <?php $lastiddate = ''; ?>
        <?php foreach($mouvements as $lotKey => $mouvement): if (isset(Lot::$libellesStatuts[$mouvement->value->statut])): ?>
          <?php $url = $sf_user->isAdminODG() === false ? '#' : url_for(strtolower($mouvement->value->document_type).'_visualisation', array('id' => $mouvement->value->document_id)); ?>
          <?php $class = ($lastiddate == preg_replace("/ .*$/", "", $mouvement->value->document_id.$mouvement->value->date)) ? "text-muted": null ; ?>
          <?php $class .= $sf_user->isAdminODG() === false ? ' disabled' : null; ?>
              <tr<?php if ($lot->unique_id !== $mouvement->value->lot_unique_id) { echo ' style="opacity:0.5"'; } ?>>
                  <td>
                      <a href="<?php echo $url; ?>" class="<?php echo $class; ?>">
                      <?php echo clarifieTypeDocumentLibelle($mouvement->value->document_type);  ?>
                      </a>
                  </td>
                <td class="<?php echo $class; ?>">
                    <?php echo format_date($mouvement->value->date, "dd/MM/yyyy", "fr_FR");  ?>
                </td>
                <!-- TODO trouver l'origine concrète du débordement du tableau. La solution display:grid peut être temporaire -->
                <td style="display:grid;"><p class="trunk-text" style="border-radius: 0.25em 0.25em 0 0; width: 100%; color:white;"><?php echo showLotStatusCartouche($mouvement->value, $sf_user->isAdminODG()); ?></p></td>

                <td class="text-right hidden-print">
                    <a href="<?php echo $url; ?>" class="btn btn-default btn-xs<?php echo " ".$class; ?>">accéder&nbsp;<span class="glyphicon glyphicon-chevron-right <?php echo $class; ?>"></span></a>
                    <?php $lastiddate = $mouvement->value->document_id.preg_replace("/ .*$/", "", $mouvement->value->date) ; ?>
                </td>
            </tr>
            <?php endif; endforeach; ?>

            <?php if ($sf_user->isAdminODG()): ?>
            <tr class="hidden-print">
              <td colspan="3">
              </td>
              <td class="text-right">
                  <div class="dropdown" style="display: inline-block">
                      <button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                          Traiter / Modifier
                          <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
                  <?php if ($mouvement->value->statut == Lot::STATUT_MANQUEMENT_EN_ATTENTE): ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_redeguster', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id, 'back' => 'degustation_nonconformites')) ?>" onclick="return confirm('Confirmez vous de rendre dégustable à nouveau ce lot ?')">Redéguster</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_from_lot', array('identifiant' => $mouvement->value->declarant_identifiant, 'lot' => $mouvement->value->document_id.':'.$mouvement->value->lot_unique_id)) ?>">Déclassement / Chgmt denom.</a></li>
                      <?php if (RegionConfiguration::getInstance()->hasOC() === false || Organisme::getInstance()->isOC()): ?>
                        <li><a class="dropdown-item" href="<?php echo url_for('degustation_recours_oc', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous le recours à l\'OC ?')">Recours OC</a></li>
                      <?php endif ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_conforme_appel', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous la mise en conformité de ce lot en appel ?')" >Conforme en appel</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_lever_nonconformite', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous la mise en conformité de ce lot en appel ?')" >Lever la non-conformite</a></li>
                  <?php elseif ($mouvement->value->statut == Lot::STATUT_RECOURS_OC): ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_conforme_appel', array('id' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous la mise en conformité de ce lot en appel ?')" >Conforme en appel</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_from_lot', array('identifiant' => $mouvement->value->declarant_identifiant, 'lot' => $mouvement->value->document_id.':'.$mouvement->value->lot_unique_id)) ?>">Déclassement / Chgmt denom.</a></li>
                      <li><a class="dropdown-item" href="<?php echo url_for('degustation_affectation_lot', array('id' => $mouvement->value->declarant_identifiant, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Affecter à une dégustation</a></li>
                  <?php elseif ($mouvement->value->statut == Lot::STATUT_ELEVAGE_EN_ATTENTE): ?>
                      <li><a class="dropdown-item" href="<?php echo url_for('drev_switch_eleve', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onclick="return confirm('Confirmez vous de rendre dégustable ce lot ?')">Enlever l'élevage et permettre la dégustation</a></li>
                 <?php elseif ($mouvement->value->statut == Lot::STATUT_AFFECTABLE): ?>
                       <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_reputeconforme', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Changer en réputé conforme</a></li>
                       <li><a class="dropdown-item" href="<?php echo url_for('drev_switch_eleve', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onclick="return confirm('Confirmez vous de rendre dégustable ce lot ?')">Mettre en élevage</a></li>
                       <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_delete', array('identifiant' => $mouvement->value->declarant_identifiant, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onclick="return confirm('Confirmez vous la suppression définitive de ce lot ?')">Supprimer ce lot</a></li>
                       <li><a class="dropdown-item" href="<?php echo url_for('degustation_affectation_lot', array('id' => $mouvement->value->declarant_identifiant, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Affecter à une dégustation</a></li>
                 <?php elseif ($mouvement->value->statut == Lot::STATUT_NONAFFECTABLE || $mouvement->value->statut == Lot::STATUT_NONAFFECTABLE_EN_ATTENTE): ?>
                       <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_affectable', array('id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Retirer le "réputé conforme"</a></li>
                <?php endif; ?>
                <?php if (in_array($mouvement->value->statut, array(Lot::STATUT_CONFORME, Lot::STATUT_NONCONFORME, Lot::STATUT_NONAFFECTABLE, Lot::STATUT_AFFECTABLE))): ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_from_lot', array('identifiant' => $mouvement->value->declarant_identifiant, 'lot' => $mouvement->value->document_id.':'.$mouvement->value->lot_unique_id)) ?>">Déclassement / Chgmt denom.</a></li>
                <?php endif; ?>
                <?php if (in_array($mouvement->value->statut, array(Lot::STATUT_CONFORME, Lot::STATUT_NONCONFORME))): ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('degustation_retirer', array('id' => $mouvement->value->declarant_identifiant, 'degustation_id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onclick="return (prompt('Pour confirmez le retrait de ce lot DÉGUSTÉ de la dégustation, merci d\'indiquer son numéro de lot (avec le 0 devant) :','') == '<?php echo $lot->numero_archive ; ?>' )">Retirer ce lot DÉGUSTÉ de la dégustation</a></li>
                <?php endif; ?>
                <?php if ($mouvement->value->statut == Lot::STATUT_ATTENTE_PRELEVEMENT): ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('degustation_retirer', array('id' => $mouvement->value->declarant_identifiant, 'degustation_id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onclick="return confirm('Confirmez vous le retrait de la dégustation de ce lot pour qu\' il soit affectable à un autre moment ?')">Retirer de la dégustation</a></li>
                <?php endif; ?>
                <?php if ($mouvement->value->statut == Lot::STATUT_ANONYMISE): ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('degustation_retirer', array('id' => $mouvement->value->declarant_identifiant, 'degustation_id' => $mouvement->value->document_id, 'unique_id' => $mouvement->value->lot_unique_id)) ?>" onlcik="return (prompt('Pour confirmez le retrait de ce lot ANONYMISÉ de la dégustation, merci d\'indiquer son numéro de lot : ', '') == '<?php echo $lot->numero_archive ; ?>')">Retirer ce lot ANONYMISÉ de la dégustation</a></li>
                <?php endif; ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('degustation_lot_modification', array('identifiant' => $lot->declarant_identifiant, 'unique_id' => $mouvement->value->lot_unique_id)) ?>">Modifier les informations du lot</a></li>
                <?php if (class_exists('Courrier') && ($sf_user->hasCredential(AppUser::CREDENTIAL_OI) || $sf_user->isAdmin())): ?>
                    <li><a class="dropdown-item" href="<?php echo url_for('courrier_lot_creation', array('identifiant' => $lot->declarant_identifiant, 'lot_unique_id' => $mouvement->value->lot_unique_id)) ?>">Créer un courrier</a></li>
                <?php endif; ?>
                <?php if ($mouvement->value->statut == Lot::STATUT_CONFORME_APPEL): ?>
                <li><a class="dropdown-item" href="<?php echo url_for('chgtdenom_create_from_lot', array('identifiant' => $mouvement->value->declarant_identifiant, 'lot' => $mouvement->value->document_id.':'.$mouvement->value->lot_unique_id)) ?>">Déclassement / Chgmt denom.</a></li>
                <?php endif; ?>
                <?php if (class_exists('Courrier') && $mouvement->value->document_type == CourrierClient::TYPE_MODEL): ?>
                    <?php if ($lot->isNonConforme()): ?>
                        <li><a class="dropdown-item" href="<?php echo url_for('courrier_redeguster', array('identifiant' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)) ?>" onclick="return confirm('Confirmez vous de rendre dégustable à nouveau ce lot ?')">Redéguster</a></li>
                        <?php if (RegionConfiguration::getInstance()->hasOC() === false || Organisme::getInstance()->isOC()): ?>
                            <li><a class="dropdown-item" href="<?php echo url_for('courrier_recours_oc', array('identifiant' => $mouvement->value->document_id, 'lot' => $mouvement->value->lot_unique_id)); ?>"  onclick="return confirm('Confirmez vous le recours à l\'OC ?')">Recours OC</a></li>
                        <?php endif ?>
                    <?php endif ?>
                <?php endif ?>
                </ul>
                </div>
              </td>
          </tr>
          <?php endif ?>
        </tbody>
          </table>
          <?php endif; ?>
      </div>

  </div>

    <div class="hidden-print">
        <a href="<?php echo url_for('degustation_declarant_lots_liste',array('identifiant' => $etablissement->identifiant, 'campagne' => $lot->campagne)); ?>" class=" btn btn-default" alt="Retour"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
