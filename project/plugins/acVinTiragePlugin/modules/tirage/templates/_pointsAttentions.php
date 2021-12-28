<?php if ($validation->hasErreurs()): ?>
    <h3>Points bloquants</h3>
    <div class="alert alert-danger" role="alert">
        <ul>
            <?php foreach ($validation->getErreurs() as $controle): ?>
                <li>
                    <?php if ($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) : ?>
                                <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                                    <?php echo $controle->getRawValue()->getInfo() ?></a>&nbsp;:&nbsp;<?php echo $controle->getRawValue()->getMessage() ?> 
                    <?php elseif ($controle->getRawValue()->getLien()): ?>
                        <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                            <?php echo $controle->getRawValue()->getMessage() ?>
                        </a>
                    <?php else: ?>
                        <?php echo $controle->getRawValue() ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if ($validation->hasVigilances()): ?>
    <h3>Points de vigilance <a title="Les points de vigilance vous permettent de repérer d'éventuels oublis ou erreurs de saisie.  Cependant ils ne vous empêchent pas de valider votre déclaration." data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a></h3>
        <div class="alert alert-warning" role="alert">
            <ul>
                <?php foreach ($validation->getVigilances() as $controle): ?>
                    <li>
                        <?php if ($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) : ?>

                            <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                                <?php echo $controle->getRawValue()->getInfo() ?></a> : <?php echo $controle->getRawValue()->getMessage() ?>
                        <?php elseif ($controle->getRawValue()->getLien()): ?>
                            <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                                <?php echo $controle->getRawValue()->getMessage() ?>
                            </a>
                        <?php else: ?>
                            <?php echo $controle->getRawValue() ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>