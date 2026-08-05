<?php echo Organisme::getInstance()->getNom() ?>


--

mailto:<?php echo (isset($email)) ? $email : Organisme::getInstance()->getEmail(); ?>

<?php if (Organisme::getInstance()->getTelephone()) {
    echo 'Tél. : ' . Organisme::getInstance()->getTelephone();
} ?>
