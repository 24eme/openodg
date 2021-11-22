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
        <tr><th>Établissements</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($mouvements->getRawValue() as $id => $mouvements): ?>
            <tr>
                <td colspan="4" title="<?php echo $id ?>"><?php echo EtablissementClient::getInstance()->retrieveById($id, acCouchdbClient::HYDRATE_JSON)->raison_sociale ?></td>
                <td colspan="3">
                    <a href="<?php echo url_for('facturation_declarant', ['id' => $id]) ?>#mouvements" class="btn btn-xs btn-default pull-right">
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
