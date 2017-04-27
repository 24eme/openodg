<ul class="nav nav-tabs <?php if($hideIfSmall): ?>hidden-xs hidden-sm<?php endif; ?>">
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <li role="presentation" class="<?php if($active == "declarations"): ?>active<?php endif; ?>"><a href="<?php echo url_for('admin'); ?>">Déclarations</a></li>
    <li role="presentation" class="<?php if($active == "facturation"): ?>active<?php endif; ?>"><a href="<?php echo url_for('facturation'); ?>">Facturation</a></li>
    <li role="presentation" class="<?php if($active == "tournees"): ?>active<?php endif; ?>"><a href="<?php echo url_for('degustation'); ?>">Dégustation</a></li>
    <?php endif; ?>
    <li role="presentation" class="<?php if($active == "constats"): ?>active<?php endif; ?>"><a href="<?php echo url_for('constats',array('jour' => date('Y-m-d'))); ?>">Constats</a></li>
    <?php if($sf_user->hasCredential(myUser::CREDENTIAL_ADMIN)): ?>
    <li role="presentation" class="<?php if($active == "export"): ?>active<?php endif; ?>"><a href="<?php echo url_for('export'); ?>">Export</a></li>
    <?php endif; ?>
    <li role="presentation" class="<?php if($active == "contacts"): ?>active<?php endif; ?>"><a href="<?php echo url_for('compte_recherche'); ?>">Contacts</a></li>
</ul>
