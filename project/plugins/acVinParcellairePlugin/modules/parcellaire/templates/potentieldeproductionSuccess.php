<h1><a href="<?php echo url_for('parcellaire_visualisation', $parcellaire); ?>">Parcellaire</a> / Potentiel de production</h1>
<?php foreach($potentiel->table_potentiel as $produit => $table): ?>
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
    $disabled = false;
    foreach($table as $c => $t) {
        echo "<tr class='";
        if (!$t['res']) {
            if ($disabled){
                echo '';
            }elseif (isset($t['impact'])) {
                switch ($t['impact']) {
                    case 'blocker':
                        echo 'danger';
                        break;
                    case 'disabling':
                        echo 'info';
                        $disabled = true;
                        break;
                    case 'disabled':
                        echo '';
                        break;
                    default:
                        echo 'warning';
                        break;
                }
            } else{
                echo 'warning';
            }
        }else{
            if ($disabled) {
                echo '';
            }else{
                echo 'success';
            }
        }
        echo "'>";
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

<h3>Potentiel : <?php echo $potentiel->potentiel_de_production[$produit]; ?> ha</h3>
<h3>Superficie non revendicable : <?php echo $potentiel->encepagement[$produit] - floatval($potentiel->potentiel_de_production[$produit]); ?> ha</h3>
<?php endforeach; ?>
