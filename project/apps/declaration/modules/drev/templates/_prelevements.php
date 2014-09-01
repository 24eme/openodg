<?php use_helper("Date"); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">Prélèvements</h2>
    </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-center col-md-3">Prélévement</th>
                    <th class="text-center col-md-2">Type</th>
                    <th class="text-center col-md-3">Produit</th>
                    <th class="text-center col-md-1">Lots</th>
                    <th class="text-center col-md-3">A partir du</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($drev->getPrelevementsByDate() as $prelevement): ?>
                <tr>
                    <td class="text-center"><?php echo $prelevement->libelle ?></td>
                    <td class="text-center"><?php echo $prelevement->libelle_produit_type ?></td>
                    <td class="text-center"><?php echo $prelevement->libelle_produit ?></td>
                    <td class="text-center"><?php echo $prelevement->total_lots ?></td>
                    <td class="text-center"><?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>
