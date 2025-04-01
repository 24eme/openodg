<h1><a href="<?php echo url_for('parcellaire_visualisation', $parcellaire); ?>">Parcellaire</a> / Potentiel de production</h1>
<?php foreach($potentiel->getProduits() as $produit): if ($produit->hasPotentiel()): ?>
<h2><?php echo $produit->getLibelle(); ?></h2>
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
    foreach($produit->getRules() as $rule) {
        echo "<tr class='";
        if (!$rule->getResult()) {
            if ($disabled){
                echo '';
            } else{
                echo $rule->getCSSClass();
                if ($rule->isDisabling()) {
                    $disabled = true;
                }
            }
        }else{
            if ($disabled) {
                echo '';
            }else{
                echo 'success';
            }
        }
        echo "'>";
        echo "<td>".$rule->getLibelle()."</td>";
        echo "<td>".implode(', ', $rule->getCepages()->getRawValue())."</td>";
        echo "<td>".$rule->getSomme()."</td>";
        echo "<td>".$rule->getSens()." ".$rule->getLimit()."</td>";
        echo "<td>";
        echo ($rule->getResult()) ? 'OK' : 'Non';
        echo "</td>";
        echo "</tr>";
    }
?>
</table>

<h3>Potentiel : <?php echo $produit->getSuperficieMax(); ?> ha</h3>
<h3>Superficie non revendicable : <?php echo $produit->getSuperficieEncepagement() - $produit->getSuperficieMax(); ?> ha</h3>
<?php endif; ?>
<?php endforeach; ?>
