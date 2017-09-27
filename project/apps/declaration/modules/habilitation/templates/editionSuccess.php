<?php use_helper('Date'); ?>
<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation )); ?>
<div class="page-header no-border">
    <h2>Habilitations</h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
  <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>
    <table class="table table-condensed table-bordered" id="table-habilitation">
        <thead>
            <tr>
                <th class="col-xs-2">Produits</th>
                <th class="col-xs-1">Activités</th>
                <th class="text-center col-xs-1">Statut</th>
                <th class="text-center col-xs-1">Date</th>
                <th class="text-center col-xs-3">Commentaire</th>
                <th class="text-center col-xs-1"><span class="open-button glyphicon glyphicon-chevron-right" style="cursor: pointer;" ></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habilitation->getProduits() as $key => $produitAppellation):
              $first = true;
              $hasHabilitations = $produitAppellation->hasHabilitations();
              $nbActivites = $produitAppellation->nbActivites();
                foreach ($produitAppellation->activites as $keyActivite => $habilitationsNode):
                  $rowDisplayed = (!$habilitationsNode->hasStatut())? '1' :'';
                  $color = ($habilitationsNode->isHabilite())?  'bg-success' :'';
                  $color = (!$color && $habilitationsNode->isWrongHabilitation())? 'bg-danger' : $color;
                ?>
                <tr data-hide="<?php echo $rowDisplayed ?>" >
                  <?php if($first): ?>
                    <td rowspan="5" data-number="<?php echo $nbActivites; ?>"><strong><?php echo $produitAppellation->getLibelleComplet(); ?></strong></td>
                  <?php endif; $first = false; ?>
                      <td class="<?php echo $color; ?>" ><strong><?php echo HabilitationClient::$activites_libelles[$keyActivite]; ?></strong></td>
                      <td class="text-center <?php echo $color; ?>" <?php $rowDisplayed ?> ><strong><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut] : ''; ?></strong></td>
                      <td class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->statut)? format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR") : ''; ?></td>
                      <td class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->commentaire); ?></td>
                      <td class="text-center <?php echo $color; ?> col-xs-1" >
                        <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                      </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h3>Historique</h3>
    <table class="table table-condensed table-bordered" id="table-history">
      <thead>
        <tr>
          <th class="col-xs-1">Date</th>
          <th class="col-xs-10">Description de la modification</th>
          <th class="col-xs-1">&nbsp;</th>
        </tr>
      </thead>
      <tbody>
        <tr><td>12/08/2017</<td><td>Côtes du Rhone : statut "vinificateur" changé de "Habilité" à "Retrait" (MON BEAU COMMENTAIRE)</td><td class="text-center"><a href="#">Voir</a></tr>
        <tr><td>12/05/2017</<td><td>Côtes du Rhone : statut "vinificateur" changé de "demande INAO" à "Habilité" (MON BEAU COMMENTAIRE)</td><td class="text-center"><a href="#">Voir</a></tr>
        <tr><td>12/03/2017</<td><td>Côtes du Rhone : statut "vinificateur" changé de "demande ODG" à "demande INAO" (MON BEAU COMMENTAIRE)</td><td class="text-center"><a href="#">Voir</a></tr>
        <tr><td>12/01/2017</<td><td>Côte Rotie : statut "vinificateur" activé à "demande ODG"</td><td class="text-center"><a href="#">Voir</a></tr>
        <tr><td>12/01/2017</<td><td>Création du produit Côte Rotie</td><td class="text-center"><a href="#">Voir</a></tr>
      </tbody>
    </table>
    <?php if ($ajoutForm->hasProduits()): ?>
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-sm btn-default ajax pull-right" data-toggle="modal" data-target="#popupAjoutProduitForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit</button>
            </div>
        </div>
    <?php endif; ?>

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
