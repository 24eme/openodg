<ol class="breadcrumb">

  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getRegion(); ?></a></li>
  <?php endif; ?>
 <li><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href=""><?php if(isset($periode)): ?><?php echo $periode ?>-<?php echo $periode +1 ?><?php else: ?>Campagnes courante<?php endif; ?></a></li>
</ol>

<?php if ($sf_user->hasDrevAdmin() && class_exists("EtablissementChoiceForm")): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('declaration_etablissement_selection'), 'noautofocus' => true)); ?>
<?php endif; ?>

<div class="page-header">
    <div class="pull-right">
        <?php if ($sf_user->hasDrevAdmin()) $nb_campagne = 5 ; else $nb_campagne = 2; ?>
        <form method="GET" class="form-inline" action="">
            Campagnes :
            <select class="select2SubmitOnChange form-control" name="campagne">
                <option value="">Courante</option>
                <?php for($i=intval(ConfigurationClient::getInstance()->getCampagneVinicole()->getCurrentNext()); $i > intval(ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_PREMIERE_ANNEE)->getCurrent()) - $nb_campagne ; $i--): ?>
                    <option <?php if(isset($periode) && $periode == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i.'-'.($i + 1) ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-default">Changer</button>
        </form>
    </div>
    <h2>Eléments déclaratifs</h2>
    <?php include_partial('global/flash'); ?>
    <?php  if(!$sf_user->isAdmin() && ($etablissement->getSociete() && count($etablissement->getSociete()->getEtablissementsObj(false)) > 1)): ?>
      <section id="principal">
          <form id="choix_etablissement" method="post" action="<?php echo url_for('drev_societe_choix_etablissement', array('identifiant' => $etablissement->identifiant)) ?>">
            <br/>
               <div >
                  <div class="bloc_form bloc_form_condensed">
                  <?php echo $etablissementChoiceForm->renderHiddenFields() ?>
                  <?php echo $etablissementChoiceForm->renderGlobalErrors() ?>

                  <div class="row">
                      <?php echo $etablissementChoiceForm['etablissementChoice']->renderError() ?>
                      <div class="col-md-3"><?php echo $etablissementChoiceForm['etablissementChoice']->renderLabel() ?></div>
                      <div class="col-md-6"><?php echo $etablissementChoiceForm['etablissementChoice']->render(array('class' => 'select2autocomplete societe_choix_etablissement', 'style' => "width: 100%;")) ?></div>
                  </div>
                  </div>
               </div>
          </form>
      </section>
    <?php  endif; ?>
</div>

<p>Veuillez trouver ci-dessous l'ensemble de vos éléments déclaratifs</p>
<div class="row">
    <?php if(class_exists("ParcellaireManquantConfiguration") && ParcellaireManquantConfiguration::getInstance()->isModuleEnabled()): ?>
    <?php include_component('parcellaireManquant', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireManquantConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php include_component('fichier', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php if(class_exists("DRevConfiguration") && DRevConfiguration::getInstance()->isModuleEnabled() && !DRevConfiguration::getInstance()->isRevendicationParLots()): ?>
    <?php include_component('drev', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("DRev") && in_array('drev', sfConfig::get('sf_enabled_modules')) && class_exists("DRevConfiguration") && DRevConfiguration::getInstance()->isRevendicationParLots()): ?>
        <?php include_component('drev', 'monEspaceIGP', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("TravauxMarcConfiguration")): ?>
    <?php include_component('travauxmarc', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : TravauxMarcConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("DRevMarcConfiguration")): ?>
    <?php include_component('drevmarc', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevMarcConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("PMCConfiguration") && PMCConfiguration::getInstance()->isModuleEnabled()): ?>
    <?php include_component('pmc', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : PMCConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("PMCConfiguration") && PMCConfiguration::getInstance()->isModuleEnabled()): ?>
    <?php include_component('pmcNc', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : PMCConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("Conditionnement") && in_array('conditionnement', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('conditionnement', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("Transaction") && in_array('transaction', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('transaction', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("ChgtDenom") && in_array('chgtdenom', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('chgtdenom', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(in_array('parcellaireAffectationCoop', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_partial('parcellaireAffectationCoop/monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireAffectationConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireIrrigable") && in_array('parcellaireIrrigable', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIrrigable', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireAffectationConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireIrrigue") && in_array('parcellaireIrrigue', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIrrigue', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireAffectationConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if($sf_user->isAdmin() && class_exists("ParcellaireIntentionAffectation") && in_array('parcellaireIntentionAffectation', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIntentionAffectation', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireAffectationConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireAffectationConfiguration") && ParcellaireAffectationConfiguration::getInstance()->isModuleEnabled()): ?>
    <?php include_component('parcellaireAffectation', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireAffectationConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireAffectationCremantConfiguration") && ParcellaireAffectationCremantConfiguration::getInstance()->isModuleEnabled()): ?>
    <?php include_component('parcellaireAffectationCremant', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : ParcellaireAffectationCremantConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("IntentionCremantConfiguration") && IntentionCremantConfiguration::getInstance()->isModuleEnabled()): ?>
    <?php include_component('intentionCremant', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : IntentionCremantConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("TirageConfiguration")): ?>
    <?php include_component('tirage', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : TirageConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
    <?php if(class_exists("Adelphe") && in_array('adelphe', sfConfig::get('sf_enabled_modules')) && $etablissement->getMasterCompte()->hasDroit(AdelpheSecurity::DROIT_ADELPHE)): ?>
    <?php include_component('adelphe', 'monEspace', array('etablissement' => $etablissement, 'periode' => isset($periode) ? $periode : DRevConfiguration::getInstance()->getCurrentPeriode())); ?>
    <?php endif; ?>
</div>
<?php if(in_array('facturation', sfConfig::get('sf_enabled_modules'))): ?>
<div class="page-header">
<h2>Espace Facture</h2>
</div>
<div class="row">
    <div class="col-sm-6 col-md-4 col-xs-12">
        <div class="block_declaration panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Vos factures</h3>
            </div>
            <div class="panel-body">
                <p class="explications">Accéder à l'espace de mise à disposition de vos factures en téléchargement</p>
                <div class="actions">
                    <a class="btn btn-block btn-default" href="<?php echo (is_string($etablissement->getCompte()))? url_for('facturation_declarant', ['identifiant' => $etablissement->getMasterCompte()->_id]) : url_for('facturation_declarant', ['identifiant' => $etablissement->getCompte()->_id]); ?>">Voir les factures</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_partial('fichier/history', array('etablissement' => $etablissement, 'history' => PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)), 'limit' => Piece::LIMIT_HISTORY)); ?>
