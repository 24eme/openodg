<?php use_helper('Float') ?>
<ol class="breadcrumb">
<?php if($sf_user->hasTeledeclaration()): ?>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire</a></li>
<?php else: ?>
    <li><a href="<?php echo url_for('parcellaire'); ?>">Parcellaire</a></li>
<?php endif; ?>
  <li><a href="<?php echo url_for('parcellaire_declarant', $etablissement); ?>">Parcellaire de <?php echo $etablissement->getNom() ?> (<?php echo $etablissement->identifiant ?>) </a></li>
  <li>Détails du Potentiel de Production</li>
</ol>

<h1>Potentiel de production</h1>
<?php foreach($potentiel->getProduits() as $produit): if ($produit->hasPotentiel()): ?>
<span class="pull-right">Caculé d'après <a href="<?php echo url_for( ($produit->parcellaire2refIsAffectation()) ? 'parcellaireaffectation_visualisation' : 'parcellaire_visualisation', $produit->getParcellaire2Ref()) ?>"><?php echo $produit->getParcellaire2Ref()->_id; ?></a></span>
<h2><?php echo $produit->getLibelle(); ?></h2>
<table class="table">
    <tr>
        <th>Condition</th>
        <th>Cepages concernés</th>
        <th class="text-right">Valeur</th>
        <th class="text-right"> </th>
        <th class="text-right">Limit</th>
        <th class="text-right"> </th>
        <th class="text-center">Résultat</th>
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
        printf("<td class='text-right'>%.4f</td>", $rule->getSomme());
        printf("<td class='text-right text-muted'>%d%s</td>", $rule->getPC()*100, '%');
        printf("<td class='text-right'>%s %.4f</td>", $rule->getSens(), $rule->getLimit());
        printf("<td class='text-right text-muted'>%d%s</td>", $rule->getLimitPC()*100, '%');
        echo "<td class='text-center'>";
        if ($rule->getResult()) {
            echo "OK";
        }elseif(!$rule->isBlockingRule()) {
            echo "LIMIT";
        }else{
            echo "NON";
        }
        echo "</td>";
        echo "</tr>";
    }
?>
</table>

<h4>Résultat</h4>
<table class="table" style="width: 30%">
<tr><th>Encepagement</th><td class="text-right"><?php echoFloat($produit->getSuperficieEncepagement(), 4); ?> ha</td></tr>
<tr><th>Superficie non revendicable</th><td class="text-right"><?php echoFloat($produit->getSuperficieEncepagement() - $produit->getSuperficieMax(), 4); ?> ha</td></tr>
<tr><th>Potentiel</th><th class="text-right"><?php echoFloat($produit->getSuperficieMax(), 4); ?> ha</th></tr>
</table>
<hr/>
<?php endif; ?>
<?php endforeach; ?>
