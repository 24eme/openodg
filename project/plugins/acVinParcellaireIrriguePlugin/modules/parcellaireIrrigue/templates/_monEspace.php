<?php if(! $etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR )): return; endif; ?>

<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if($parcellaireIrrigue): ?>panel-primary panel-irrigue <?php elseif($parcellaireIrrigable && !$parcellaireIrrigable->validation): ?>panel-primary<?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclarations d'irrigation <?php echo $periode ?></h3>
        </div>
<?php if (!$parcellaireIrrigable): ?>
    <?php if (!ParcellaireIrrigableConfiguration::getInstance()->isOpen()): ?>
      <div class="panel-body">
          <?php if(date('Y-m-d') > ParcellaireIrrigableConfiguration::getInstance()->getDateOuvertureFin()): ?>
          <p class="explications">Le Téléservice « Irrigable » est fermé.</p>
          <?php else: ?>
          <p class="explications">Le Téléservice « Irrigable » sera ouvert à partir du <?php echo format_date(ParcellaireIrrigableConfiguration::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
          <?php endif; ?>
          <div class="actions">
              <?php if ($sf_user->isAdminODG()): ?>
                  <a class="btn btn-default btn-block" href="<?php echo url_for('parcellaireirrigable_edit', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><?php if(!$parcellaireIrrigable): ?>Démarrer la déclaration « Irrigable »<?php else: ?>Voir ou continuer l'Irrigable<?php endif; ?></a>
              <?php endif; ?>
          </div>
      </div>
      <?php elseif ($needAffectation): ?>
      <div class="panel-body">
          <p class="explications">Cette déclaration s'appuie sur l'affectation parcellaire qui n'a pas encore été saisie et approuvée pour la période <?php echo $periode ?>.</p>
      </div>
      <?php elseif (!$parcellaire): ?>
      <div class="panel-body">
          <p class="explications">Les données de votre parcellaire ne sont pas présente sur la plateforme.<br/><br/>Il ne vous est donc pas possible de déclarer vos pieds morts ou manquants : <a href="<?php echo url_for("parcellaire_declarant", $etablissement) ?>">Voir le parcellaire</a></p>
      </div>
      <?php else:  ?>
    <div class="panel-body">
        <p class="explications">Identifier ou mettre à jour vos parcelles irrigables.<br/>&nbsp;</p>
        <div class="actions">
            <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireirrigable_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la déclaration « Irrigable »</a>
        </div>
    </div>
    <?php endif; ?>
    <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
        <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireirrigable')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
    </div>
<?php elseif(!$parcellaireIrrigable->validation): ?>
    <div class="panel-body">
        <p class="explications">Vous avez débuté votre Identification des parcelles irrigables sans la valider.</p>
        <div class="actions">
            <a class="btn btn-block btn-primary" href="<?php echo url_for('parcellaireirrigable_edit', $parcellaireIrrigable) ?>">Continuer la déclaration « irrigable »</a>
            <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-default btn-block" href="<?php echo url_for('parcellaireirrigable_delete', $parcellaireIrrigable) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
        </div>
    </div>
    <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
        <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireirrigable')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
    </div>
<?php elseif ($parcellaireIrrigable && $parcellaireIrrigable->validation): ?>
    <?php if (!ParcellaireIrrigueConfiguration::getInstance()->isOpen()): ?>
          <div class="panel-body">
              <p class="explications">
              <?php if(date('Y-m-d') > ParcellaireIrrigueConfiguration::getInstance()->getDateOuvertureFin()): ?>
              Le Téléservice « Irrigué » est fermé. Pour toute question, veuillez contacter directement l'ODG.
              <?php else: ?>
                  Le Téléservice « Irrigué »  sera ouvert à partir du <?php echo format_date(ParcellaireIrrigueConfiguration::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>
              <?php endif; ?>
              </p>
              <div class="actions">
                  <div class="actions">
                  <?php if ($sf_user->isAdminODG()): ?>
                          <a class="btn btn-default btn-block" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><?php if(!$parcellaireIrrigue): ?>Démarrer un « Irrigué »<?php else: ?>Visualiser et continuer l'Irrigué<?php endif; ?></a>
                  <?php endif; ?>
                        <div class="panel-default mt-2">
                        <a class="panel-heading btn btn-block btn-xs p-1" href="<?php echo url_for('parcellaireirrigable_visualisation', $parcellaireIrrigable) ?>">Voir la déclaration « Irrigable »</a>
                    </div>
              </div>
              </div>
          </div>
      <?php else:  ?>
        <div class="panel-body">
            <p class="explications"><?php if(!$parcellaireIrrigue): ?>Identifier<?php else: ?>Mettre à jour<?php endif; ?> vos parcelles irriguées depuis votre <a href="<?php echo url_for('parcellaireirrigable_visualisation', array('sf_subject' => $parcellaireIrrigable)) ?>">déclaration d'irrigabilité <?php echo $periode; ?></a>.</p>
          	<div class="actions">
                <a class="btn btn-block btn-default" href="<?php echo url_for('parcellaireirrigue_edit', array('sf_subject' => $etablissement, 'periode' => $periode, 'papier' => false)) ?>"><?php if(!$parcellaireIrrigue): ?>Démarrer un « Irrigué »<?php else: ?>Visualiser et continuer l'irrigué<?php endif; ?></a>
            </div>
        </div>
      <?php endif; ?>
      <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
          <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireirrigue')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
      </div>
<?php endif; ?>
    </div>
</div>
