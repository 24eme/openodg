<?php use_helper('Date'); ?>

<div class="well mb-5">
    <?php include_partial('etablissement/blocDeclaration', ['etablissement' => $controle->getEtablissementObject()]); ?>
</div>

<h3>Manquements du <?php echo $controle->_id?> </h3>

<table class="table table-bordered table-striped">
    <thead>
    <tr>
        <th class="col-1">Point de contrôle</th>
        <th class="col-3">Manquement</th>
        <th class="col-4">Parcelles concernées - Observations</th>
        <th class="col-1">Délais</th>
        <th class="col-1"></th>
    </tr>
    </thead>
    <tbody>
<?php foreach ($sorted_manquements as $numRtm => $manquement): ?>
    <tr>
        <td><?php echo $manquement->libelle_point_de_controle ?></td>
        <td><?php echo $manquement->libelle_manquement ?></td>
        <td><?php echo $manquement->observations ?></td>
        <td><?php echo $manquement->delais ?></td>
        <td class="text-center">
            <?php if (!$manquement->cloture_date): ?>
                <div class="dropdown">
                    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        Traiter
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                        <li><a href="<?php echo url_for('controle_cloture_manquement', ['id_controle' => $controle->_id, 'id_manquement' => $numRtm, 'type' => ControleClient::CONTROLE_CLOTURE_OC]); ?>">Enregistrer une Transmission à l'OC</a></li>
                        <li><a href="<?php echo url_for('controle_cloture_manquement', ['id_controle' => $controle->_id, 'id_manquement' => $numRtm, 'type' => ControleClient::CONTROLE_CLOTURE_LEVER]); ?>">Lever le manquements</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a class="btn btn-sm btn-primary" disabled><?php if($manquement->cloture_type == ControleClient::CONTROLE_CLOTURE_OC): ?>Transmis à l'OC<?php else: ?>Levé<?php endif; ?> le <?php echo format_date($manquement->cloture_date, "dd/MM/yyyy", "fr_FR"); ?></a>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
        </tbody>
    </table>
