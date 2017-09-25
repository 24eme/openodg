<?php use_helper('Date'); ?>
<?php include_partial('habilitation/breadcrumb', array('habilitation' => $habilitation )); ?>
<?php include_partial('habilitation/step', array('step' => 'revendication', 'habilitation' => $habilitation)) ?>
<?php  $nbActivites = count(HabilitationClient::$activites_libelles); ?>
<div class="page-header no-border">
    <h2>Habilitations</h2>
</div>

    <p>Veuillez saisir les données par cépage</p>

    <?php if ($sf_user->hasFlash('notice')): ?>
        <div class="alert alert-success" role="alert"><?php echo $sf_user->getFlash('notice') ?></div>
    <?php endif; ?>
    <?php if ($sf_user->hasFlash('erreur')): ?>
        <p class="alert alert-danger" role="alert"><?php echo $sf_user->getFlash('erreur') ?></p>
    <?php endif; ?>

    <table class="table table-condensed table-bordered" id="table-revendication">
        <thead>
            <tr>
                <th class="col-xs-3">Produits</th>
                <th class="col-xs-5">Activités</th>
                <th class="text-center col-xs-2">Statut</th>
                <th class="text-center col-xs-2">Date</th>
                <th class="text-center col-xs-1"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($habilitation->getProduits() as $key => $produitCepage):
              $first = true;
                foreach ($produitCepage->getDetails() as $keyDetails => $habilitationsNode):
                ?>
                <tr>
                  <?php if($first): ?><td rowspan="<?php echo $nbActivites; ?>"><strong><?php echo $produitCepage->getLibelleComplet(); ?></strong></td><?php endif; $first = false; ?>
                      <td><?php echo HabilitationClient::$activites_libelles[$keyDetails]; ?></td>
                      <td class="text-center" ><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut] : ''; ?></td>
                      <td class="text-center" ><?php echo format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR"); ?></td>
                      <td class="text-center col-xs-1">
                        <a class="btn btn-sm btn-default" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                      </td>
                </tr>
              <?php endforeach; ?>
            <?php endforeach; ?>
            <?php if ($ajoutForm->hasProduits()): ?>
                <tr>
                    <td>
                        <button class="btn btn-sm btn-warning ajax" data-toggle="modal" data-target="#popupAjoutProduitForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit / cépage</button>
                    </td>
                    <td></td>
                    <td></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
<form role="form" class="ajaxForm" action="<?php echo url_for("habilitation_edition", $habilitation) ?>" method="post">
    <?php
    echo $editForm->renderHiddenFields();
    echo $editForm->renderGlobalErrors();

    foreach ($habilitation->getProduits() as $key => $produitCepage):
      foreach ($produitCepage->getDetails() as $keyDetails => $habilitationsNode):
        include_partial('habilitation/popupEditionForm', array('url' => url_for('habilitation_edition', $habilitation), 'editForm' => $editForm,'idPopup' => 'editForm_'.$habilitationsNode->getHashForKey(), 'produitCepage' => $produitCepage, 'details' => $habilitationsNode));
      endforeach;
    endforeach;
    ?>
</form>

<?php include_partial('habilitation/popupAjoutForm', array('url' => url_for('habilitation_ajout', $habilitation), 'form' => $ajoutForm)); ?>
