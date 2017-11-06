<?php use_helper('Compte') ?>
<ol class="breadcrumb">
    <li><a href="<?php echo url_for('societe') ?>">Contacts</a></li>
    <li class="active"><a href="<?php echo url_for('societe_visualisation', array('identifiant' => $societe->identifiant)); ?>"><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> <?php echo $societe->raison_sociale; ?></a></li>
</ol>

<div class="row">
    <div class="col-xs-8">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-9">
                        <h4><span class="<?php echo comptePictoCssClass($societe->getRawValue()) ?>"></span> Societe n° <?php echo $societe->identifiant; ?></h4>
                    </div>
                    <div class="col-xs-3 text-muted text-right">
                        <div class="btn-group">
                            <a class="btn dropdown-toggle " data-toggle="dropdown" href="#">Modifier <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li<?php echo ($societe->isSuspendu() || $societe->isSuspendu()) ? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $societe->isSuspendu()) ? 'javascript:void(0)' : url_for('societe_modification', $societe); ?>">Editer</a></li>
                                <li<?php echo ($societe->isSuspendu() || $societe->isSuspendu())? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $societe->isSuspendu())? 'javascript:void(0)' : url_for('societe_switch_statut', array('identifiant' => $societe->identifiant)); ?>">Archiver</a></li>
                                <li<?php echo ($societe->isSuspendu() || $societe->isActif())? ' class="disabled"' : ''; ?>><a href="<?php echo ($societe->isSuspendu() || $societe->isActif())? 'javascript:void(0)' : url_for('societe_switch_statut', array('identifiant' => $societe->identifiant)); ?>">Activer</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-body" style="border-right: 6px solid #9f0038;">
                <h2>
                	<?php echo $societe->raison_sociale; ?>
                	<?php if ($societe->getMasterCompte()->isSuspendu()): ?>
					    <span class="label label-default pull-right" style="padding-top: 0;"><small style="font-weight: inherit; color: inherit;"><?php echo $societe->getMasterCompte()->getStatutLibelle(); ?></small></span>
					<?php endif; ?>
                </h2>
                <hr/>
                <div class="row">
                    <div class="col-xs-5">
                        <div class="row">
                            <?php if ($societe->identifiant): ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Identifiant&nbsp;:</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $societe->identifiant; ?></div>
                            <?php endif; ?>
                            <?php if ($societe->code_comptable_client): ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Comptable&nbsp;:</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $societe->code_comptable_client; ?></div>
                            <?php endif; ?>
                            <?php if ($societe->siret): ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">SIRET&nbsp;:</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo formatSIRET($societe->siret); ?></div>
                            <?php endif; ?>
                            <?php if ($societe->code_naf): ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">Code naf&nbsp;:</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $societe->code_naf; ?></div>
                            <?php endif; ?>
                            <?php if ($societe->no_tva_intracommunautaire): ?>
                                <div style="margin-bottom: 5px;" class="col-xs-4 text-muted">TVA&nbsp;Intracom.&nbsp;:</div>
                                <div style="margin-bottom: 5px;" class="col-xs-8"><?php echo $societe->no_tva_intracommunautaire; ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-xs-7" style="border-left: 1px solid #eee">
                        <?php include_partial('compte/visualisationAdresse', array('compte' => $societe->getMasterCompte())); ?>
                    </div>
                </div>
                <hr />
                <h5 style="margin-bottom: 15px; margin-top: 15px;" class="text-muted"><strong>Informations complémentaires</strong></h5>
                <?php include_partial('compte/visualisationTags', array('compte' => $societe->getMasterCompte())); ?>
                <?php if ($societe->commentaire) : ?>
                <hr />
                <h5 class="text-muted" style="margin-bottom: 15px; margin-top: 0px;"><strong>Commentaire</strong></h5>
                <pre><?php echo html_entity_decode($societe->commentaire); ?></pre>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-xs-4">
        <?php include_component('societe', 'sidebar', array('societe' => $societe, 'activeObject' => $societe)); ?>
    </div>
</div>
