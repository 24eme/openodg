<?php if ($validation->hasErreurs()): ?>
    <h3>Points bloquants</h3>
    <div class="alert alert-danger" role="alert">
        <ul>
            <?php foreach ($validation->getPoints(TourneeValidation::TYPE_ERROR) as $controle): ?>
                <li>
                    <strong><?php echo $controle->getMessage(); ?>&nbsp;:</strong>
                    <?php if ($controle->getLien()) : ?>
                                <a class="alert-link" href="<?php echo $controle->getLien() ?>">
                    <?php endif;?>
                    <?php echo $controle->getInfo() ?>
                    <?php if ($controle->getLien()) echo '</a>'; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if ($validation->hasVigilances()): ?>
    <h3>Points de vigilance</h3>
    <div class="alert alert-warning" role="alert">
        <ul>
            <?php foreach ($validation->getPoints(TourneeValidation::TYPE_WARNING) as $controle): ?>
                <li>
                    <strong><?php echo $controle->getMessage(); ?>&nbsp;:</strong>
                    <?php if ($controle->getLien()) : ?>
                                <a class="alert-link" href="<?php echo $controle->getLien() ?>">
                    <?php endif;?>
                    <?php echo $controle->getInfo() ?>
                    <?php if ($controle->getLien()) echo '</a>'; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
