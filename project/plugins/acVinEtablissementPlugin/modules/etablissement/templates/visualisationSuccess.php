<?php use_helper('Compte') ?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li><a href="<?php echo url_for('societe_visualisation', array('identifiant' => $societe->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?></a></li>
    <li class="active"><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $etablissement->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span> <?php echo $etablissement->nom; ?></a></li>
</ol>

<div class="row">
    <div class="col-xs-8">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Établissements</h3></div>
            <div class="panel-body">
                <div class="row" style="margin-bottom: 10px; margin-top: 5px;">
                    <div class="col-xs-9">
                        <h2><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span>  <?php echo $etablissement->nom; ?> <small class="text-muted">(n° <?php echo $etablissement->identifiant; ?>)</small></h2>
                    </div>
                    <div class="col-xs-3 text-muted text-right">
                        <div class="btn-group">
                            <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">Modifier <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu()) ? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu()) ? 'javascript:void(0)' : url_for('etablissement_modification', $etablissement); ?>">Editer</a></li>
                                <li<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu())? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu())? 'javascript:void(0)' : url_for('etablissement_switch_statut', array('identifiant' => $etablissement->identifiant)); ?>">Suspendre</a></li>
                                <li<?php echo ($societe->isSuspendu() || $etablissement->isActif())? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $etablissement->isActif())? 'javascript:void(0)' : url_for('etablissement_switch_statut', array('identifiant' => $etablissement->identifiant)); ?>">Activer</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <hr style="margin-top: 10px; margin-bottom: 15px;" />
                <div class="row">
                    <div class="col-xs-5">
                        <h5 style="margin-bottom: 15px; margin-top: 0px;" class="text-muted"><strong>Identités</strong></h5>
                        <div class="row">
                            <?php if ($etablissement->recette_locale && $etablissement->recette_locale->nom) : ?>
                                <div style="font-weight: bold; margin-bottom: 5px;" class="col-xs-4 text-muted">Recette locale :</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><a href="<?php echo url_for('societe_visualisation', SocieteClient::getInstance()->find($etablissement->recette_locale->id_douane)); ?>"Société>
                                    <?php echo $etablissement->recette_locale->nom; ?></a></div>
                                <?php endif; ?>
                                <?php if ($etablissement->identifiant): ?>
                                    <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Identifiant&nbsp;:</div>
                                    <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->identifiant; ?></div>
                                <?php endif; ?>
                                <?php if ($etablissement->cvi): ?>
                                    <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">CVI : </div>
                                    <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->cvi; ?></div>
                                <?php endif; ?>
                                <?php if ($etablissement->no_accises): ?>
                                    <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">N°&nbsp;d'accise&nbsp;:&nbsp;</div>
                                    <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->no_accises; ?></div>
                                <?php endif; ?>
                                <?php if ($etablissement->carte_pro && $etablissement->isCourtier()) : ?>
                                    <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Carte professionnelle : </div>
                                    <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->carte_pro; ?></div>
                                <?php endif; ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Région : </div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->region; ?></div>
                                <?php if ($etablissement->exist('crd_regime') && $etablissement->crd_regime): ?>
                                    <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Régime CRD : </div>
                                    <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->crd_regime; ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-xs-7" style="border-left: 1px solid #eee">
                            <h5 style="margin-bottom: 15px; margin-top: 0px;" class="text-muted"><strong>Coordonnées</strong></h5>
                            <div class="row">
                                <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                    Adresse&nbsp;:
                                </div>
                                <div style="margin-bottom: 5px" class="col-xs-9">
                                    <address class="<?php if(!$smallBlock): ?><?php endif; ?>" style="margin-bottom: 0;">
                                        <?php echo $etablissement->adresse; ?><br />
                                        <?php if ($etablissement->adresse_complementaire) : ?><?php echo $etablissement->adresse_complementaire ?><br /><?php endif ?>
                                        <span <?php if($etablissement->insee): ?>title="<?php echo $etablissement->insee ?>"<?php endif; ?>><?php echo $etablissement->code_postal; ?></span> <?php echo $etablissement->commune; ?> <small class="text-muted">(<?php echo $etablissement->pays; ?>)</small>
                                    </address>
                                </div>
                            </div>
                            <?php if ($etablissement->email) : ?>
                                <div class="row">
                                    <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                        Email :
                                    </div>
                                    <div style="margin-bottom: 5px" class="col-xs-9">
                                        <a href="mailto:<?php echo $etablissement->email; ?>"><?php echo $etablissement->email; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($etablissement->telephone_perso) : ?>
                                <div class="row">
                                    <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                        Tél. perso :
                                    </div>
                                    <div style="margin-bottom: 5px" class="col-xs-9">
                                        <a href="callto:<?php echo $etablissement->telephone_perso; ?>"><?php echo $etablissement->telephone_perso; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($etablissement->telephone_bureau) : ?>
                                <div class="row">
                                    <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                        Tél.&nbsp;bureau&nbsp;:
                                    </div>
                                    <div style="margin-bottom: 5px" class="col-xs-9"><a href="callto:<?php echo $etablissement->telephone_bureau; ?>"><?php echo $etablissement->telephone_bureau; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($etablissement->telephone_mobile) : ?>
                                <div class="row">
                                    <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                        Tél.&nbsp;mobile&nbsp;:
                                    </div>
                                    <div style="margin-bottom: 5px" class="col-xs-9">
                                        <a href="callto:<?php echo $etablissement->telephone_mobile; ?>"><?php echo $etablissement->telephone_mobile; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($etablissement->fax) : ?>
                                <div class="row">
                                    <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                        Fax&nbsp;:
                                    </div>
                                    <div style="margin-bottom: 5px" class="col-xs-9">
                                        <a href="callto:<?php echo $etablissement->fax; ?>"><?php echo $etablissement->fax; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($etablissement->exist('site_internet') && $etablissement->site_internet) : ?>
                                <div class="row">
                                    <div style="margin-bottom: 5px;" class="col-xs-3 text-muted">
                                        Site&nbsp;Internet&nbsp;:
                                    </div>
                                    <div style="margin-bottom: 5px" class="col-xs-9">
                                        <a href="<?php echo $etablissement->site_internet; ?>"><?php echo $etablissement->site_internet; ?></a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <hr style="margin-top: 15px; margin-bottom: 10px;" />
                    <h5 style="margin-bottom: 15px; margin-top: 15px;" class="text-muted"><strong>Informations complémentaires</strong></h5>
                    <?php foreach ($contact->tags as $type_tag => $selected_tags) : ?>
                        <div style="margin-bottom: 10px;" class="row">
                            <div class="col-xs-3 text-muted"><?php echo ucfirst($type_tag) ?> :</div>
                            <div class="col-xs-9">
                                <?php foreach ($selected_tags as $t): ?>
                                    <?php $targs['tags'] = implode(',', array($type_tag . ':' . $t)); ?>
                                    <div class="btn-group">
                                        <a class="btn btn-sm <?php if($type_tag == "automatique"): ?>btn-link<?php endif; ?> <?php if($type_tag == "metier"): ?>btn-info<?php endif; ?> <?php if($type_tag == "manuel"): ?>btn-default<?php endif; ?>" href="<?php echo url_for('compte_search', $targs) ?>"><?php echo ucfirst(str_replace('_', ' ', $t)) ?></a>
                                        <?php $targs['tag'] = $t; ?>
                                        <?php $targs['q'] = $contact->identifiant ?>
                                        <?php if ($type_tag == 'manuel'): ?><a class="btn btn-sm btn-default" href="<?php echo url_for('compte_removetag', $targs) ?>"><span class="glyphicon glyphicon-trash"></span></a><?php endif; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($etablissement->commentaire) : ?>
                    <hr style="margin-top: 15px; margin-bottom: 10px;" />
                    <h5 class="text-muted" style="margin-bottom: 15px; margin-top: 0px;"><strong>Commentaire</strong></h5>
                    <pre><?php echo $etablissement->commentaire; ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xs-4">
        <div class="panel panel-default" style="margin-bottom: 10px;">
            <div class="panel-heading"><h3 class="panel-title">Société</h3></div>
            <div class="panel-body">
                <?php include_partial('compte/visualisationBloc', array('compte' => $societe->getContact(), 'societe' => $societe, 'forceCoordonnee' => true, 'lead' => true)); ?>
            </div>
        </div>
        <div class="carte" data-point='<?php echo json_encode(array_values($contact->getRawValue()->getCoordonneesLatLon())) ?>'  style="height: 180px; border-radius: 4px; margin-bottom: 10px;"></div>
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">Contacts</h3></div>
            <div class="list-group">
                <?php foreach ($interlocuteurs as $interlocuteurId => $compte) : ?>
                    <?php if(!$compte): continue; endif; ?>
                    <?php if ($compte->isSocieteContact() || $compte->isEtablissementContact()): ?><?php continue; ?><?php endif; ?>
                    <div class="list-group-item clearfix">
                        <?php include_partial('compte/visualisationBloc', array('compte' => $compte)); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
