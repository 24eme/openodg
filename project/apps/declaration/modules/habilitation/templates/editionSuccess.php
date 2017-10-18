<?php use_helper('Date'); ?>
<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation )); ?>
<div class="page-header no-border">
    <h2>Habilitations<?php if(!$habilitation->isLastOne()): ?> au <?php echo Date::francizeDate($habilitation->getDate()); ?><?php endif; ?></h2>
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
                <th class="text-center col-xs-1"><span class="open-button glyphicon glyphicon-eye-open" style="cursor: pointer;" ></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habilitation->getProduits() as $key => $produitAppellation):
              $first = true;
              $hasHabilitations = $produitAppellation->hasHabilitations();
              $nbActivites = $produitAppellation->nbActivites();
                foreach ($produitAppellation->activites as $keyActivite => $habilitationsNode):
                  $tdDisplayed = (!$habilitationsNode->hasStatut())? '1' :'';
                  $tdHide = (!$habilitationsNode->hasStatut())? 'style="display:none;"' :'';
                  $color = ($habilitationsNode->isHabilite())?  'bg-success' :'';
                  $color = (!$color && $habilitationsNode->isWrongHabilitation())? 'bg-danger' : $color;
                ?>
                <tr data-hide="<?php echo ($nbActivites)? '' : '1'; ?>" <?php echo ($nbActivites)? '' : 'style="display:none;"'; ?> >
                  <?php if($first): ?>
                    <td data-hide="<?php echo (!$first)? $tdDisplayed : ''; ?>" "<?php echo (!$first)? 'style="display:none;"' : ''; ?>" rowspan="5" data-number="<?php echo $nbActivites; ?>"><strong><?php echo $produitAppellation->getLibelleComplet(); ?></strong></td>
                  <?php endif; $first = false; ?>
                      <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="<?php echo $color; ?>" ><strong><?php echo HabilitationClient::$activites_libelles[$keyActivite]; ?></strong></td>
                      <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" <?php $rowDisplayed ?> ><strong><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut] : ''; ?></strong></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->statut)? format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR") : ''; ?></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->commentaire); ?></td>
                      <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?> col-xs-1" >
                        <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                      </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($ajoutForm->hasProduits()): ?>
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
            <td class="text-center"><a href="<?php echo url_for('habilitation_edition', array('id' => $historiqueDoc->iddoc)); ?>">Voir</a></tr>
        <?php endforeach; ?>
      </tbody>
    </table>


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

<?php include_partial('habilitation/popupAjoutForm', array('url' => url_for('habilitation_ajout', $habilitation), 'form' => $ajoutForm)); ?>
