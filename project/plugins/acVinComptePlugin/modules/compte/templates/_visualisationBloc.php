<div class="row">
    <div class="col-xs-10">
        <<?php if(isset($lead)): ?>h4<?php else: ?>h5<?php endif; ?> style="margin-top: 0;"><span class="<?php echo comptePictoCssClass($compte->getRawValue()) ?>"></span> <?php echo ($compte->nom_a_afficher) ? $compte->nom_a_afficher : $compte->nom; ?></<?php if(isset($lead)): ?>h4<?php else: ?>h5<?php endif; ?>>
        </div>
        <div class="col-xs-2 text-right">
            <div class="btn-group">
                <a class="btn btn-xs dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-pencil"></span> <span class="caret"></span></a>
                <ul class="dropdown-menu">
                    <li<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? ' class="disabled"' : '' ; ?>><a href="<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? 'javascript:void(0)' : url_for('compte_modification', $compte); ?>">Editer</a></li>
                    <li<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? ' class="disabled"' : '' ; ?>><a href="<?php echo ($compte->getSociete()->isSuspendu() || $compte->isSuspendu()) ? 'javascript:void(0)' : url_for('compte_switch_statut', array('identifiant' => $compte->identifiant)); ?>">Suspendre</a></li>
                    <li<?php echo ($compte->getSociete()->isSuspendu() || $compte->isActif()) ? ' class="disabled"' : '' ; ?>><a href="<?php echo ($compte->getSociete()->isSuspendu() || $compte->isActif()) ? 'javascript:void(0)' : url_for('compte_switch_statut', array('identifiant' => $compte->identifiant)); ?>">Activer</a></li>
                </ul>
            </div>
        </div>
        <?php if($compte->fonction): ?>
            <span class="col-xs-3">Fonction&nbsp;:</span><span class="col-xs-9"><?php echo $compte->fonction; ?></span>
        <?php endif; ?>
        <?php if ($compte->isSuspendu()): ?>
            <span class="label label-default"><?php echo $compte->statut; ?></span>
        <?php endif; ?>
        <?php if (!$compte->isSameAdresseThanSociete() || isset($forceCoordonnee)): ?>
            <div class="col-xs-12">
                <div class="row">
                    <div class="col-xs-3">
                        Adresse&nbsp;:
                    </div>
                    <div class="col-xs-9">
                        <address style="margin-bottom: 0;">
                            <?php echo $compte->adresse; ?><br />
                            <?php if ($compte->adresse_complementaire) : ?><?php echo $compte->adresse_complementaire ?><br /><?php endif ?>
                            <span <?php if($compte->insee): ?>title="<?php echo $compte->insee ?>"<?php endif; ?>><?php echo $compte->code_postal; ?></span> <?php echo $compte->commune; ?> <small class="text-muted">(<?php echo $compte->pays; ?>)</small>
                        </address>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!$compte->isSameContactThanSociete() || isset($forceCoordonnee)): ?>
            <div style="margin-top: 10px;" class="col-xs-12">
                <?php if ($compte->email) : ?>
                    <div class="row">
                        <div class="col-xs-3">
                            Email :
                        </div>
                        <div class="col-xs-9">
                            <a href="mailto:<?php echo $compte->email; ?>"><?php echo $compte->email; ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compte->telephone_perso) : ?>
                    <div class="row">
                        <div class="col-xs-3">
                            Tél. perso :
                        </div>
                        <div class="col-xs-9">
                            <a href="callto:<?php echo $compte->telephone_perso; ?>"><?php echo $compte->telephone_perso; ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compte->telephone_bureau) : ?>
                    <div class="row">
                        <div class="col-xs-3">
                            Tél. bureau :
                        </div>
                        <div class="col-xs-9"><a href="callto:<?php echo $compte->telephone_bureau; ?>"><?php echo $compte->telephone_bureau; ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compte->telephone_mobile) : ?>
                    <div class="row">
                        <div class="col-xs-3">
                            Tél.&nbsp;mobile&nbsp;:
                        </div>
                        <div class="col-xs-9">
                            <a href="callto:<?php echo $compte->telephone_mobile; ?>"><?php echo $compte->telephone_mobile; ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compte->fax) : ?>
                    <div class="row">
                        <div class="col-xs-3">
                            Fax :
                        </div>
                        <div class="col-xs-9">
                            <a href="callto:<?php echo $compte->fax; ?>"><?php echo $compte->fax; ?></a>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compte->exist('site_internet') && $compte->site_internet) : ?>
                    <div class="row">
                        <div class="col-xs-3">
                            Site Internet :
                        </div>
                        <div class="col-xs-9">
                            <a href="<?php echo $compte->site_internet; ?>"><?php echo $compte->site_internet; ?></a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
