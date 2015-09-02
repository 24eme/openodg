

<div class="row row-margin">
    <table class="table table-hover table-bordered">
        <tr>
            <th>Jour</th>
            <th>Nb en attente de plannification</th>
            <th>Plannifié</th>
            <th>Réalisé</th>
            <th>Nb agents</th>
            <th></th>
        </tr>

        <?php foreach ($organisationJournee as $dateKey => $journee) : 
            $nbRendezvousPris = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS]))? 
                count($journee[RendezvousClient::RENDEZVOUS_STATUT_PRIS]) : '0';
            $nbRendezvousPlanifie = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]))? 
                count($journee[RendezvousClient::RENDEZVOUS_STATUT_PLANIFIE]) : '0';
            $nbRendezvousRealise = (isset($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]))? 
                count($journee[RendezvousClient::RENDEZVOUS_STATUT_REALISE]) : '0';
            ?>
            <tr>
                <td><?php echo $dateKey; ?></td>
                <td><?php echo $nbRendezvousPris; ?></td>
                <td><?php echo $nbRendezvousPlanifie; ?></td>
                <td><?php echo $nbRendezvousRealise; ?></td>
                <td>0</td>
                <td>ici bouton</td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
