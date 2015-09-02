<?php use_helper("Date") ?>
<div class="page-header">
    <h2>Constats Opérateurs</h2>
</div>
<div class="row">    
    <div class="col-xs-12">        
        <div class="list-group">
            <?php foreach ($compte->getChais() as $keyChai => $chai): ?>
                <div class="list-group-item">
                    <?php include_partial('constats/rendezvousModification',array('chai' => $chai, 'form' => $formsRendezVous[$keyChai], 'numChai' => $keyChai + 1)); ?> 
                </div>
            <?php endforeach; ?>

        </div>
    </div>
</div>
<br/>
<h2>Les RDV</h2>
<table class="table table-hover table-bordered">
    <tr>
        <th>Chai</th>
        <th>Infos</th>
        <th>RDV<span class="icon-raisins size-36"></span></th>
        <th>RDV</th>
        <th>Résultat</th>  
    </tr>
    <?php foreach ($rendezvousDeclarant as $rendezvous) : ?>
        <tr>
            <td class="text-center"><?php echo "".$rendezvous->idchai+1; ?></td>
            <td><?php echo RendezvousClient::$rendezvous_statut_libelles[$rendezvous->statut]; ?></td>
            <td class="text-center">
                <a href="<?php echo url_for('rendezvous_modification', $rendezvous); ?>">
                <?php echo format_date($rendezvous->date, "P", "fr_FR"); ?> <?php echo str_replace(':', 'h', $rendezvous->heure); ?>
                </a>
            </td>
            <td>2nd</td>
            <td>Rés</td>
        </tr>
    <?php endforeach; ?>
</table>
