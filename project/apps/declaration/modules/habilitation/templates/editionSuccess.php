<?php use_helper('Date'); ?>
<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation )); ?>
<?php  $nbActivites = count(HabilitationClient::$activites_libelles); ?>
<div class="page-header no-border">
    <h2>Habilitations</h2>
</div>

<?php if ($sf_user->hasFlash('notice')): ?>
  <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
<?php endif; ?>
<?php if ($sf_user->hasFlash('erreur')): ?>
  <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
<?php endif; ?>
    <p>Veuillez saisir les données par appellations</p>


    <table class="table table-condensed table-bordered" id="table-habilitation">
        <thead>
            <tr>
                <th class="col-xs-3">Produits</th>
                <th class="col-xs-3">Activités</th>
                <th class="text-center col-xs-2">Statut</th>
                <th class="text-center col-xs-3">Commentaire</th>
                <th class="text-center col-xs-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habilitation->getProduits() as $key => $produitAppellation):
              $first = true;
              $hasHabilitations = $produitAppellation->hasHabilitations();
                foreach ($produitAppellation->activites as $keyActivite => $habilitationsNode):
                ?>
                <tr <?php echo ($hasHabilitations)? '' : 'style="display:none;"' ?> class="tr-open " data-id="<?php echo $produitAppellation->getHash(); ?>" >
                  <?php if($first): ?>
                    <td rowspan="<?php echo $nbActivites; ?>"><strong><?php echo $produitAppellation->getLibelleComplet(); ?></strong><span data-id="<?php echo $produitAppellation->getHash(); ?>" class="close-button pull-right glyphicon glyphicon-chevron-down" style="cursor: pointer;" ></span></td>
                  <?php endif; $first = false; ?>
                      <td class="<?php echo ($habilitationsNode->isHabilite())? "table-success" : ""; ?>" ><strong><?php echo HabilitationClient::$activites_libelles[$keyActivite]; ?></strong></td>
                      <td class="text-center <?php echo ($habilitationsNode->isHabilite())? "table-success" : ""; ?>" ><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut]." <br/>".format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR") : ''; ?></td>
                      <td class="text-center <?php echo ($habilitationsNode->isHabilite())? "table-success" : ""; ?>" ><?php echo ($habilitationsNode->commentaire); ?></td>
                      <td class="text-center <?php echo ($habilitationsNode->isHabilite())? "table-success" : ""; ?> col-xs-1">
                        <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                      </td>
                </tr>
              <?php endforeach; ?>
              <tr <?php echo (!$hasHabilitations)? '' : 'style="display:none;"' ?> data-id="<?php echo $produitAppellation->getHash(); ?>" class="tr-collapsed" >
                  <td><strong><?php echo $produitAppellation->getLibelleComplet(); ?></strong><span data-id="<?php echo $produitAppellation->getHash(); ?>" class="open-button pull-right glyphicon glyphicon-chevron-right" style="cursor: pointer;" ></span></td>
                  <td colspan="4" class="text-center" ><spanstyle="font-style: italic">Aucune habilitations</span></td>
              </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($ajoutForm->hasProduits()): ?>
        <div class="row">
            <div class="col-xs-12">
                <a class="btn btn-sm btn-default ajax pull-left" href="<?php echo url_for('habilitation_declarant', $habilitation->getEtablissementObject()); ?>" ><span class="glyphicon glyphicon-arrow-left"></span>&nbsp;&nbsp;Retour</a>
                <button class="btn btn-sm btn-default ajax pull-right" data-toggle="modal" data-target="#popupAjoutProduitForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit / cépage</button>
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
