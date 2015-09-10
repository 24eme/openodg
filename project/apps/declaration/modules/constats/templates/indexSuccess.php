<?php use_helper("Date"); ?>
<?php include_partial('admin/menu', array('active' => 'constats')); ?>

<div class="row row-margin">
        <form method="post" action="" role="form" class="form-horizontal">
            <?php echo $form->renderHiddenFields(); ?>
            <?php echo $form->renderGlobalErrors(); ?>
            <div class="form-group">
                <?php echo $form["login"]->renderError(); ?>
                <div class="col-xs-8 col-xs-offset-1">
                    <?php echo $form["login"]->render(array("class" => "form-control input-lg select2 select2-offscreen select2autocompleteremote",
                                    "placeholder" => "Prendre un RDV ou rechercher un opérateur",
                                    "data-url" => url_for('compte_recherche_json', array('type_compte' => CompteClient::TYPE_COMPTE_ETABLISSEMENT))
                                    )); ?>
                </div>
                <div class="col-xs-2">
                    <button class="btn btn-default btn-lg" type="submit">Valider</button>
                </div>
            </div>

        </form>
</div>

<div class="row row-margin">
    <div class="col-xs-12">
        <table class="table table-striped table-bordered">
            <thead>
            <tr>
                <th class="text-left col-xs-3">Jour</th>
                <th class="text-center col-xs-2">En attente de planification</th>
                <th class="text-center col-xs-2">Planifié (non&nbsp;réalisé)</th>
                <th class="text-center col-xs-2">Réalisé</th>
                <th class="text-center col-xs-2">Agents</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($organisationJournee as $dateKey => $journee) : 
                $nbRendezvousPris = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS]))? 
                    count($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS]) : '';
                $nbRendezvousPlanifie = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]))? 
                    count($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]) : '';
                $nbRendezvousRealise = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]))? 
                    count($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]) : '';
                ?>
                <tr class="<?php if($dateKey == date('Y-m-d')): ?>font-weight: bold<?php endif; ?>">
                    <td class="<?php if($dateKey == date('Y-m-d')): ?>lead<?php endif ;?>"><?php if($dateKey == date('Y-m-d')): ?>Aujourd'hui<?php else: ?><?php echo ucfirst(format_date($dateKey, "P", "fr_FR")); ?><?php endif; ?></td>
                    <td class="text-center <?php if($dateKey == date('Y-m-d')): ?>lead<?php endif ;?>"><?php echo $nbRendezvousPris; ?></td>
                    <td class="text-center <?php if($dateKey == date('Y-m-d')): ?>lead<?php endif ;?>"><?php echo $nbRendezvousPlanifie; ?></td>
                    <td class="text-center <?php if($dateKey == date('Y-m-d')): ?>lead<?php endif ;?>"><?php echo $nbRendezvousRealise; ?></td>
                    <td class="text-center <?php if($dateKey == date('Y-m-d')): ?>lead<?php endif ;?>"></td>
                    <td class="text-center">
                        <?php if($dateKey >= date('Y-m-d')): ?>
                        <a href="<?php echo url_for('constats_planification_jour', array('jour' => $dateKey)); ?>" class="btn btn-upper btn-default btn-default-step <?php if($dateKey == date('Y-m-d')): ?>btn-lg<?php endif; ?>">Planifier</a>
                        <?php else: ?>
                        <a href="<?php echo url_for('constats_planification_jour', array('jour' => $dateKey)); ?>" class="btn btn-upper btn-default btn-default-step" >Voir</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

