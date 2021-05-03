<ol class="breadcrumb">

  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationDrevRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationDrevRegion(); ?></a></li>
  <?php endif; ?>
 <li><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href=""><?php echo $periode ?>-<?php echo $periode +1 ?></a></li>
</ol>

<?php if ($sf_user->isAdmin() && class_exists("EtablissementChoiceForm")): ?>
<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('declaration_etablissement_selection'), 'noautofocus' => true)); ?>
    </div>
</div>
<?php endif; ?>
<div class="page-header">
    <div class="pull-right">
        <?php if ($sf_user->hasDrevAdmin()): ?>
        <form method="GET" class="form-inline" action="">
            Campagne :
            <select class="select2SubmitOnChange form-control" name="campagne">
                <?php for($i=ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() * 1; $i > ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() - 5; $i--): ?>
                    <option <?php if($periode == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i.'-'.($i + 1) ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-default">Changer</button>
        </form>
        <?php else: ?>
            <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne; ?></span>
        <?php endif; ?>
    </div>
    <h2>Eléments déclaratifs</h2>
    <?php if ($sf_user->hasFlash('notice')): ?>
    <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('error')): ?>
    <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('error') ?></p>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('warning')): ?>
    <div class="alert alert-warning" role="alert"><?php echo $sf_user->getFlash('warning') ?></div>
    <?php endif; ?>
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
    <?php if(class_exists("DRev") && in_array('drev', sfConfig::get('sf_enabled_modules')) && !DRevConfiguration::getInstance()->isRevendicationParLots()): ?>
    <?php include_component('drev', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("DRev") && in_array('drev', sfConfig::get('sf_enabled_modules')) && DRevConfiguration::getInstance()->isRevendicationParLots()): ?>
        <?php include_component('drev', 'monEspaceIGP', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("DRevMarc")): ?>
    <?php include_component('drevmarc', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("Conditionnement") && in_array('conditionnement', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('conditionnement', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("Transaction") && in_array('transaction', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('transaction', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("ChgtDenom") && in_array('chgtdenom', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('chgtdenom', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("TravauxMarc")): ?>
    <?php include_component('travauxmarc', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("Parcellaire") && in_array('parcellaire', sfConfig::get('sf_enabled_modules')) && sfContext::getInstance()->getController()->componentExists('parcellaire', 'monEspace')): ?>
    <?php include_component('parcellaire', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireIrrigable") && in_array('parcellaireIrrigable', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIrrigable', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireIrrigue") && in_array('parcellaireIrrigue', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIrrigue', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if($sf_user->isAdmin() && class_exists("ParcellaireIntentionAffectation") && in_array('parcellaireIntentionAffectation', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIntentionAffectation', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireAffectation") && in_array('parcellaireAffectation', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireAffectation', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireCremant") && in_array('parcellaireCremant', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireCremant', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("IntentionCremant") && in_array('intentionCremant', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('intentionCremant', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php if(class_exists("Tirage")): ?>
    <?php include_component('tirage', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
    <?php endif; ?>
    <?php include_component('fichier', 'monEspace', array('etablissement' => $etablissement, 'periode' => $periode)); ?>
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
                    <a class="btn btn-block btn-default" href="<?php echo (is_string($etablissement->getCompte()))? url_for('facturation_declarant', $etablissement->getMasterCompte()) : url_for('facturation_declarant', $etablissement->getCompte()); ?>">Voir les factures</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_partial('fichier/history', array('etablissement' => $etablissement, 'history' => PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)), 'limit' => Piece::LIMIT_HISTORY)); ?>
