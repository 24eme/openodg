<h2>Cloturer un contrôle</h2>

<p>Opérateur


<?php foreach ($listeManquements as $codeRtm => $data): ?>
    <?php echo $data['libelle_point_de_controle'] . ' - ' . $data['libelle_manquement']; ?>
    <ul>
        <?php foreach ($data['parcelles'] as $parcelleId => $infoParcelle): ?>
            <?php echo $parcelleId; ?>
            <?php foreach ($infoParcelle as $key => $info): ?>
                <li><?php echo $key . ' => ' . $info; ?></li>
            <?php endforeach;?>
        <?php endforeach; ?>
    </ul>
<?php endforeach; ?>
