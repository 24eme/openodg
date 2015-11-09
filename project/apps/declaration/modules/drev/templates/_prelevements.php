<?php use_helper("Date"); ?>

<?php
foreach ($drev->getPrelevementsOrdered() as $prelevementsOrdered):
    ?>
    <div class="col-xs-6">
        <h3><?php echo $prelevementsOrdered->libelle; ?></h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center col-md-4">Produit</th>
                    <th class="text-center col-md-1">Lots</th>
                    <th class="text-center col-md-3">A partir du</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($prelevementsOrdered->prelevements as $prelevement): ?>
                    <tr>
                        <td class="text-center"><?php echo $prelevement->libelle_produit ?></td>
                        <td class="text-center" >
                            <?php echo (!$prelevement->total_lots) ? '-' : $prelevement->total_lots; ?></td>
                        <td class="text-center"><?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
                    </tr> 
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>
<?php if($drev->isNonConditionneurJustForThisMillesime()): ?>
    <div class="col-xs-6">
        <h3>Contrôle externe</h3>
        <p><em>Ne conditionne pas de volume pour ce millésime.</em></p>
    </div>
<?php endif; ?>

