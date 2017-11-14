<?php use_helper('Date'); ?>
<?php use_helper('Compte'); ?>
<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation ));
  $etablissement = $habilitation->getEtablissementObject();
  $compte = $habilitation->getEtablissementObject()->getMasterCompte();
 ?>
<div class="page-header no-border">
    <h2>Habilitations<?php if(!$habilitation->isLastOne()): ?> au <?php echo Date::francizeDate($habilitation->getDate()); ?><?php endif; ?></h2>
</div>
<div class="row">
  <div class="col-xs-12">

<div class="panel panel-default">
  <div class="panel-body">
    <h4><span class="glyphicon glyphicon-home"></span> <?php  echo $etablissement->getNom()." - ".$etablissement->getIdentifiant(); ?><?php  if($etablissement->getCvi()){ echo ' - CVI : '.$etablissement->getCvi(); } ?><?php  if($etablissement->getSiret()){ echo ' - SIRET : '.formatSIRET($etablissement->getSiret()); } ?></h4>
    <div class="row">
        <div class="col-xs-12">
          <div class="row">
              <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                Adresse&nbsp;:
              </div>
              <div style="margin-bottom: 5px" class="col-xs-9">
                <address style="margin-bottom: 0;">
                  <?php echo $compte->getAdresse(); ?><?php echo ($compte->getAdresseComplementaire())? " ".$compte->getAdresseComplementaire() : ''; ?>
                  <span><?php echo ' '.$compte->getCodePostal(); ?></span><?php echo ' '.$compte->getCommune(); ?><small class="text-muted">(<?php echo ' '.$compte->getPays(); ?>)</small>
                </address>
              </div>
            </div>
        </div>
        <div class="col-xs-12">
          <div class="row">
              <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                Contact&nbsp;:
              </div>
              <div style="margin-bottom: 5px" class="col-xs-9">
                  <?php echo ($compte->getEmail())? "<a href='mailto:".$compte->getEmail()."'>".$compte->getEmail()."</a> / " : ''; ?>
                  <?php echo ($compte->getTelephoneBureau())? "<a href='callto:".$compte->getTelephoneBureau()."'>".$compte->getTelephoneBureau()."</a> / " : ''; ?>
                  <?php echo ($compte->getTelephoneMobile())? "<a href='callto:".$compte->getTelephoneMobile()."'>".$compte->getTelephoneMobile()."</a>" : ''; ?>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>

  </div>
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
    <table class="table table-condensed table-bordered" id="table-habilitation">
        <thead>
            <tr>
                <th class="col-xs-2">Produits</th>
                <th class="col-xs-1">Activités</th>
                <th class="text-center col-xs-1">Statut</th>
                <th class="text-center col-xs-1">Date</th>
                <th class="text-center col-xs-3">Commentaire</th>
                <th class="text-center col-xs-1"><span id="ouvert" class="open-button glyphicon glyphicon-eye-open" style="cursor: pointer;" ></span></th>
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
                      <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="<?php echo $color; ?>" ><strong><?php echo HabilitationClient::$activites_libelles[$keyActivite]; ?></strong></td>
                      <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" <?php $rowDisplayed ?> ><strong><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut] : ''; ?></strong></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->statut)? format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR") : ''; ?></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->commentaire); ?></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?> col-xs-1" >
                        <?php if(isset($editForm)): ?>
                        <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                        <?php endif; ?>
                      </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if (isset($ajoutForm) && $ajoutForm->hasProduits()): ?>
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-sm btn-default pull-right" data-toggle="modal" data-target="#popupAjoutProduitForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit</button>
            </div>
        </div>
    <?php endif; ?>

    <h3>Historique</h3>
    <table class="table table-condensed table-bordered" id="table-history">
      <thead>
        <tr>
          <th class="col-xs-1">Date</th>
          <th class="col-xs-1" style="border-right: none;"></th>
          <th class="col-xs-9" style="border-left: none;">Description de la modification</th>
          <th class="col-xs-1">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <?php
        foreach ($habilitation->getFullHistoriqueReverse() as $historiqueDoc): ?>
          <tr>
            <td><?php echo Date::francizeDate($historiqueDoc->date); ?></<td>
            <td class="text-right text-muted" style="border-right: none;"><?php echo $historiqueDoc->auteur; ?> </td>
            <td style="border-left: none;"><?php echo $historiqueDoc->description; ?><?php if($historiqueDoc->commentaire): ?> <span class="text-muted"><?php echo '('.$historiqueDoc->commentaire.')'; ?></span><?php endif ?>
            </td>
            <td class="text-center"><a href="<?php echo url_for('habilitation_visualisation', array('id' => $historiqueDoc->iddoc)); ?>">Voir</a></tr>
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
<?php include_partial('habilitation/popupAjoutForm', array('url' => url_for('habilitation_ajout', $etablissement), 'form' => $ajoutForm)); ?>
<?php endif; ?>
