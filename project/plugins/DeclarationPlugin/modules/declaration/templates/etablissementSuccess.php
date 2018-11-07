<ol class="breadcrumb">

  <li><a href="<?php echo url_for('accueil'); ?>">Déclarations</a></li>
  <li><a href="<?php echo url_for('declaration_etablissement', $etablissement); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>)</a></li>
  <li class="active"><a href=""><?php echo $campagne ?>-<?php echo $campagne +1 ?></a></li>
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
        <?php if ($sf_user->isAdmin()): ?>
        <form method="GET" class="form-inline" action="">
            Campagne :
            <select class="select2SubmitOnChange form-control" name="campagne">
                <?php for($i=ConfigurationClient::getInstance()->getCampagneManager()->getCurrent(); $i > ConfigurationClient::getInstance()->getCampagneManager()->getCurrent() - 5; $i--): ?>
                    <option <?php if($campagne == $i): ?>selected="selected"<?php endif; ?> value="<?php echo $i ?>"><?php echo $i; ?>-<?php echo $i+1 ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-default">Changer</button>
        </form>
        <?php else: ?>
            <span style="margin-top: 8px; display: inline-block;" class="text-muted">Campagne <?php echo $campagne ?>-<?php echo $campagne + 1 ?></span>
        <?php endif; ?>
    </div>
    <h2>Eléments déclaratifs</h2>
</div>

<p>Veuillez trouver ci-dessous l'ensemble de vos éléments déclaratifs</p>
<div class="row">
    <?php if(class_exists("DRev") && in_array('drev', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('drev', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("DRevMarc")): ?>
    <?php include_component('drevmarc', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("TravauxMarc")): ?>
    <?php include_component('travauxmarc', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireAffectation") && in_array('parcellaireAffectation', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireAffectation', 'monEspace', array('etablissement' => $etablissement, 'campagne' => ConfigurationClient::getInstance()->getCampagneManager()->getNext($campagne))); ?>
    <?php endif; ?>
    <?php if(class_exists("Parcellaire") && in_array('parcellaire', sfConfig::get('sf_enabled_modules')) && sfContext::getInstance()->getController()->componentExists('parcellaire', 'monEspace')): ?>
    <?php include_component('parcellaire', 'monEspace', array('etablissement' => $etablissement, 'campagne' => ConfigurationClient::getInstance()->getCampagneManager()->getNext($campagne))); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireIrrigable") && in_array('parcellaireIrrigable', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIrrigable', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireIrrigue") && in_array('parcellaireIrrigue', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireIrrigue', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php if(class_exists("ParcellaireCremant") && in_array('parcellaireCremant', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('parcellaireCremant', 'monEspace', array('etablissement' => $etablissement, 'campagne' => ConfigurationClient::getInstance()->getCampagneManager()->getNext($campagne))); ?>
    <?php endif; ?>
    <?php if(class_exists("IntentionCremant") && in_array('intentionCremant', sfConfig::get('sf_enabled_modules'))): ?>
    <?php include_component('intentionCremant', 'monEspace', array('etablissement' => $etablissement, 'campagne' => ConfigurationClient::getInstance()->getCampagneManager()->getNext($campagne))); ?>
    <?php endif; ?>
    <?php if(class_exists("Tirage")): ?>
    <?php include_component('tirage', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
    <?php endif; ?>
    <?php include_component('fichier', 'monEspace', array('etablissement' => $etablissement, 'campagne' => $campagne)); ?>
</div>
<?php if(in_array('facturation', sfConfig::get('sf_enabled_modules'))): ?>
<div class="page-header">
<h2>Espace Facture</h2>
</div>
<div class="row">
    <div class="col-sm-6 col-md-4 col-xs-12">
        <div class="block_declaration panel panel-success">
            <div class="panel-heading">
                <h3>Vos Factures<br /><br /></h3>
            </div>
            <div class="panel-body">
                <p>Accéder à l'espace de mise à disposition de vos factures en téléchargement<br /><br /><br /><br /><br /><br /></p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-primary" href="<?php echo url_for('facturation_declarant', $etablissement->getSociete()->getMasterCompte()); ?>">Voir les factures</a>
                </p>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php include_partial('fichier/history', array('etablissement' => $etablissement, 'history' => PieceAllView::getInstance()->getPiecesByEtablissement($etablissement->identifiant, $sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)), 'limit' => Piece::LIMIT_HISTORY)); ?>
