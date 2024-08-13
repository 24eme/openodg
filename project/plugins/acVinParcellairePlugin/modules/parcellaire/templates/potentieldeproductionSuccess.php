<h1>Potentiel de production</h1>
<?php foreach($table_potentiel as $produit => $table): ?>
<h2><?php echo $produit; ?></h2>
<table class="table">
    <tr>
        <th>Condition</th>
        <th>Cepages concernés</th>
        <th>Valeur</th>
        <th>Limit</th>
        <th>Résultat</th>
    </tr>
<?php
    foreach($table as $c => $t) {
        echo "<tr";
        if (!$t['res']) {
            echo ' class="danger"';
        }
        echo ">";
        echo "<td>$c</td>";
        echo "<td>".implode(', ', array_keys($t['cepages']->getRawValue()))."</td>";
        echo "<td>".$t['somme']."</td>";
        echo "<td>".$t['sens']." ".$t['limit']."</td>";
        echo "<td>";
        echo ($t['res']) ? 'OK' : 'Non';
        echo "</td>";
        echo "</tr>";
    }
?>
</table>

<h3>Potentiel : <?php echo $potentiel_de_production[$produit]; ?> ha</h3>
<h3>Superficie non revendicable : <?php echo $encepagement[$produit] - floatval($potentiel_de_production[$produit]); ?> ha</h3>
<?php endforeach; ?>
