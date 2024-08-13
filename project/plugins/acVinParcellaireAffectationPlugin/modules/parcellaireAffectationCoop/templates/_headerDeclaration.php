<div class="modal modal-page" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xxl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <a href="<?php echo url_for('parcellaireaffectationcoop_liste', ['id' => $coop]) ?>" class="close" aria-label="Close"><span aria-hidden="true">&times;</span></a>
                <h4 class="modal-title"><span class="glyphicon glyphicon-home"></span> <?php echo $declaration->declarant->getNom() ?> (<?php echo $declaration->declarant->cvi; ?>) - <?php $client = get_class($declaration->getRawValue())."Client"; echo $client::TYPE_LIBELLE ?>
            </div>
            <div class="modal-body">
