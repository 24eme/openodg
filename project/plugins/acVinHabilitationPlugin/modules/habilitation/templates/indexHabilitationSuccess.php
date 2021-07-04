<?php use_helper('Date'); ?>
<?php $query = ($query) ? $query->getRawValue() : $query; ?>

<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('habilitation'); ?>">Habilitations</a></li>
</ol>

<?php if(isset($form)): ?>
<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('habilitation_etablissement_selection'))); ?>
    </div>
</div>
<?php endif; ?>

<h3>Liste des habilitations</h3>
<div class="row">
    <div class="col-sm-9 col-xs-12">
        <table class="table table-bordered table-striped table-condensed">
            <thead>
                <tr>
                    <th class="col-xs-4">Opérateur</th>
                    <th class="col-xs-3 text-center">Produit</th>
                    <th class="col-xs-1 text-center">Activité</th>
                    <th class="col-xs-1 text-center">Statut</th>
                    <th class="col-xs-1 text-center">Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($docs as $doc):
                  $declarant = EtablissementClient::getInstance()->findByIdentifiant($doc->key[HabilitationActiviteView::KEY_IDENTIFIANT], acCouchdbClient::HYDRATE_JSON);
                   ?>
                    <tr>
                        <td><a href="<?php echo url_for("habilitation_declarant", array("identifiant" => $doc->key[HabilitationActiviteView::KEY_IDENTIFIANT])); ?>"><?php echo Anonymization::hideIfNeeded($declarant->raison_sociale); ?> <small>(<?php echo $doc->key[HabilitationActiviteView::KEY_IDENTIFIANT]; echo ($declarant->cvi)? "/".$declarant->cvi : ""; ?>)</small></a></td>
                        <td><?php echo $doc->key[HabilitationActiviteView::KEY_PRODUIT_LIBELLE]; ?></td>
                        <td><?php echo HabilitationClient::getInstance()->getLibelleActivite($doc->key[HabilitationActiviteView::KEY_ACTIVITE]); ?></td>
                        <td><a href="<?php echo url_for('habilitation_declarant', array('identifiant' => $doc->key[HabilitationActiviteView::KEY_IDENTIFIANT])) ?>"><?php echo HabilitationClient::$statuts_libelles[$doc->key[HabilitationActiviteView::KEY_STATUT]]; ?></a></td>
                        <td class="text-center"><?php echo format_date($doc->key[HabilitationActiviteView::KEY_DATE], "dd/MM/yyyy", "fr_FR"); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <ul class="pagination" style="margin-top: 0;">
                <li <?php if ($page - 1  < 1) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation', array('query' =>  $query, 'page' => (($page - 1) > 0) ? $page - 1 : 1)); ?>" aria-label="Previous"><span aria-hidden="true"><span class="glyphicon glyphicon-chevron-left"></span></span></a></li>
                <li <?php if ($page -1 < 1) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation', array('query' =>  $query, 'page' => 1)); ?>" aria-label="Previous"><span aria-hidden="true"><small>Première page</small></span</span></a></li>
                <li><span aria-hidden="true"><small>Page <?php echo $page ?> / <?php echo $nbPage ?></span></small></li>
                <li <?php if ($page +1 > $nbPage) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation', array('query' =>  $query, 'page' => $nbPage)); ?>" aria-label="Next"><span aria-hidden="true"><small>Dernière page</small></span></a></li>
                <li <?php if ($page + 1 > $nbPage) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation', array('query' =>  $query, 'page' =>(($page + 1) > $nbPage) ? $page : $page + 1)); ?>" aria-label="Next"><span aria-hidden="true"></span><span class="glyphicon glyphicon-chevron-right"></span></a></li>
            </ul>
        </div>
    </div>

    <div class="col-sm-3 col-xs-12">
        <p class="text-muted"><i><?php echo $nbResultats ?> habilitation<?php if ($nbResultats > 1): ?>s<?php endif; ?></i></p>
        <p><a href="<?php echo url_for('habilitation_export', array('query' => $query)) ?>" class="btn btn-default btn-default-step btn-block btn-upper"><span class="glyphicon glyphicon-export"></span>&nbsp;&nbsp;Exporter en CSV</a>
        </p>
        <?php if($query && count($query) > 0): ?>
        <p>
            <a href="<?php echo url_for('habilitation', array('query' => "0")) ?>"><small><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Annuler tous les filtres</small></a>
        </p>
        <?php endif; ?>
        <?php foreach($facets as $facetNom => $items): ?>
        <h4><?php echo $facetNom; ?></h4>
        <div class="list-group">
            <?php foreach($items as $itemNom => $count): ?>
                <?php $active = isset($query[$facetNom]) && $query[$facetNom] == $itemNom; ?>
                <?php $params = is_array($query) ? $query : array(); if($active): unset($params[$facetNom]); else: $params = array_merge($params, array($facetNom => $itemNom)); endif; ?>
                <?php if(!count($params)): $params = false; endif; ?>
                <a href="<?php echo url_for('habilitation', array('query' => $params)) ?>" class="list-group-item <?php if($active): ?>active<?php endif; ?>"><span class="badge"><?php echo $count; ?></span> <?php if($facetNom == "Statut"): ?><?php echo HabilitationClient::$statuts_libelles[$itemNom]; ?><?php elseif($facetNom == "Activité") :?><?php echo HabilitationClient::getInstance()->getLibelleActivite($itemNom); ?><?php else :?><?php echo $itemNom; ?><?php endif ?></a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
