<?php use_helper('Date'); ?>

<table style="margin-top: 30px;" class="table table-condensed table-bordered" id="table-habilitation">
    <thead>
        <tr>
            <th class="col-xs-3">Produits</th>
            <th class="col-xs-2">Activités</th>
            <th class="text-center col-xs-2">Statut de l'habilitation</th>
            <th class="text-center col-xs-1">Date</th>
            <?php if(!isset($public) || !$public): ?>
            <th class="text-center col-xs-3">Commentaire</th>
            <th class="text-center"><span id="ouvert" class="open-button glyphicon glyphicon-eye-open" style="cursor: pointer;" ></span></th>
            <?php endif; ?>
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
              if ($habilitationsNode->isHabiliteExterieur()) {
                  $color = 'bg-info';
              }
            ?>
            <tr data-hide="<?php echo ($nbActivites)? '' : '1'; ?>" <?php echo ($nbActivites)? '' : 'style="display:none;"'; ?> >
              <?php if($first): ?>
                <td data-hide="<?php echo (!$first)? $tdDisplayed : ''; ?>" "<?php echo (!$first)? 'style="display:none;"' : ''; ?>" rowspan="<?php echo $produitAppellation->getNbActivites(); ?>" data-number="<?php echo $nbActivites; ?>"><strong><?php echo $produitAppellation->getLibelleComplet(); ?></strong></td>
              <?php endif; $first = false; ?>
                  <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="<?php echo $color; ?>" >
                    <strong><?php echo HabilitationClient::getInstance()->getLibelleActivite($keyActivite); ?></strong>
                    <?php if($habilitationsNode->hasSite()): ?>
                    <span class="text-muted"><?php echo $habilitationsNode->site; ?></span>
                    <?php endif;?>
                  </td>
                  <td data-hide="<?php echo $tdDisplayed ?>" <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" <?php $rowDisplayed ?> ><strong><?php echo ($habilitationsNode->statut)? HabilitationClient::$statuts_libelles[$habilitationsNode->statut] : ''; ?></strong></td>
                  <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->statut)? format_date($habilitationsNode->date, "dd/MM/yyyy", "fr_FR") : ''; ?></td>
                  <?php if(!isset($public) || !$public): ?>
                  <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" ><?php echo ($habilitationsNode->commentaire); ?></td>
                  <td data-hide="<?php echo $tdDisplayed ?>"  <?php echo $tdHide ?> class="text-center <?php echo $color; ?>" >
                    <?php if(isset($editForm)): ?>
                    <a class="btn btn-xs btn-default <?php if(HabilitationConfiguration::getInstance()->isSuiviParDemande()): ?>invisible<?php endif; ?>" data-toggle="modal" data-target="#editForm_<?php echo $habilitationsNode->getHashForKey(); ?>" type="button"><span class="glyphicon glyphicon-pencil"></span></a>
                    <?php endif; ?>
                  </td>
                  <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
        <?php if(!isset($public) && !$public && $has_ajoutForm): ?>
            <tr>
                <td colspan="6" class="text-right">
                    <button class="btn btn-sm btn-default pull-right" data-toggle="modal" data-target="#popupAjoutProduitForm" type="button"><span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Ajouter un produit</button>
                </td>
            </tr>
        <?php endif;?>
    </tbody>
</table>
