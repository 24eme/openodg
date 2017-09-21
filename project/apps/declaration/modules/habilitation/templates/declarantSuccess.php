<?php use_helper('Date') ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('degustation'); ?>">Habilitation</a></li>
  <li class="active"><a href=""><?php echo $etablissement->getNom() ?> (<?php echo $etablissement->getIdentifiant() ?>)</a></li>
</ol>

<div class="row">
    <div class="col-xs-12">
        <h3>Liste de l'historique des habilitations</h3>
        <?php if(count($habilitationsHistory)): ?>
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr>
                    <th class="col-xs-2">Date</th>
                    <th class="col-xs-3">Produit</th>
                    <th class="col-xs-4">Activité</th>
                    <th class="col-xs-2">Commentaire</th>
                    <th class="col-xs-1">Statut</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($habilitationsHistory as $habilitation): ?>
                    <tr>
                        <td><a href="<?php echo url_for("degustation_visualisation", array('id' => str_replace("DEGUSTATION-".$habilitation->identifiant."-", "TOURNEE-", $degustation->_id))) ?>"><?php echo format_date($habilitation->date_degustation, "P", "fr_FR") ?></a></td>
                        <td><?php echo $prelevement->libelle; ?> <?php echo $habilitation->millesime ?><small class="text-muted"><br /><?php echo $habilitation->libelle_produit ?>  </small></td>
                        <td>
                    </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <em>Aucune habilitation actuellement pour cet opérateur</em>
        <?php endif; ?>
    </div>
</div>
<?php if(!count($habilitationsHistory)): ?>
<div class="row" style="padding-top:20px;">
    <div class="col-xs-12">
      <a class="btn btn-default" href="<?php echo url_for('habilitation_create', array('sf_subject' => $etablissement)) ?>">Démarrer une habilitation</a>
    </div>
</div>
<?php endif; ?>
