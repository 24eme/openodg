<?php use_helper('Compte') ?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li><a href="<?php echo url_for('societe_visualisation', array('identifiant' => $societe->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?></a></li>
    <li class="active"><a href="<?php echo url_for('etablissement_visualisation', array('identifiant' => $etablissement->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span> <?php echo $etablissement->nom; ?></a></li>
</ol>

<div class="row">
    <div class="col-xs-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-9">
                        <h4>Établissement n° <?php echo $etablissement->identifiant; ?></h4>
                    </div>
                    <div class="col-xs-3 text-muted text-right">
                        <div class="btn-group">
                            <a class="btn dropdown-toggle " data-toggle="dropdown" href="#">Modifier <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu()) ? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu()) ? 'javascript:void(0)' : url_for('etablissement_modification', $etablissement); ?>">Editer</a></li>
                                <li<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu())? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $etablissement->isSuspendu())? 'javascript:void(0)' : url_for('etablissement_switch_statut', array('identifiant' => $etablissement->identifiant)); ?>">Suspendre</a></li>
                                <li<?php echo ($societe->isSuspendu() || $etablissement->isActif())? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $etablissement->isActif())? 'javascript:void(0)' : url_for('etablissement_switch_statut', array('identifiant' => $etablissement->identifiant)); ?>">Activer</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="border-right: 6px solid #9f0038;">
                <h2><span class="<?php echo comptePictoCssClass($etablissement->getRawValue()) ?>"></span>  <?php echo $etablissement->nom; ?></h2>
                <hr/>
                <div class="row">
                    <div class="col-xs-5">
                        <div class="row">
                            <?php if ($etablissement->recette_locale && $etablissement->recette_locale->nom) : ?>
                                <div style="font-weight: bold; margin-bottom: 5px;" class="col-xs-4 text-muted">Recette locale :</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><a href="<?php echo url_for('societe_visualisation', SocieteClient::getInstance()->find($etablissement->recette_locale->id_douane)); ?>">
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
                                <?php if ($etablissement->region): ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Région : </div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->region; ?></div>
                                <?php endif; ?>
                                <?php if ($etablissement->exist('crd_regime') && $etablissement->crd_regime): ?>
                                    <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Régime CRD : </div>
                                    <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $etablissement->crd_regime; ?></div>
                                <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-xs-7" style="border-left: 1px solid #eee">
                        <?php include_partial('compte/visualisationAdresse', array('compte' => $etablissement->getMasterCompte())); ?>
                    </div>
                </div>
                <hr />
                <h5 style="margin-bottom: 15px; margin-top: 15px;" class="text-muted"><strong>Informations complémentaires</strong></h5>
                <?php include_partial('compte/visualisationTags', array('compte' => $etablissement->getMasterCompte())); ?>
                <hr />
                <h5 class="text-muted" style="margin-bottom: 15px; margin-top: 0px;"><strong>Chais</strong></h5>
                <?php if($etablissement->exist('chais')  && count($etablissement->chais)): ?>
                <table class="table table-condensed table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="col-xs-6">Adresse</th>
                            <th class="col-xs-5">Attributs</th>
                            <th class="col-xs-1">Partagé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($etablissement->chais as $chai): ?>
                            <tr>
                                <td><?php echo $chai->adresse ?><br />
                                <?php echo $chai->code_postal ?> <?php echo $chai->commune ?></td>
                                <td><?php echo implode("<br />", array_values($chai->getRawValue()->attributs->toArray(true, false))) ?></td>
                                <td><?php if($chai->partage): ?>Partagé<?php endif; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p class="text-muted">Aucun chai</p>
                <?php endif; ?>
                <?php if ($etablissement->commentaire) : ?>
                <hr />
                <h5 class="text-muted" style="margin-bottom: 15px; margin-top: 0px;"><strong>Commentaire</strong></h5>
                <pre><?php echo $etablissement->commentaire; ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xs-4">
        <?php include_component('societe', 'sidebar', array('societe' => $societe, 'activeObject' => $etablissement)); ?>
    </div>
</div>
