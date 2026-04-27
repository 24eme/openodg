<?php use_helper('Date'); ?>
<?php include_partial('global/flash'); ?>

<ol class="breadcrumb">
  <li><a href="<?php echo url_for('controle_index'); ?>">Contrôles</a></li>
  <li class="active"><a href="">Liste des manquements à gérer</a></li>
</ol>

<h2>Manquements à gérer</h2>
<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-1">Date de notification</th>
        <th class="col-4">Opérateur</th>
        <th class="col-1">Nb manquements</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($sorted_controles as $controle): ?>
    <tr>
        <td><?php echo format_date($controle->notification_date, "dd/MM/yyyy", "fr_FR"); ?></td>
        <td>
            <a href="<?php echo url_for('controle_operateur', ['identifiant' => $controle->identifiant]); ?>"><?php echo $controle->declarant->nom; ?></a>
             <span class="text-muted"> -
                 <?php echo $controle->declarant->commune; ?> -
                <?php echo $controle->identifiant; ?> -
                <?php echo $controle->declarant->cvi; ?>
            </span>
             <br/>
            <?php $has_secteur = false; ?>
            <?php if ($controle->secteur): ?>
                Sect. : <?php echo $controle->secteur; ?>
            <?php $has_secteur = true; endif; ?>
            <?php if ($controle->hasLiaisons()): ?>
                <?php if ($has_secteur) : ?> - <?php endif; ?>
                <span class="text-muted">
                Caves :
                <span><?php echo $controle->getLiaisonsLibellesString(); ?></span>
                </span>
            <?php endif; ?>
        </td>
        <td><?php echo count($controle->manquements); ?></td>
        <td><a href="<?php echo url_for('controle_liste_manquements_operateur', ['id_controle' => $controle->_id]); ?>" class="btn btn-sm btn-primary">Gérer les manquements</a></td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>

    <div class="row col-xs-12">
        <a href="<?php echo url_for('controle_index') ?>" class="btn btn-default"><span class="glyphicon glyphicon-chevron-left"></span> Retour</a>
    </div>
