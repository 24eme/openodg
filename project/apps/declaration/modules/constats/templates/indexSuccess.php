<?php use_helper("Date"); ?>
<?php use_javascript("constats.js?201504020331", "last") ?>
<?php include_partial('admin/menu', array('active' => 'constats')); ?>
<div class="row row-margin text-center">
    <h2>Prendre un rendez-vous</h2>
</div>
<div class="row row-margin">
    <form method="post" action="" role="form" class="form-horizontal">
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->renderGlobalErrors(); ?>
        <div class="form-group">
            <?php echo $form["login"]->renderError(); ?>
            <div class="col-xs-8 col-xs-offset-1">
                <?php
                echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote",
                    "placeholder" => "Prendre un RDV ou rechercher un opérateur",
                    "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                ));
                ?>
            </div>
            <div class="col-xs-2">
                <button class="btn btn-default btn-lg" type="submit">Valider</button>
            </div>
        </div>
    </form>
</div>
<div class="row row-margin text-center">
    <h2>Planifications sur 5 jours</h2>
</div>
<div class="row row-margin">
    <div class="col-xs-12">
        <form method="post" action="<?php echo url_for('constats',array('jour' => $jour)) ?>" role="form" class="form-horizontal" id="tourneesRecapDateForm">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th class="text-center col-xs-2">
                <div class="col-xs-6 pull-left">

                    Jour
                </div>
                <div class="col-xs-6">
                    <div class="date-picker" >
                        <?php echo $formDate['date']->render(array('class' => 'form-control date')); ?>
                        <div class="btn btn-default btn-default-step"><span class="glyphicon-calendar glyphicon"></span></div>
                    </div>
                </div>


                </th>
                <th class="text-center col-xs-2">En attente de planification</th>
                <th class="text-center col-xs-1">Planifié (non&nbsp;réalisé)</th>
                <th class="text-center col-xs-1">Réalisé</th>
                <th class="text-center col-xs-3">Agents</th>
                <th class="text-center col-xs-3" >&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($organisationJournee as $dateKey => $journee) :
                        $nbRendezvousPris = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS])) ?
                                count($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS]) : '';
                        $nbRendezvousPlanifie = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE])) ?
                                count($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]) : '';
                        $nbRendezvousRealise = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE])) ?
                                count($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]) : '';
                        ?>
                        <tr class="<?php if ($dateKey == date('Y-m-d')): ?>font-weight: bold<?php endif; ?>">
                            <td class="<?php if ($dateKey == date('Y-m-d')): ?>lead<?php endif; ?>"><?php if ($dateKey == date('Y-m-d')): ?>Aujourd'hui<?php else: ?><?php echo ucfirst(format_date($dateKey, "P", "fr_FR")); ?><?php endif; ?></td>
                            <td class="text-center <?php if ($dateKey == date('Y-m-d')): ?>lead<?php endif; ?>"><?php echo $nbRendezvousPris; ?></td>
                            <td class="text-center <?php if ($dateKey == date('Y-m-d')): ?>lead<?php endif; ?>"><?php echo $nbRendezvousPlanifie; ?></td>
                            <td class="text-center <?php if ($dateKey == date('Y-m-d')): ?>lead<?php endif; ?>"><?php echo $nbRendezvousRealise; ?></td>
                            <td class="text-center <?php if ($dateKey == date('Y-m-d')): ?>lead<?php endif; ?>"></td>
                            <td class="text-center">
                                <?php if ($dateKey >= date('Y-m-d')): ?>
                                    <a href="<?php echo url_for('constats_planifications', array('date' => $dateKey)); ?>" class=" btn btn-upper btn-default pull-left"><span class="glyphicon glyphicon-calendar"></span>&nbsp;Planifier</a>

                                    <a href="<?php echo url_for('constats_planification_jour', array('jour' => $dateKey)); ?>" class="btn btn-upper btn-default btn-default-step pull-right" ><span class="glyphicon glyphicon-list-alt"></span></a>

                                <?php else: ?>
                                    <a href="<?php echo url_for('constats_planification_jour', array('jour' => $dateKey)); ?>" class="btn btn-upper btn-default btn-default-step pull-right" ><span class="glyphicon glyphicon-list-alt"></span></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </form>
    </div>
</div>
<div class="row row-margin" style="padding-left: 20px;">
    <h3>Liste des rendez-vous non planifiés</h3>
</div>
<div class="row row-margin">
    <div class="col-xs-12">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="text-left col-xs-4">Jour/Heure</th>
                    <th class="text-left col-xs-2">Type de RDV</th>
                    <th class="text-center col-xs-6">Opérateur</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($rendezvousNonPlanifies as $dateIdRdvKey => $rendezvous) :
                    ?>
                    <tr class="<?php if ($rendezvous->date == date('Y-m-d')): ?>font-weight: bold<?php endif; ?>">

                        <td class="">
                            <?php if ($rendezvous->date == date('Y-m-d')): ?>Aujourd'hui<?php else: ?>
                                <?php echo ucfirst(format_date($rendezvous->date, "P", "fr_FR")); ?>
                            <?php endif; ?>
                            <?php if ($rendezvous->type_rendezvous == RendezvousClient::RENDEZVOUS_TYPE_RAISIN): ?>
                                &nbsp;à&nbsp;<?php echo str_replace(":", "h", $rendezvous->heure); ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center ">

                            <?php if ($rendezvous->type_rendezvous == RendezvousClient::RENDEZVOUS_TYPE_RAISIN): ?>
                                <span style="font-size: 20px;" class="icon-raisins"></span>   
                            <?php else : ?>
                                <span style="font-size: 20px;" class="icon-mouts"></span>   
                            <?php endif; ?>
                        </td>
                        <td class="text-center "><?php echo $rendezvous->raison_sociale . " (" . $rendezvous->cvi . ") " . $rendezvous->commune; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

