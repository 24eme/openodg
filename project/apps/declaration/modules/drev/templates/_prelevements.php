<?php use_helper("Date"); ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">Prélèvements</h2>
    </div>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th class="text-left col-md-4">Prélévement</th>
                    <th class="text-center col-md-4">Produit</th>
                    <th class="text-center col-md-1">Lots</th>
                    <th class="text-center col-md-3">A partir du</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($drev->getPrelevementsByDate() as $prelevement): ?>
                <tr>
                    <td class="text-left"><?php echo $prelevement->libelle ?> <span class="text-muted"><?php echo $prelevement->libelle_produit_type ?></span></td>
                    <td class="text-center"><?php echo $prelevement->libelle_produit ?></td>
                    <td class="text-center"><?php echo $prelevement->total_lots ?></td>
                    <td class="text-center"><?php echo format_date($prelevement->date, "D", "fr_FR") ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
</div>
