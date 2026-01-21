<ul>
    <?php foreach ($controles as $id => $data): ?>
        <li><a href="<?php echo url_for("controle_liste_manquements_controle", array('id' => $data->_id)) ?>"><?php echo $data->_id ?></a></li>
    <?php endforeach;?>
</ul>
