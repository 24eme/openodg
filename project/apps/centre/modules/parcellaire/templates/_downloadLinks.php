<div class="dropdown dropup center-block" style="width: 150px;">
    <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Télécharger...&nbsp;<span class="caret"></span></button>
    <ul class="dropdown-menu">
        <li><a href="<?php echo url_for('parcellaire_export_csv', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le CSV du parcellaire</a></li>
        <li><a href="<?php echo url_for('parcellaire_pdf', array('id' => $parcellaire->_id)); ?>" class="dropdown-item">Télécharger le PDF Douanier</a></li>
    </ul>
</div>
