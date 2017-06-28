<?php use_helper('Date'); ?>

<?php if (!$sf_user->isAdmin()): ?>
    <?php return; ?>
<?php endif; ?>
<div class="col-xs-4">
    <div class="block_declaration panel panel-danger" style="background: #c56f6f none repeat scroll 0 0;">
        <div class="panel-heading" style="background: #c56f6f none repeat scroll 0 0; border-color: #6e6e6e;">
            <h3 style="color: #ffffff;">Documents<br /><br /></h3>
        </div>
            <div class="panel-body">
                <p style="color: #ffffff;">Espace de téléversement de document pour le déclarant.</p>
            </div>
            <div class="panel-bottom">
                <p>
                    <a class="btn btn-lg btn-block btn-default" href="<?php echo url_for('upload_fichier', $etablissement) ?>">Ajouter un document</a>
                </p>
            </div>

    </div>
</div>
