<?php $allTypeCompte = CompteClient::getInstance()->getAllTypesCompteWithLibelles(); ?>

<div>
<div class="btn-group pull-right">
    <button type="button" class="btn btn-default btn-default-step" data-toggle="dropdown" aria-expanded="false">
        <span class="glyphicon glyphicon-plus-sign"></span>&nbsp;&nbsp;Créer un contact&nbsp;&nbsp;<span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
    <?php foreach(CompteClient::getInstance()->getAllTypesCompteWithLibelles() as $type_compte => $libelle): ?>
        <li><a href="<?php echo url_for("compte_creation_admin", array("type_compte" => $type_compte)) ?>"><?php echo $libelle ?></a></li>
    <?php endforeach; ?>
    </ul>
</div>

<ul class="nav nav-tabs">
  <li role="presentation" ><a href="<?php echo url_for('admin'); ?>">Déclarations</a></li>
  <li role="presentation" class="active"><a href="<?php echo url_for('compte_recherche'); ?>">Contacts</a></li>
</ul>
</div>


<?php $argsForm = $args->getRawValue(); ?>
<?php unset($argsForm['q']) ?>
<form action="<?php echo url_for("compte_recherche", $argsForm) ?>" method="get" class="form-horizontal">  
<div class="row">
    <div class="col-xs-9">
        <div class="col-xs-12">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="input-group">
                <?php echo $form["q"]->render(array("value" => ($q == '*') ? '' : $q, "class" => "form-control input-lg", "placeholder" => "Votre recherche...")); ?>
                <span class="input-group-btn">
                    <button class="btn btn-lg btn-info" type="submit" style="font-size: 22px; padding-top: 8px; padding-bottom: 8px;"><span class="glyphicon glyphicon-search"></span></button>
                </span>
            </div>
        </div>
        <?php if ($nb_results > 0): ?>
        <div class="col-xs-12" style="padding-top: 15px">
            <div class="list-group">
            <?php foreach ($results as $res): ?>
            <?php $data = $res->getData(); ?>
                <a style="<?php if($data['statut'] != CompteClient::STATUT_ACTIF): ?>opacity: 0.6<?php endif ?>" href="<?php echo url_for('compte_visualisation_admin', array("id" => $data["_id"])); ?>" class="list-group-item">
                    <h3 class="list-group-item-heading"><?php echo $data['nom_a_afficher']; ?> <?php if($data['cvi'] || $data['siren']): ?><small><?php if($data['cvi']): ?><?php echo $data['cvi'] ?><?php endif; ?><?php if($data['cvi'] && $data['siren']): ?> / <?php endif; ?><?php if($data['siren']): ?><?php echo $data['siren'] ?><?php endif; ?></small><?php endif; ?> <button class="btn btn-xs btn-info pull-right"><?php echo $allTypeCompte[$data['type_compte']]; ?></button></h3>
                    <p class="list-group-item-text">
                    <div class="pull-right">
                     <?php if ($data['telephone_bureau']):?>
                                    <abbr class="text-muted" title="Mobile"><i>Bureau</abbr>&nbsp;:</i>&nbsp;&nbsp;<?php echo $data['telephone_bureau'] ?><br />
                                <?php endif; ?>
                                <?php if ($data['telephone_mobile']):?>
                                    <abbr class="text-muted" title="Mobile"><i>Mobile</abbr>&nbsp;:</i>&nbsp;&nbsp;&nbsp;<?php echo $data['telephone_mobile'] ?><br />
                                <?php endif; ?>
                                <?php if ($data['telephone_prive']):?>
                                    <abbr class="text-muted" title="Privé"><i>Privé</abbr>&nbsp;:</i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $data['telephone_prive'] ?><br />
                                <?php endif; ?>
                    </div>
                    <div>
                              <?php echo $data['adresse']; ?><br />
                              <?php echo $data['code_postal']; ?>&nbsp;<?php echo $data['commune']; ?><br />
                              <?php if ($data['email']):?>
                                <span class="glyphicon glyphicon-envelope"></span>&nbsp;<?php echo $data['email'] ?><br />
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
        </div>
        <?php endif; ?>
        <?php if ($nb_results > 0 && $last_page > 1): ?>
            <div class="col-xs-12 text-center">
                <nav>
                    <ul class="pagination pagination-lg" style="margin-top: 0;">
                        <?php
                        $argssearch = array('q' => $q, 'tags' => $args['tags']->getRawValue());
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
    <div class="col-xs-3">
        <p class="text-muted"><i><?php echo $nb_results ?> résultat<?php if ($nb_results > 1): ?>s<?php endif; ?></i></p>
        <p><a href="<?php echo url_for("compte_recherche_csv", $args->getRawValue()) ?>" class="btn btn-default btn-default-step"><span class="glyphicon glyphicon-export"></span>&nbsp;&nbsp;Exporter en CSV</a></p>
        <div style="<?php if($q == '*'): echo "opacity: 0.5"; endif; ?>">
            <h4>Affiner la recherche</h4>
            <div class="input-group">
                <div class="checkbox">
                    <label>
                        <small><?php echo ($all) ? $form["all"]->render(array('checked' => 'checked')) : $form["all"]->render(); ?> Inclure les comptes inactifs</small>
                    </label>
                </div>
            </div>
            <div class="input-group">
            <?php if(count($args['tags']->getRawValue()) > 0): ?>
            <p>
                <?php $argsTemplate = $args->getRawValue(); unset($argsTemplate['tags']); ?>
                <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="text-danger"><small><span class="glyphicon glyphicon-trash"></span>&nbsp;&nbsp;Annuler tous les filtres</small></a>
            </p>
            <?php endif; ?>
            <?php foreach ($facets as $type => $ftype): ?>
                <?php if (count($ftype['terms'])): ?>
                    <h5><small><?php echo $facets_libelle[$type] ?></small></h5>
                    <div class="list-group">
                        <?php
                        foreach ($ftype['terms'] as $f):
                            $tag = $type . ':' . $f['term'];
                            $argsTemplate = $args->getRawValue();
                            if (!in_array($tag, $argsTemplate['tags'])) {
                                $argsTemplate['tags'][] = $tag;
                            }
                            ?>
                            <?php if(in_array($tag, $args['tags']->getRawValue())): ?>
                                <?php $argsTemplate['tags'] = array_diff($argsTemplate['tags'], array($tag)); ?>
                                <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="list-group-item list-group-item-warning" style="padding: 8px 8px"><small class="pull-right"><span class="glyphicon glyphicon-trash"></span></small><small><?php echo $f['term'] ?></small>&nbsp;</a>
                            <?php else: ?>
                                <a href="<?php echo url_for('compte_recherche', $argsTemplate) ?>" class="list-group-item" style="padding: 8px 8px"><span class="badge"><small><?php echo $f['count'] ?></small></span><small><?php echo $f['term'] ?></small>&nbsp;</a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</form>