<?php
if(
    ! $etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR )
    && ! $etablissement->hasFamille(EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR)
): return;
endif; ?>

<?php use_helper('Date'); ?>

<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if($parcellaireIrrigue): ?>panel-primary panel-irrigue <?php elseif($parcellaireIrrigable && !$parcellaireIrrigable->validation): ?>panel-primary panel-irrigable <?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Déclaration de renonciation à produire<?php echo $periode ?></h3>
        </div>
<?php if (!$parcellaireIrrigable): ?>
    <?php if (!ParcellaireIrrigableConfiguration::getInstance()->isOpen()): ?>
      <div class="panel-body">
          <?php if(date('Y-m-d') > ParcellaireIrrigableConfiguration::getInstance()->getDateOuvertureFin()): ?>
          <p class="explications">Le Téléservice « DRaP » est fermé.</p>
          <?php else: ?>
          <p class="explications">Le Téléservice « DRaP » sera ouvert à partir du <?php echo format_date(ParcellaireIrrigableConfiguration::getInstance()->getDateOuvertureDebut(), "D", "fr_FR") ?>.</p>
          <?php endif; ?>
          <div class="actions">
              <?php if ($sf_user->isAdminODG()): ?>
                  <a class="btn btn-default btn-block" href="<?php echo url_for('drap_edit', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><?php if(!$parcellaireIrrigable): ?>Démarrer la déclaration<?php else: ?>Voir ou continuer la déclaration<?php endif; ?></a>
              <?php endif; ?>
          </div>
      </div>
      <?php elseif ($needAffectation): ?>
      <div class="panel-body">
          <p class="explications">Cette déclaration s'appuie sur l'affectation parcellaire qui n'a pas encore été saisie et approuvée pour la période <?php echo $periode ?>.</p>
      </div>
      <?php elseif (!$parcellaire): ?>
      <div class="panel-body">
          <p class="explications">Les données de votre parcellaire ne sont pas présente sur la plateforme.<br/><br/>Il ne vous est donc pas possible de déclarer vos parcelles irrigables : <a href="<?php echo url_for("parcellaire_declarant", $etablissement) ?>">Voir le parcellaire</a></p>
      </div>
      <?php else:  ?>
    <div class="panel-body">
        <p class="explications">Identifier ou mettre à jour vos parcelles avant de déclarer vos renonciation à produire<?php echo $periode ?>.</p>
        <div class="actions">
            <a class="btn btn-block btn-default" href="<?php echo url_for('drap_create', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>">Démarrer la déclaration</a>
        </div>
    </div>
    <?php endif; ?>
    <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
        <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireirrigable')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
    </div>
<?php elseif(!$parcellaireIrrigable->validation): ?>
    <div class="panel-body">
        <p class="explications">Vous avez débuté votre Identification des parcelles en renoncement à produire sans la valider.</p>
        <div class="actions">
            <a class="btn btn-block btn-primary" href="<?php echo url_for('drap_edit', $parcellaireIrrigable) ?>">Continuer la déclaration « irrigable »</a>
            <a onclick='return confirm("Êtes vous sûr de vouloir supprimer cette saisie ?");' class="btn btn-xs btn-default btn-block" href="<?php echo url_for('drap_delete', $parcellaireIrrigable) ?>"><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Supprimer le brouillon</a>
        </div>
    </div>
    <div class="panel-footer" style="padding-top: 0; padding-bottom: 0;">
        <a href="<?php echo url_for('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireirrigable')) ?>" class="btn btn-xs btn-link btn-block">Voir tous les documents</a>
    </div>
<?php endif; ?>
    </div>
</div>
