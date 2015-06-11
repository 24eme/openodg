<ul class="nav nav-tabs">
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <li role="presentation" class="<?php if($active == "declarations"): ?>active<?php endif; ?>"><a href="<?php echo url_for('admin'); ?>">Déclarations</a></li>
    <li role="presentation" class="<?php if($active == "tournees"): ?>active<?php endif; ?>"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
    <li role="presentation" class="<?php if($active == "facturation"): ?>active<?php endif; ?>"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
    <?php endif; ?>
    <li role="presentation" class="<?php if($active == "contacts"): ?>active<?php endif; ?>"><a href="<?php echo url_for('compte_recherche'); ?>">Contacts</a></li>
</ul>