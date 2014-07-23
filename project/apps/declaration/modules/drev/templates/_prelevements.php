<?php use_helper("Date"); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">Prélèvements</h2>
    </div>
        <table class="table table-striped table-condensed">
            <thead>
                <tr>
                    <th class="text-center col-md-3 small">A partir du</th>
                    <th class="text-center col-md-3 small">Prélévement</th>
                    <th class="text-center col-md-2 small">Type</th>
                    <th class="text-center col-md-3 small">Produit</th>
                    <th class="text-center col-md-1 small">Lots</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($drev->getPrelevementsByDate() as $prelevement): ?>
                <tr>
                    <td class="text-center"><?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
                    <td class="text-center"><?php echo $prelevement->libelle ?></td>
                    <td class="text-center"><?php echo $prelevement->libelle_produit_type ?></td>
                    <td class="text-center"><?php echo $prelevement->libelle_produit ?></td>
                    <td class="text-center"><?php echo $prelevement->total_lots ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>
