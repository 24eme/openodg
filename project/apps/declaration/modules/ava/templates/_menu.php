<ul class="nav nav-tabs">
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <li role="presentation" class="<?php if($active == "declarations"): ?>active<?php endif; ?>"><a href="<?php echo url_for('home'); ?>">Eléments declaratifs</a></li>
    <li role="presentation" class="<?php if($active == "facturation"): ?>active<?php endif; ?>"><a href="<?php echo url_for('facturation_declarant', $sf_user->getCompte()); ?>">Factures</a></li>
    <?php endif; ?>
</ul>