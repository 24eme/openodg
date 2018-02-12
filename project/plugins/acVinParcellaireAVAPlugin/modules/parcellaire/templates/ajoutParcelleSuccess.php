<div class="modal modal-page" id="" role="dialog" aria-labelledby="Ajouter un produit" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="" role="form" class="form-horizontal" novalidate="novalidate">
                <?php echo $form->renderHiddenFields(); ?>
                <?php echo $form->renderGlobalErrors(); ?>
                <div class="modal-header">
                <a href="<?php echo url_for("parcellaire_parcelles", array('id' => $parcellaire->_id, 'appellation' => $appellation)) ?>" class="close">&times;</a>
                    <h2 class="modal-title" id="myModalLabel">Ajouter la parcelle</h2>
                </div>
                <div class="modal-body">                    
                    <?php include_partial('parcellaire/parcelleForm', array('form' => $form,'appellation' => $appellation)); ?>                                   
                </div>
                <div class="modal-footer">
                    <a href="<?php echo url_for("parcellaire_parcelles", array('id' => $parcellaire->_id, 'appellation' => $appellation)) ?>" class="btn btn-danger btn pull-left">Annuler</a>
                    <button type="submit" class="btn btn-default btn pull-right">Ajouter la parcelle</button>
                </div>
            </form>
        </div>
    </div>
</div>