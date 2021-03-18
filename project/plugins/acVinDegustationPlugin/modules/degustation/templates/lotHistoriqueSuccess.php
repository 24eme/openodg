<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
  <li><a href="<?php echo url_for('degustation_etablissement_list',array('id' => $etablissement->identifiant)); ?>"><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?> - <?php echo $etablissement->cvi ?>)</a></li>
  <li><a href="" class="active" >N° dossier : <?php echo $numero_dossier ?> - N° archive :<?php echo $numero_archive ?></a></li>
</ol>
<?php use_helper('Float') ?>

<div class="page-header no-border">

  <h2>Historique du lot de <?php echo $etablissement->getNom(); ?></h2>
</div>
<?php if (count($mouvements)): ?>
  <div class="row">
    <table class="table table-condensed table-striped">
      <thead>
        <th class="col-sm-2">Document</th>
        <th class="col-sm-1">N° Dossier Lot</th>
        <th class="col-sm-1">N° Archive Lot</th>
        <th class="col-sm-6">Appellation</th>
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
                <td><?php echo $mouvement->value->numero_dossier;  ?></td>
                <td><?php echo $mouvement->value->numero_archive;  ?></td>
                <td><?php echo $mouvement->value->libelle;  ?></td>
                <td><?php echo Lot::$libellesStatuts[$mouvement->value->statut];  ?><td/>

            </tr>
                <?php endforeach; ?>
            <tbody>
            </table>
          <?php endif; ?>
        </div>
