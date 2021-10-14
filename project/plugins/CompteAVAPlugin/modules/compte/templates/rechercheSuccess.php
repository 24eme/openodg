<?php $allTypeCompte = CompteClient::getInstance()->getAllTypesCompteWithLibelles(); ?>

<?php $argsForm = $args->getRawValue(); ?>
<?php unset($argsForm['q']) ?>

<ol class="breadcrumb">
    <li class="active"><a href="<?php echo url_for('compte_recherche'); ?>">Contacts</a></li>
</ol>

<div class="row">
    <div class="col-sm-offset-6 col-sm-3 col-xs-12">
            <button type="button" class="btn btn-sm btn-default btn-default-step btn-block btn-upper dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                <span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Créer un contact&nbsp;&nbsp;<span class="caret"></span>
            </button>
            <ul class="dropdown-menu" role="menu">
            <?php foreach(CompteClient::getInstance()->getAllTypesCompteWithLibelles() as $type_compte => $libelle): ?>
                <li><a href="<?php echo url_for("compte_creation", array("type_compte" => $type_compte)) ?>"><?php echo $libelle ?></a></li>
            <?php endforeach; ?>
            </ul>
    </div>
    <div class="col-sm-3 col-xs-12">
    <a class="btn btn-default btn-default-step  btn-sm btn-block btn-upper" href="<?php echo url_for("compte_recherche_avancee") ?>"><span class="glyphicon glyphicon-zoom-in"></span>&nbsp;&nbsp;Recherche avancée</a>
    </div>
    <div class="col-sm-9 col-xs-12" style="padding-top: 10px;">
        <form action="<?php echo url_for("compte_recherche", $argsForm) ?>" method="get" class="form-horizontal">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="input-group">
                <?php echo $form["q"]->render(array("value" => ($q == '*') ? '' : $q, "class" => "form-control input-lg typeahead", "placeholder" => "Votre recherche...", "autofocus" => "autofocus", "autocomplete" => "off", "data-url" => url_for('compte_recherche_json', array('link' => true, 'type_compte' => '*')), "data-query-param" => "q", "data-link" => "visualisationLink", "data-text" => "text_html")); ?>
                <span class="input-group-btn">
                    <button class="btn btn-lg btn-info" type="submit" style="font-size: 22px; padding-top: 8px; padding-bottom: 8px;"><span class="glyphicon glyphicon-search"></span></button>
                </span>
            </div>
        </form>
        <?php if ($nb_results > 0): ?>
            <div class="list-group" style="margin-top: 15px">
            <?php foreach ($results as $res): ?>
            <?php $data = $res->getData()['doc']; ?>
                <a style="<?php if($data['statut'] != CompteClient::STATUT_ACTIF): ?>opacity: 0.6<?php endif ?>" href="<?php echo url_for('compte_visualisation', array("id" => $data["_id"])); ?>" class="list-group-item">
                    <h3 class="list-group-item-heading"><?php echo Anonymization::hideIfNeeded($data['nom_a_afficher']); ?> <?php if($data['cvi'] || $data['siren']): ?><small><?php if($data['cvi']): ?><?php echo $data['cvi'] ?><?php endif; ?><?php if($data['cvi'] && $data['siren']): ?> / <?php endif; ?><?php if($data['siren']): ?><?php echo Anonymization::hideIfNeeded($data['siren']) ?><?php endif; ?></small><?php endif; ?> <button class="btn btn-xs btn-info pull-right"><?php echo $allTypeCompte[$data['type_compte']]; ?></button></h3>
                    <p class="list-group-item-text">
                    <div class="pull-right">
                     <?php if ($data['telephone_bureau']):?>
                                    <abbr class="text-muted" title="Bureau"><i>Bureau</abbr>&nbsp;:</i>&nbsp;&nbsp;<?php echo Anonymization::hideIfNeeded($data['telephone_bureau']) ?><br />
                                <?php endif; ?>
                                <?php if ($data['telephone_mobile']):?>
                                    <abbr class="text-muted" title="Mobile"><i>Mobile</abbr>&nbsp;:</i>&nbsp;&nbsp;&nbsp;<?php echo Anonymization::hideIfNeeded($data['telephone_mobile']) ?><br />
                                <?php endif; ?>
                                <?php if ($data['telephone_prive']):?>
                                    <abbr class="text-muted" title="Privé"><i>Privé</abbr>&nbsp;:</i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo Anonymization::hideIfNeeded($data['telephone_prive']) ?><br />
                                <?php endif; ?>
                    </div>
                    <div>
                            <?php if($data['adresse_complement_destinataire']): ?>
                                <?php echo Anonymization::hideIfNeeded($data['adresse_complement_destinataire']); ?><br />
                            <?php endif; ?>
                            <?php echo Anonymization::hideIfNeeded($data['adresse']); ?><br />
                            <?php if($data['adresse_complement_lieu']): ?>
                                <?php echo Anonymization::hideIfNeeded($data['adresse_complement_lieu']); ?><br />
                            <?php endif; ?>
                            <?php echo $data['code_postal']; ?>&nbsp;<?php echo $data['commune']; ?><br />
                            <?php if ($data['email']):?>
                                <span class="glyphicon glyphicon-envelope"></span>&nbsp;<?php echo Anonymization::hideIfNeeded($data['email']) ?><br />
                            <?php endif; ?>
                            <?php $tags_contact = (isset($data['tags'])) ? array_merge($data['tags']['attributs']->getRawValue(), $data['tags']['manuels']->getRawValue()) : array(); ?>
                            <?php if(count($tags_contact) > 0): ?>
                            <br />
                            <small class="text-muted"><span class="glyphicon glyphicon-tags"></span>&nbsp;&nbsp;&nbsp;<?php echo implode(", ", $tags_contact); ?></small>
                            <?php endif; ?>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($nb_results > 0 && $last_page > 1): ?>
            <div class="text-center">
                <nav>
                    <ul class="pagination pagination-lg" style="margin-top: 0;">
                        <?php
                        $argssearch = array('q' => $q, 'tags' => $args['tags']->getRawValue(), 'all' => $args['all']);
                        ?>
                        <?php if ($current_page > 1) : ?>
                            <?php $argssearch['page'] = $current_page - 1; ?>
                            <li><a href="<?php echo url_for('compte_recherche', $argssearch); ?>" aria-label="Previous"><span aria-hidden="true"><span class="glyphicon glyphicon-chevron-left"></span></span></a></li>
                            <?php $argssearch['page'] = 1; ?>
                            <li><a href="<?php echo url_for('compte_recherche', $argssearch); ?>" aria-label="Previous"><span aria-hidden="true"><small><small class="text-muted">Première page</small></small></span</span></a></li>
                        <?php else: ?>
                            <li class="disabled"><span aria-hidden="true"><span class="glyphicon glyphicon-chevron-left"></span></span></li>
                            <li class="disabled"><span aria-hidden="true"><small><small>Première page</small></small></span></li>
                        <?php endif; ?>
                        <li><span aria-hidden="true"><small><small>Page <?php echo $current_page ?> / <?php echo $last_page ?></span></small></small></li>
                        <?php $argssearch['page'] = $last_page; ?>
                        <?php if ($current_page != $argssearch['page']): ?>
                            <li><a href="<?php echo url_for('compte_recherche', $argssearch); ?>" aria-label="Next"><span aria-hidden="true"><small><small class="text-muted">Dernière page</small></small></span></a></li>
                        <?php else: ?>
                            <li class="disabled"><span aria-hidden="true"><small><small>Dernière page</small></small></span></li>
                        <?php endif; ?>
                        <?php
                        if ($current_page < $last_page)
                            $argssearch['page'] = $current_page + 1;
                        else
                            $argssearch['page'] = $last_page;
                        ?>
                        <?php if ($current_page != $argssearch['page']): ?>
                            <li><a href="<?php echo url_for('compte_recherche', $argssearch); ?>" aria-label="Next"><span aria-hidden="true"></span><span class="glyphicon glyphicon-chevron-right"></span></a></li>
                        <?php else: ?>
                            <li class="disabled"><span aria-hidden="true"><span class="glyphicon glyphicon-chevron-right"></span></span></li>
                        <?php endif; ?>

                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-sm-3 col-xs-12">
        <p class="text-muted"><i><?php echo $nb_results ?> résultat<?php if ($nb_results > 1): ?>s<?php endif; ?></i></p>
        <p>
        <a href="<?php echo url_for("compte_recherche_csv", $args->getRawValue()) ?>" class="btn btn-default btn-default-step btn-block btn-upper"><span class="glyphicon glyphicon-export"></span>&nbsp;&nbsp;Exporter en CSV</a>
        <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
        <a href="<?php echo url_for("facturation_massive", array("q" => compteActions::convertArgumentsToQuery($args->getRawValue()))) ?>" class="btn btn-default btn-default-step btn-block btn-upper"><span class="glyphicon glyphicon-euro"></span>&nbsp;&nbsp;Facturer</a>
        <?php endif; ?>
        </p>
        <div style="<?php if($q == '*'): echo "opacity: 0.8"; endif; ?>">
            <h4>Affiner la recherche</h4>
            <?php if(!isset($args['all']) || !$args['all']): ?>
            <?php $argsTemplate = $args->getRawValue(); $argsTemplate['all'] = 1; ?>
            <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class=""><span class="glyphicon glyphicon-unchecked"></span><small>&nbsp;&nbsp;Inclure les comptes inactifs</small></a>
            <?php else: ?>
            <?php $argsTemplate = $args->getRawValue(); $argsTemplate['all'] = 0; ?>
            <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class=""><span class="glyphicon glyphicon-check"></span><small>&nbsp;&nbsp;Inclure les comptes inactifs</small></a>
            <?php endif ?>
            <?php if(count($args['tags']->getRawValue()) > 0): ?>
            <p>
                <?php $argsTemplate = $args->getRawValue(); unset($argsTemplate['tags']); ?>
                <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="text-danger"><small><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Annuler tous les filtres</small></a>
            </p>
            <?php endif; ?>
            <?php foreach ($facets->getRawValue() as $type => $ftype): ?>
                <?php if (count($ftype['buckets'])): ?>
                    <h5><small><?php echo $facets_libelle[$type] ?></small></h5>
                    <div class="list-group" style="max-height: 200px; overflow: auto;">
                        <?php
                        foreach ($ftype['buckets'] as $f):
                            $tag = $type . ':' . $f['key'];
                            $argsTemplate = $args->getRawValue();
                            if (!in_array($tag, $argsTemplate['tags'])) {
                                $argsTemplate['tags'][] = $tag;
                            }
                            ?>
                            <?php if(in_array($tag, $args['tags']->getRawValue())): ?>
                                <?php $argsTemplate['tags'] = array_diff($argsTemplate['tags'], array($tag)); ?>
                                <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="list-group-item list-group-item-warning" style="padding: 8px 8px"><small class="pull-right"><span class="glyphicon glyphicon-trash"></span></small><small><?php echo $f['key'] ?></small>&nbsp;</a>
                            <?php else: ?>
                                <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="list-group-item" style="padding: 8px 8px"><span class="badge"><small><?php echo $f['doc_count'] ?></small></span><small><?php echo $f['key'] ?></small>&nbsp;</a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
