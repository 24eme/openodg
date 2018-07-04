<?php use_helper('Date'); ?>
<?php $query = ($query) ? $query->getRawValue() : $query;
$activites_libelles = HabilitationClient::$activites_libelles;
if(count(HabilitationConfiguration::getInstance()->getActivites())){
    $activites_libelles = HabilitationConfiguration::getInstance()->getActivites();
}
?>

<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('habilitation'); ?>">Habilitations</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('habilitation_etablissement_selection'))); ?>
    </div>
</div>

<h3>Liste des demandes en cours</h3>
<div class="row">
    <div class="col-sm-9 col-xs-12">
        <table class="table table-bordered table-striped table-condensed">
            <thead>
                <tr>
                    <th class="col-xs-1 text-center">Date</th>
                    <th class="col-xs-3">Opérateur</th>
                    <th class="col-xs-2 text-center">Demande</th>
                    <th class="col-xs-4 text-center">Libellé</th>
                    <th class="col-xs-2 text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($docs as $doc):
                  $habilitation = HabilitationClient::getInstance()->find($doc->id);
                  $declarant = $habilitation->getDeclarant();
                   ?>
                    <tr>
                        <td class="text-center"><?php echo format_date($doc->key[HabilitationDemandeView::KEY_DATE], "dd/MM/yyyy", "fr_FR"); ?></td>
                        <td><a href="<?php echo url_for("habilitation_declarant", array("identifiant" => $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT])); ?>"><?php echo $declarant->raison_sociale; ?> <small>(<?php echo $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT]; echo ($declarant->cvi)? "/".$declarant->cvi : ""; ?>)</small></a></td>
                        <td><?php echo HabilitationClient::$demande_libelles[$doc->key[HabilitationDemandeView::KEY_DEMANDE]]; ?></td>
                        <td><?php echo $doc->key[HabilitationDemandeView::KEY_LIBELLE]; ?></td>
                        <td><a href="<?php echo url_for('habilitation_demande_edition', array('identifiant' => $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT], 'demande' => $doc->key[HabilitationDemandeView::KEY_DEMANDE_KEY], 'retour' => $sf_request->getUri())) ?>"><?php echo HabilitationClient::getInstance()->getLibelleStatut($doc->key[HabilitationDemandeView::KEY_STATUT]) ; ?></a></td>
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
        <p class="text-muted"><i><?php echo $nbResultats ?> demande<?php if ($nbResultats > 1): ?>s<?php endif; ?></i></p>
        <?php if($query && count($query) > 0): ?>
        <p>
            <a href="<?php echo url_for('habilitation', array('query' => 0)) ?>"><small><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Annuler tous les filtres</small></a>
        </p>
        <?php endif; ?>
        <?php foreach($facets as $facetNom => $items): ?>
        <h4><?php echo $facetNom; ?></h4>
        <div class="list-group">
            <?php foreach($items as $itemNom => $count): ?>
                <?php $active = isset($query[$facetNom]) && $query[$facetNom] == $itemNom; ?>
                <?php $params = is_array($query) ? $query : array(); if($active): unset($params[$facetNom]); else: $params = array_merge($params, array($facetNom => $itemNom)); endif; ?>
                <?php if(!count($params)): $params = false; endif; ?>
                <a href="<?php echo url_for('habilitation', array('query' => $params)) ?>" class="list-group-item <?php if($active): ?>active<?php endif; ?>"><span class="badge"><?php echo $count; ?></span> <?php if($facetNom == "Statut"): ?><?php echo HabilitationClient::$demande_statut_libelles[$itemNom]; ?><?php elseif($facetNom == "Demande"): ?><?php echo HabilitationClient::$demande_libelles[$itemNom]; ?><?php endif ?></a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
