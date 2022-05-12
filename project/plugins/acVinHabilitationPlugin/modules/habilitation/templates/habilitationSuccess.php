<style>
  .table thead > tr > th,
  .table tbody > tr > td {
    width: auto;
  }
</style>
<?php use_helper('Date'); ?>
<?php $habilitation = $habilitations[0]; ?>
<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation )); ?>
<div class="page-header no-border">
    <h2>Habilitations<?php if(!$habilitation->isLastOne()): ?> au <?php echo Date::francizeDate($habilitation->getDate()); ?><?php endif; ?></h2>
</div>

<?php if(isset($form)): ?>
<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('habilitation_etablissement_selection'),  'noautofocus' => true)); ?>
    </div>
</div>
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

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
  <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>
<?php if(!$habilitation->isLastOne()): ?>
  <p class="alert alert-warning" role="alert">Ceci n'est pas la dernière version de cette habilitation. <a href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>">Pour accèder à la dernière version cliquez ici.</a></p>
<?php endif; ?>

<?php foreach($habilitations as $hid => $habilitation): $etablissement = $habilitation->getEtablissementObject()->getRawValue(); ?>
    <?php if (!$hid): ?>
        <h3>Chais principal</h3>
    <?php else: ?>
        <h3>Chais secondaire - <?php echo $habilitation->declarant->nom; ?></h3>
        <h4 class="text-muted"><?php echo $habilitation->declarant->adresse; ?> <?php echo $habilitation->declarant->code_postal; ?> <?php echo $habilitation->declarant->commune; ?></h4>
    <?php endif; ?>
    <table style="margin-top: 30px;" class="table table-condensed table-bordered" id="table-habilitation">
        <thead>
            <tr>
                <th class="col-xs-3">Produits</th>
                <th class="col-xs-2">Activités</th>
                <th class="text-center col-xs-2">Statut</th>
                <th class="text-center col-xs-1">Date</th>
                <th class="text-center col-xs-3">Commentaire</th>
                <th class="text-center"><span id="ouvert" class="open-button glyphicon glyphicon-eye-open" style="cursor: pointer;" ></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habilitation->getProduits() as $key => $produitAppellation):
              $first = true;
              $hasHabilitations = $produitAppellation->hasHabilitations();
              $nbActivites = $produitAppellation->getNbActivitesSaisies();
                foreach ($produitAppellation->activites as $keyActivite => $habilitationsNode):
                  $tdDisplayed = (!$habilitationsNode->hasStatut())? '1' :'';
                  $tdHide = (!$habilitationsNode->hasStatut())? 'style="display:none;"' :'';
                  $color = ($habilitationsNode->isHabilite())?  'bg-success' :'';
                  $color = (!$color && $habilitationsNode->isWrongHabilitation())? 'bg-danger' : $color;
                ?>
                <tr data-hide="<?php echo ($nbActivites)? '' : '1'; ?>" <?php echo ($nbActivites)? '' : 'style="display:none;"'; ?> >
                  <?php if($first): ?>
                    <td data-hide="<?php echo (!$first)? $tdDisplayed : ''; ?>" "<?php echo (!$first)? 'style="display:none;"' : ''; ?>" rowspan="<?php echo $produitAppellation->getNbActivites(); ?>" data-number="<?php echo $nbActivites; ?>"><strong><?php echo $produitAppellation->getLibelleComplet(); ?></strong></td>
                  <?php endif; $first = false; ?>
                      <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="<?php echo $color; ?>" ><strong><?php echo HabilitationClient::getInstance()->getLibelleActivite($keyActivite); ?></strong></td>
                      <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" <?php $rowDisplayed ?> ><strong><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut] : ''; ?></strong></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->statut)? format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR") : ''; ?></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->commentaire); ?></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" >
                        <?php if(isset($editForm)): ?>
                        <a class="btn btn-xs btn-default <?php if(HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>invisible<?php endif; ?>" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                        <?php endif; ?>
                      </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ($sf_user->hasCredential(myUser::CREDENTIAL_HABILITATION) && count(HabilitationClient::getInstance()->getDemandes($filtre)) && HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>
        <div class="text-right">
        <a class="btn btn-sm btn-default" href="<?php echo url_for('habilitation_demande_creation', array('identifiant' => $habilitation->identifiant) ) ?>"><span class="glyphicon glyphicon-pencil"></span>&nbsp;&nbsp;Saisie d’une demande</a>
        </div>
    <?php endif; ?>

    <?php if ($sf_user->isAdmin() && isset($ajoutForm) && $ajoutForm->hasProduits()): ?>
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-sm btn-default pull-right" data-toggle="modal" data-target="#popupAjoutProduitForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit</button>
            </div>
        </div>
    <?php endif; ?>
<?php endforeach; ?>
<?php $habilitation = $habilitations[0]; ?>
    <?php if(HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>
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
                    <?php if($sf_user->hasCredential(AppUser::CREDENTIAL_HABILITATION) && (!$filtre || preg_match("/".$filtre."/i", $d->getStatut()))): ?>
                        <a href="<?php echo url_for('habilitation_demande_edition', array('sf_subject' => $etablissement, 'demande' => $d->getKey())) ?>">Voir&nbsp;/&nbsp;Modifier</a></td>
                    <?php else: ?>
                        <a href="<?php echo url_for('habilitation_demande_visualisation', array('sf_subject' => $etablissement, 'demande' => $d->getKey())) ?>">Voir</a></td>
                    <?php endif; ?>
                    <?php endif; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

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
      </div>
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
            <td class="text-center"><a href="<?php echo url_for('habilitation_visualisation', array('id' => preg_replace("/:.+/", "", $historiqueDoc->iddoc))); ?>">Voir</a></td>
          </tr>
          <?php $cpt++;?>
        <?php endforeach; ?>
      </tbody>
    </table>

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
<?php include_partial('habilitation/popupAjoutForm', array('url' => url_for('habilitation_ajout', $habilitation->getEtablissementChais()), 'form' => $ajoutForm)); ?>
<?php endif; ?>

<?php if(isset($formDemandeCreation)): ?>
<?php include_partial('habilitation/demandeCreationForm', array('form' => $formDemandeCreation)); ?>
<?php endif; ?>

<?php if(isset($formDemandeGlobale)): ?>
<?php include_partial('habilitation/demandeGlobaleForm', array('form' => $formDemandeGlobale)); ?>
<?php endif; ?>

<?php if(isset($formDemandeEdition)): ?>
<?php include_partial('habilitation/demandeEditionForm', array('form' => $formDemandeEdition, 'demande' => $demande, 'urlRetour' => $urlRetour)); ?>
<?php endif; ?>
