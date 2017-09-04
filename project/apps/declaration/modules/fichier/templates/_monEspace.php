<?php use_helper('Date'); ?>

<?php if (!$sf_user->isAdmin()): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-sm-6 col-md-4 col-xs-12">
    <div class="block_declaration panel panel-danger">
        <div class="panel-heading">
            <h3 class="panel-title">Documents</h3>
        </div>
            <div class="panel-body">
                <p>Espace de téléversement de document pour le déclarant.</p>
                <div style="margin-top: 50px;">
                	<a class="btn btn-block btn-default" href="<?php echo url_for('upload_fichier', $etablissement) ?>">Ajouter un document</a>
                </div>
            </div>
    </div>
</div>
