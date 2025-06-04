<?php use_helper('Date'); ?>
<?php use_helper('Date'); ?>
<?php use_helper('Float'); ?>

<ol class="breadcrumb">
    <li><a href="<?php echo url_for('facturation') ?>">Facturation</a></li>
    <li>Facturation en attente</li>
</ol>

<h2>Mouvements de facturation en attente</h2>
<h4 class="page-header"><?php echo count($mouvements) ?> opérateurs avec des mouvements en attente</h4>

<?php include_partial('global/flash'); ?>

    <table class="table table-striped table-condensed">
      <thead>
        <tr><th colspan="7">Établissements <a href="<?php echo url_for('facturation_en_attente', array('details' => 1)) ?>" class="btn btn-xs btn-link pull-right"><span class="glyphicon glyphicon-eye-open"></span > Voir tous les mouvements</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($mouvements->getRawValue() as $id => $mouvements): ?>
            <?php $etablissement = EtablissementClient::getInstance()->retrieveById($id, acCouchdbClient::HYDRATE_JSON); ?>
            <?php if(!$etablissement): ?>
                <?php $compte = CompteClient::getInstance()->findByIdentifiant($id, acCouchdbClient::HYDRATE_JSON); ?>
            <?php endif; ?>
            <tr>
                <td colspan="4" title="<?php echo $id ?>"><?php if($etablissement): ?><?php echo $etablissement->raison_sociale ?> <small class="text-muted"><?php echo $etablissement->famille; ?></small><?php endif ?><?php if(isset($compte)): ?><?php echo $compte->nom_a_afficher ?> (<?php echo $id ?>)<?php endif; ?></td>
                <td colspan="3">
                    <a href="<?php echo url_for('facturation_declarant', ['identifiant' =>  isset($compte) ? $compte->_id : $id]) ?>#mouvements" class="btn btn-xs btn-default pull-right">
                        <?php if($withDetails): ?>
                        Voir l'espace facture
                        <?php else: ?>
                        Voir le<?php echo (count($mouvements) > 1) ? 's' : '' ?> <?php echo count($mouvements) ?> mouvement<?php echo (count($mouvements) > 1) ? 's' : '' ?>
                        <span class="glyphicon glyphicon-chevron-right"></span>
                        <?php endif; ?>
                    </a>
                </td>
            </tr>
            <?php if($withDetails): ?>
                <?php foreach($mouvements as $mvt): ?>
                    <?php $valueMvt = (isset($mvt->value))? $mvt->value : $mvt;
                     ?>
                    <tr>
                        <td><a href="<?php echo url_for("declaration_doc", array("id" => $mvt->id))?>" ><?php echo $valueMvt->type;?><?php if($valueMvt->version): ?>&nbsp;<?php echo $valueMvt->version;?><?php endif; ?>&nbsp;<?php echo $valueMvt->campagne;?></a></td>
                        <td><?php echo format_date($valueMvt->date, "dd/MM/yyyy", "fr_FR"); ?></td>
                        <td><?php echo $valueMvt->type_libelle ?> <?php echo $valueMvt->detail_libelle ?></td>
                        <td class="text-right"><?php echo echoFloat($valueMvt->quantite); ?>&nbsp;<small class="text-muted"><?php if(isset($valueMvt->unite)): ?><?php echo $valueMvt->unite ?><?php else: ?>&nbsp;&nbsp;<?php endif; ?></small></td>
                        <td class="text-right"><?php echo echoFloat($valueMvt->taux); ?>&nbsp;€</td>
                        <td class="text-right"><?php echo echoFloat($valueMvt->tva * 100, 0, 0); ?>&nbsp;%</td>
                        <td class="text-right"><?php echo echoFloat($valueMvt->taux * $valueMvt->quantite); ?>&nbsp;€</td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach ?>
      </tbody>
    </table>
