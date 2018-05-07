<?php if ($validation->hasErreurs()): ?>
    <h3>Points bloquants</h3>
    <div class="alert alert-danger" role="alert">
        <ul>
            <?php foreach ($validation->getPoints(DrevValidation::TYPE_ERROR) as $controle): ?>
                <li>
                    <strong><?php echo $controle->getRawValue()->getMessage(); ?>&nbsp;:</strong>
                    <?php if ($controle->getRawValue()->getLien()) : ?>
                                <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                    <?php endif;?>
                    <?php echo $controle->getRawValue()->getInfo() ?>
                    <?php if ($controle->getRawValue()->getLien()) echo '</a>'; ?> 
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if ($validation->hasVigilances()): ?>
    <h3>Points de vigilance <a title="Les points de vigilance vous permettent de repérer d'éventuels oublis ou erreurs de saisie.  Cependant ils ne vous empêchent pas de valider votre déclaration." data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a></h3>
        <div class="alert alert-warning" role="alert">
            <ul>
                <?php foreach ($validation->getPoints(DrevValidation::TYPE_WARNING) as $controle): ?>
                    <li>
                    <strong><?php echo $controle->getRawValue()->getMessage(); ?>&nbsp;:</strong>
                    <?php if ($controle->getRawValue()->getLien()) : ?>
                                <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                    <?php endif;?>
                    <?php echo $controle->getRawValue()->getInfo() ?>
                    <?php if ($controle->getRawValue()->getLien()) echo '</a>'; ?> 
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>