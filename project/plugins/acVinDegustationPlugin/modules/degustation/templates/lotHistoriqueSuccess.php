<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
</ol>
<?php use_helper('Float') ?>

<div class="page-header no-border">

  <h2>Historique du lot de <?php echo $etablissement->getNom(); ?></h2>
</div>
<?php if (count($mouvements)): ?>
  <div class="row">
    <table class="table table-condensed table-striped">
      <thead>
        <th class="col-sm-3">Document</th>
        <th class="col-sm-2">N° Dossier Lot</th>
        <th class="col-sm-2">N° Archive Lot</th>
        <th class="col-sm-3">Appellation</th>
        <th class="col-sm-2 text-center">Etape</th>
      </thead>
      <tbody>
        <?php foreach($mouvements as $lotKey => $mouvement): ?>
              <tr>
                <td>
                    <a href="<?php echo url_for(strtolower($mouvement->value->document_type).'_visualisation', array('id' => $mouvement->value->document_id));  ?>">
                        <?php echo $mouvement->value->document_type;  ?>
                    </a>
                </td>
                <td><?php echo $lot_dossier;  ?></td>
                <td><?php echo $lot_archive;  ?></td>
                <?php
                $doc = acCouchdbManager::getClient()->find($mouvement->value->document_id);
                $lot = $doc->get($mouvement->value->lot_hash);
                ?>

                <td><?php echo $mouvement->libelle;  ?></td>
                <td><?php echo Lot::$libellesStatuts[$mouvement->key[MouvementLotHistoryView::KEY_STATUT]];  ?><td/>

            </tr>
                <?php endforeach; ?>
            <tbody>
            </table>
          <?php endif; ?>
        </div>
