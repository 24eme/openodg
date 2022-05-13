<?php use_helper('Date'); ?>
<?php $query = ($query) ? $query->getRawValue() : $query ?>

<ol class="breadcrumb">
  <li class="active"><a href="<?php echo ($regionParam)?  url_for('declaration',(array('region' => $regionParam))) : url_for('declaration'); ?>">Déclarations</a></li>
  <?php if ($sf_user->getTeledeclarationDrevRegion()): ?>
  <li><a href="<?php echo url_for('accueil'); ?>"><?php echo $sf_user->getTeledeclarationDrevRegion(); ?></a></li>
  <?php endif; ?>
</ol>

<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('declaration_etablissement_selection'))); ?>
    </div>
</div>
<h3>Liste des déclarations</h3>
<div class="row">
    <div class="col-sm-9 col-xs-12">
    	<?php if (!count($docs)): ?>
    	<p><em>Aucune déclaration enregistrée</em></p>
    	<?php else: ?>
        <table class="table table-bordered table-striped table-condensed">
            <thead>
                <tr>
                    <th class="col-xs-1">Date</th>
                    <th class="col-xs-1 text-center">Camp.</th>
                    <th class="col-xs-1 text-center">Type</th>
                    <th class="col-xs-4">Opérateur</th>
                    <th class="col-xs-2 text-center">Mode</th>
                    <th class="col-xs-2 text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($docs as $doc):

                  ?>
                    <tr>

                      <?php $params = array("id" => $doc->id); if($regionParam): $params=array_merge($params,array('region' => $regionParam)); endif; ?>
                        <td><a href="<?php echo url_for("declaration_doc", $params); ?>"><?php if($doc->key[DeclarationTousView::KEY_DATE] && $doc->key[DeclarationTousView::KEY_DATE] !== true): ?><?php echo format_date($doc->key[DeclarationTousView::KEY_DATE], "dd/MM/yyyy", "fr_FR"); ?><?php else: ?><small class="text-muted">Aucune</small><?php endif; ?></a></td>
                        <td><?php echo $doc->key[DeclarationTousView::KEY_CAMPAGNE]; ?></td>
                        <td><a href="<?php echo url_for("declaration_doc", $params); ?>"><?php echo $doc->key[DeclarationTousView::KEY_TYPE]; ?></a></td>
                        <td><a href="<?php echo url_for("declaration_doc", $params); ?>"><?php echo Anonymization::hideIfNeeded($doc->key[DeclarationTousView::KEY_RAISON_SOCIALE]); ?> <small>(<?php echo $doc->key[DeclarationTousView::KEY_IDENTIFIANT]; ?>)</small></a></td>
                        <td class="text-center"><?php echo $doc->key[DeclarationTousView::KEY_MODE]; ?></td>
                        <td class="text-center"><a href="<?php echo url_for("declaration_doc", $params); ?>"><?php echo $doc->key[DeclarationTousView::KEY_STATUT]; ?><?php if($doc->key[DeclarationTousView::KEY_INFOS]): ?><br /><small class="text-muted"><?php echo $doc->key[DeclarationTousView::KEY_INFOS] ?></small><?php endif; ?></a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-center">
            <ul class="pagination" style="margin-top: 0;">
                <li <?php if ($page - 1  < 1) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('declaration', array('query' =>  $query, 'page' => (($page - 1) > 0) ? $page - 1 : 1)); ?>" aria-label="Previous"><span aria-hidden="true"><span class="glyphicon glyphicon-chevron-left"></span></span></a></li>
                <li <?php if ($page -1 < 1) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('declaration', array('query' =>  $query, 'page' => 1)); ?>" aria-label="Previous"><span aria-hidden="true"><small>Première page</small></span</span></a></li>
                <li><span aria-hidden="true"><small>Page <?php echo $page ?> / <?php echo $nbPage ?></span></small></li>
                <li <?php if ($page +1 > $nbPage) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('declaration', array('query' =>  $query, 'page' => $nbPage)); ?>" aria-label="Next"><span aria-hidden="true"><small>Dernière page</small></span></a></li>
                <li <?php if ($page + 1 > $nbPage) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('declaration', array('query' =>  $query, 'page' =>(($page + 1) > $nbPage) ? $page : $page + 1)); ?>" aria-label="Next"><span aria-hidden="true"></span><span class="glyphicon glyphicon-chevron-right"></span></a></li>
            </ul>
        </div>
    <?php endif; ?>
    </div>

    <div class="col-sm-3 col-xs-12">
        <p class="text-muted"><i><?php echo $nbResultats ?> déclaration<?php if ($nbResultats > 1): ?>s<?php endif; ?></i></p>
        <p><a href="<?php echo url_for('declaration_export', array('query' => $query)) ?>" class="btn btn-default btn-default-step btn-block btn-upper"><span class="glyphicon glyphicon-export"></span>&nbsp;&nbsp;Exporter en CSV</a>
        </p>
        <?php if($query && count($query) > 0): ?>
        <p>
            <a href="<?php echo url_for('declaration', array('query' => 0)) ?>"><small><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Annuler tous les filtres</small></a>
        </p>
        <?php endif; ?>
        <?php if ($regionParam) : ?>
        <h4>Région</h4>
        <div class="list-group">
            <span class="list-group-item active"><span class="badge"><?php echo $nbResultats; ?></span> <?php echo str_replace('_', ' ', $regionParam); ?></a>
        </div>
        <?php endif; ?>
        <?php foreach($facets as $facetNom => $items): ?>
        <h4><?php echo $facetNom; ?></h4>
        <div class="list-group">
            <?php foreach($items as $itemNom => $count): ?>
                <?php $active = isset($query[$facetNom]) && $query[$facetNom] == $itemNom; ?>
                <?php $params = is_array($query) ? $query : array(); if($active): unset($params[$facetNom]); else: $params = array_merge($params, array($facetNom => $itemNom)); endif; ?>
                <?php if(!count($params)): $params = false; endif; ?>
                <?php if($facetNom == 'Produit'): $itemNom = $produitsLibelles[$itemNom]; endif; ?>
                <?php $allParams=array('query' => $params); if($regionParam): $allParams=array_merge($allParams,array('region' => $regionParam)); endif;  ?>
                <a href="<?php echo url_for('declaration', $allParams) ?>" class="list-group-item <?php if($active): ?>active<?php endif; ?>"><span class="badge"><?php echo $count; ?></span> <?php echo $itemNom; ?></a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
