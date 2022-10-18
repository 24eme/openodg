<?php use_helper('Date'); ?>
<?php $query = ($query) ? $query->getRawValue() : $query; ?>

<ol class="breadcrumb">
  <li class="active"><a href="<?php echo url_for('habilitation_demande'); ?>">Habilitations</a></li>
</ol>

<div class="row row-margin">
    <div class="col-xs-12">
        <?php include_partial('etablissement/formChoice', array('form' => $form, 'action' => url_for('habilitation_etablissement_selection'))); ?>
    </div>
</div>

<div class="row">
    <div class="col-sm-9 col-lg-10 col-xs-12">
        <table class="table table-bordered table-striped table-condensed">
            <thead>
                <tr>
                    <th class="col-xs-1 text-left">Date</th>
                    <th class="col-xs-1 text-center">Nb jours</th>
                    <th class="col-xs-3">Opérateur</th>
                    <th class="col-xs-1 text-center">Demande</th>
                    <th class="col-xs-4 text-center">Libellé</th>
                    <th class="col-xs-2 text-center">Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($docs as $doc):
                     $declarant = EtablissementClient::getInstance()->findByIdentifiant($doc->key[HabilitationDemandeView::KEY_IDENTIFIANT], acCouchdbClient::HYDRATE_JSON);
                     $date = new DateTime($doc->key[HabilitationDemandeView::KEY_DATE]);
                   ?>
                    <tr class="<?php if(in_array($doc->key[HabilitationDemandeView::KEY_STATUT], HabilitationClient::getInstance()->getStatutsFerme())): ?>transparence-sm<?php endif; ?>">
                        <td class="text-left"><?php echo $date->format('d/m/Y') ?></td>
                        <td class="text-center"><?php echo $doc->key[HabilitationDemandeView::KEY_NBJOURS]; ?></td>
                        <td><a href="<?php echo url_for("habilitation_declarant", array("identifiant" => $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT])); ?>"><?php echo $declarant->raison_sociale; ?> <small>(<?php echo $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT]; echo ($declarant->cvi)? "/".$declarant->cvi : ""; ?>)</small></a></td>
                        <td><?php echo HabilitationClient::$demande_libelles[$doc->key[HabilitationDemandeView::KEY_DEMANDE]]; ?></td>
                        <td><?php echo $doc->key[HabilitationDemandeView::KEY_LIBELLE]; ?></td>
                        <td><a href="<?php echo url_for('habilitation_demande_edition', array('identifiant' => $doc->key[HabilitationDemandeView::KEY_IDENTIFIANT], 'demande' => $doc->key[HabilitationDemandeView::KEY_DEMANDE_KEY], 'retour' => $sf_request->getUri())) ?>"><?php echo HabilitationClient::getInstance()->getDemandeStatutLibelle($doc->key[HabilitationDemandeView::KEY_STATUT]) ; ?></a></td>
                    </tr>
                <?php endforeach; ?>
                <?php if(!$nbResultats): ?>
                    <tr>
                        <td colspan="6" class="text-center"><em>Aucune demande</em></td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
        <?php if($nbPage > 0): ?>
        <div class="text-center">
            <ul class="pagination" style="margin-top: 0;">
                <li <?php if ($page - 1  < 1) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation_demande', array('query' =>  $query, 'page' => (($page - 1) > 0) ? $page - 1 : 1, 'voirtout' => $voirtout*1, 'sort' => $sort)); ?>" aria-label="Previous"><span aria-hidden="true"><span class="glyphicon glyphicon-chevron-left"></span></span></a></li>
                <li <?php if ($page -1 < 1) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation_demande', array('query' =>  $query, 'page' => 1, 'voirtout' => $voirtout*1, 'sort' => $sort)); ?>" aria-label="Previous"><span aria-hidden="true"><small>Première page</small></span</span></a></li>
                <li><span aria-hidden="true"><small>Page <?php echo $page ?> / <?php echo $nbPage ?></span></small></li>
                <li <?php if ($page +1 > $nbPage) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation_demande', array('query' =>  $query, 'page' => $nbPage, 'voirtout' => $voirtout*1, 'sort' => $sort)); ?>" aria-label="Next"><span aria-hidden="true"><small>Dernière page</small></span></a></li>
                <li <?php if ($page + 1 > $nbPage) : ?>class="disabled"<?php endif; ?>><a href="<?php echo url_for('habilitation_demande', array('query' =>  $query, 'page' =>(($page + 1) > $nbPage) ? $page : $page + 1, 'voirtout' => $voirtout*1, 'sort' => $sort)); ?>" aria-label="Next"><span aria-hidden="true"></span><span class="glyphicon glyphicon-chevron-right"></span></a></li>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-sm-3 col-lg-2 col-xs-12">
        <div class="list-group">
            <p class="text-muted"><i><?php echo $nbResultats ?> demande<?php if ($nbResultats > 1): ?>s<?php endif; ?></i></p>
        </div>
        <?php if ($regionParam): ?>
        <h4>Région</h4>
        <div class="list-group">
            <span class="list-group-item active"><span class="badge"><?php echo $nbResultats; ?></span> <?php echo str_replace('_', ' ', $regionParam); ?></a>
        </div>
        <?php endif; ?>
        <h4>Trié par</h4>
        <div class="list-group">
            <?php foreach($sorts as $libelle => $keys): ?>
                <a href="<?php echo url_for('habilitation_demande', array('query' => $query, 'sort' => $libelle, 'voirtout' => $voirtout*1)) ?>" class="list-group-item <?php if($sort == $libelle): ?>active<?php endif; ?>"><?php echo $libelle ?></a>
            <?php endforeach; ?>
        </div>
        <div class="checkbox" style="margin-bottom: 20px;">
            <label>
                <input data-href="<?php echo url_for('habilitation_demande', array('voirtout' => (!$voirtout)*1, 'region' => $regionParam)); ?>" id="habilitation_voirtout" <?php if($voirtout): ?>checked="checked"<?php endif; ?> type="checkbox" value="1" /> Demandes terminées
            </label>
        </div>
        <?php if($query && count($query) > 0): ?>
        <p>
            <a href="<?php echo url_for('habilitation_demande') ?>"><small><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Annuler tous les filtres</small></a>
        </p>
        <?php endif; ?>
        <?php foreach($facets as $facetNom => $items): ?>
        <h4><?php echo $facetNom; ?></h4>
        <div class="list-group">
            <?php foreach($items as $itemNom => $count): ?>
                <?php $active = isset($query[$facetNom]) && $query[$facetNom] == $itemNom; ?>
                <?php $params = is_array($query) ? $query : array(); if($active): unset($params[$facetNom]); else: $params = array_merge($params, array($facetNom => $itemNom)); endif; ?>
                <?php if(!count($params)): $params = false; endif; ?>
                <a href="<?php echo url_for('habilitation_demande', array('query' => $params, 'sort' => $sort, 'voirtout' => $voirtout*1)) ?>" class="list-group-item <?php if($active): ?>active<?php endif; ?>"><span class="badge"><?php echo $count; ?></span> <?php if($facetNom == "Statut"): ?><?php echo HabilitationClient::getInstance()->getDemandeStatutLibelle($itemNom); ?><?php elseif($facetNom == "Demande"): ?><?php echo HabilitationClient::$demande_libelles[$itemNom]; ?><?php else: ?><?php echo $itemNom ?><?php endif ?></a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
        <hr />
        <div class="list-group">
            <a href="<?php echo url_for('habilitation_export_historique', array('dateFrom' => date('Y-m-d'), 'dateTo' => date('Y-m-d'))) ?>"><small>Exporter l'historique du jour</small></a>
        </div>
    </div>
</div>
