<?php if(!$etablissement->hasFamille(EtablissementFamilles::FAMILLE_COOPERATIVE)): return; endif; ?>
<?php use_helper('Date'); ?>
<?php $parcellaireAffectationCoop = ParcellaireAffectationCoopClient::getInstance()->find(ParcellaireAffectationCoopClient::getInstance()->buildId($etablissement->identifiant, $periode), acCouchdbClient::HYDRATE_JSON); ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel <?php if($parcellaireAffectationCoop): ?>panel-primary<?php else: ?>panel-default<?php endif; ?>">
        <div class="panel-heading">
            <h3 class="panel-title">Affectations parcellaires de vos apporteurs</h3>
        </div>
        <div class="panel-body">
            <p class="explications">Vous pouvez déclarer les affectations parcellaires de vos apporteurs.</p>
            <div class="actions">
                <a id="btn_affection_parcellaire_coop" class="btn btn-block <?php if($parcellaireAffectationCoop): ?>btn-primary<?php else: ?>btn-default<?php endif; ?>" href="<?php echo url_for('parcellaireaffectationcoop_edit', array('sf_subject' => $etablissement, 'periode' => $periode)) ?>"><?php if($parcellaireAffectationCoop): ?>Continuer à déclarer pour vos apporteurs<?php else: ?>Déclarer pour vos apporteurs<?php endif; ?></a>
            </div>
        </div>
    </div>
</div>
