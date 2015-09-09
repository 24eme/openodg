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
                    <button class="btn btn-default btn-lg" type="submit">Se connecter</button>
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
                    count($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS]) : '0';
                $nbRendezvousPlanifie = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]))? 
                    count($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]) : '0';
                $nbRendezvousRealise = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]))? 
                    count($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]) : '0';
                ?>
                <tr class="<?php if($dateKey == date('Y-m-d')): ?>warning<?php endif; ?>">
                    <td><?php echo ucfirst(format_date($dateKey, "P", "fr_FR")); ?></td>
                    <td class="text-center"><?php echo $nbRendezvousPris; ?></td>
                    <td class="text-center"><?php echo $nbRendezvousPlanifie; ?></td>
                    <td class="text-center"><?php echo $nbRendezvousRealise; ?></td>
                    <td class="text-center">0</td>
                    <td class="text-center">
                        <?php if($dateKey <= date('Y-m-d')): ?>
                        <a href="<?php echo url_for('constats_planification_jour', array('jour' => $dateKey)); ?>" class="btn btn-upper btn-default <?php if($dateKey < date('Y-m-d')): ?>btn-default-step<?php endif; ?>">Planifier</a>
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

