<style>
  .table thead > tr > th,
  .table tbody > tr > td {
    width: auto;
  }
</style>

<?php use_helper('Date'); ?>

<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation));
  $etablissement = $habilitation->getEtablissementObject();
 ?>

<div class="page-header no-border">
    <h2>Habilitations<?php if(!$habilitation->isLastOne()): ?> au <?php echo Date::francizeDate($habilitation->getDate()); ?><?php endif; ?></h2>
</div>

<?php if(isset($form)): ?>
    <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('habilitation_etablissement_selection'),  'noautofocus' => true)); ?>
<?php endif; ?>

<div class="well">
    <?php if ($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) && count(HabilitationClient::getInstance()->getDemandes($filtre)) && HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>
<a style="margin-bottom: 30px;" class="btn btn-sm btn-default pull-right" href="<?php echo url_for('habilitation_demande_globale', array('sf_subject' => $etablissement)) ?>"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Demande de modification globale</a>

<?php endif; ?>
<?php include_partial('etablissement/blocDeclaration', array('etablissement' => $habilitation->getEtablissementObject())); ?>
<?php if (CertipaqService::getInstance()->hasConfiguration()): ?>
<a style="margin-bottom: 30px;" class="pull-right" href="<?php echo url_for('certipaq_diff', array('sf_subject' => $etablissement)) ?>"><span class="glyphicon glyphicon-transfer"></span>&nbsp;Certipaq</a>
<?php endif; ?>
</div>

<?php include_partial('global/flash'); ?>

<?php if(!$habilitation->isLastOne()): ?>
  <p class="alert alert-warning" role="alert">Ceci n'est pas la dernière version de cette habilitation. <a href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>">Pour accèder à la dernière version cliquez ici.</a></p>
<?php endif; ?>

<?php
if (class_exists(EtablissementFindByCviView::class) && ($etablissement->cvi || in_array($etablissement->famille, [EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR, EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR, EtablissementFamilles::FAMILLE_PRODUCTEUR, EtablissementFamilles::FAMILLE_COOPERATIVE]))):
$e = EtablissementFindByCviView::getInstance()->findByCvi($etablissement->cvi);
if($etablissement->cvi && count($e) > 1):
?>
<p class="alert alert-danger" role="alert">Le CVI de l'opérateur est attribué à plusieurs établissement : <a href="<?php echo url_for('compte_search', array('q' => $etablissement->cvi, 'contacts_all' => 1, 'tags' => 'automatique:etablissement')); ?>">consulter les établissements</a>.</p>
<?php elseif (!$etablissement->cvi && (!in_array($etablissement->famille, [EtablissementFamilles::FAMILLE_PRODUCTEUR]) || !$etablissement->ppm)) : ?>
    <p class="alert alert-danger" role="alert">CVI ou PPM absent.</p>
<?php endif; ?>
<?php endif; ?>

<?php include_partial('habilitation/habilitation', array('habilitation' => $habilitation, 'editForm' => isset($editForm) ? $editForm : null, 'public' => !$sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION), 'has_ajoutForm' => isset($ajoutForm))); ?>

    <?php if ($sf_user->isAdmin() && ($habilitation->getProduits()->getRawValue())): ?>
        <div class="text-right">
            <div class="btn-group">
                <?php if($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) && count(HabilitationClient::getInstance()->getDemandes($filtre)) && HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>
                    <a class="btn btn-sm btn-default" href="<?php echo url_for('habilitation_demande_creation', $etablissement) ?>"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Saisie d’une demande</a>
                <?php endif; ?>
                <button class="btn btn-sm btn-default" id="editHabilitation" type="button"><span class="glyphicon glyphicon-edit"></span>&nbsp;&nbsp;Éditer l'habilitation</button>
            </div>
        </div>
    <?php endif; ?>


    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) && HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>
    <h3>Demandes en cours <small><a id="voir_toutes_les_demandes" href="javascript:void(0)">(voir tout)</a></small></h3>
    <table id="tableaux_des_demandes" class="table table-condensed table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Demande</th>
                <th>Date</th>
                <th>Statut</th>
                <th class="col-xs-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habilitation->getDemandesSortedOldToRecent() as $d): ?>
            <tr class="<?php if(!$d->isOuvert()): ?>hidden tohide<?php endif; ?> <?php if(!$d->isOuvert()): ?>transparence-sm<?php endif; ?>">
                <td><?php echo $d->getDemandeLibelle() ?></td>
                <td><?php echo $d->getLibelle() ?> <?php if($d->commentaire): ?><span class="text-muted">(<?php echo $d->commentaire; ?>)</span><?php endif; ?></td>
                <td><?php echo Date::francizeDate($d->date); ?></td>
                <td><?php echo $d->getStatutLibelle() ?></td>
                <td class="text-center">
                    <?php if($habilitation->isLastOne()): ?>
                    <?php if($sf_user->hasHabilitation() && (!$filtre || preg_match("/".$filtre."/i", $d->getStatut()))): ?>
                        <a href="<?php echo url_for('habilitation_demande_edition', array('sf_subject' => $etablissement, 'demande' => $d->getKey())) ?>">Voir&nbsp;/&nbsp;Modifier</a>
                    <?php else: ?>
                        <a href="<?php echo url_for('habilitation_demande_visualisation', array('sf_subject' => $etablissement, 'demande' => $d->getKey())) ?>">Voir</a>
                    <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if ($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION)): ?>
    <h3>Historique</h3>
    <table class="table table-condensed table-bordered" id="table-history">
      <thead>
        <tr>
          <th class="col-xs-1">Date</th>
          <th class="col-xs-1">Type</th>
          <th class="col-xs-1">Auteur</th>
          <th class="col-xs-7">Description de la modification</th>
          <th class="col-xs-1">Statut</th>
          <th class="col-xs-1">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php $cpt = 0;
        foreach ($habilitation->getFullHistoriqueReverse() as $historiqueDoc): ?>
          <tr class="<?php echo ($cpt % 2) ? "" : "table_td_zebra"; ?>">
            <td><?php echo Date::francizeDate($historiqueDoc->date); ?></td>
            <td><?php if(preg_match('/demande/', $historiqueDoc->iddoc)): ?>Demande<?php else: ?>Habilitation<?php endif; ?></td>
            <td><?php echo $historiqueDoc->auteur; ?></td>
            <td><?php echo preg_replace('/"([^"]+)"/', '<code>\1</code>', $historiqueDoc->getRawValue()->description); ?><?php if($historiqueDoc->commentaire): ?> <small class="text-muted">(<?php echo $historiqueDoc->commentaire; ?>)</small><?php endif ?>
            </td>
            <td><?php if(isset($historiqueDoc->statut) && $historiqueDoc->statut): ?><?php echo HabilitationClient::getInstance()->getLibelleStatut($historiqueDoc->statut); ?> <?php endif ?></td>
            <td class="text-center">
                <?php if ($historiqueDoc->iddoc != $habilitation->_id): ?>
                <a href="<?php echo url_for('habilitation_visualisation', array('id' => preg_replace("/:.+/", "", $historiqueDoc->iddoc))); ?>">Voir</a>
                <?php endif; ?>
            </td>
          </tr>
          <?php $cpt++;?>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>

<?php if(isset($editForm)): ?>
<form role="form" class="ajaxForm" action="<?php echo url_for("habilitation_edition", $habilitation) ?>" method="post">
    <?php
    echo $editForm->renderHiddenFields();
    echo $editForm->renderGlobalErrors();

    foreach ($habilitation->getProduits() as $key => $produitAppellation):
      foreach ($produitAppellation->activites as $keyActivite => $activite):
        include_partial('habilitation/popupEditionForm', array('url' => url_for('habilitation_edition', $habilitation), 'editForm' => $editForm,'idPopup' => 'editForm_'.$activite->getHashForKey(), 'produitCepage' => $produitAppellation, 'details' => $activite));
      endforeach;
    endforeach;
    ?>
</form>
<?php endif; ?>

<?php if(isset($ajoutForm)): ?>
<?php include_partial('habilitation/popupAjoutForm', array('url' => url_for('habilitation_ajout', $etablissement), 'form' => $ajoutForm)); ?>
<?php endif; ?>

<?php if(isset($formDemandeCreation)): ?>
<?php include_partial('habilitation/demandeCreationForm', array('form' => $formDemandeCreation, 'etablissement' => $etablissement)); ?>
<?php endif; ?>

<?php if(isset($formDemandeGlobale)): ?>
<?php include_partial('habilitation/demandeGlobaleForm', array('form' => $formDemandeGlobale, 'etablissement' => $etablissement)); ?>
<?php endif; ?>

<?php if(isset($formDemandeEdition)): ?>
<?php include_partial('habilitation/demandeEditionForm', array('form' => $formDemandeEdition, 'etablissement' => $etablissement, 'demande' => $demande, 'urlRetour' => $urlRetour)); ?>
<?php endif; ?>
