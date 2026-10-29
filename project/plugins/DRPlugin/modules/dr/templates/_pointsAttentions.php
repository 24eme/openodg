<?php if ($validation->hasErreurs()): ?>
    <h3>Points bloquants</h3>
    <div class="alert alert-danger" role="alert">
        <ul class="list-unstyled">
            <?php foreach ($validation->getPoints(DRValidation::TYPE_ERROR) as $controle): ?>
                <li>
                    <?php if ($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) : ?>
                        <span class="glyphicon glyphicon-warning-sign"></span>
                        <a class="alert-link" style="<?php if(isset($noLink)): ?>cursor: initial;<?php endif; ?>" <?php if(isset($noLink)): ?>onclick="return false;"<?php endif; ?> href="<?php echo url_for('setflash', array('url' => $controle->getRawValue()->getLien(), 'message' => $controle->getRawValue()->getInfo() . " : " .$controle->getRawValue()->getMessage(), 'type' => 'error')) ?>">
                             <?php echo $controle->getRawValue()->getInfo() ?></a>&nbsp;:&nbsp;<?php echo $controle->getRawValue()->getMessage() ?>
                    <?php elseif ($controle->getRawValue()->getLien()): ?>
                        <span class="glyphicon glyphicon-warning-sign"></span> <a style="<?php if(isset($noLink)): ?>cursor: initial;<?php endif; ?>" <?php if(isset($noLink)): ?>onclick="return false;"<?php endif; ?>  class="alert-link" href="<?php echo url_for('setflash', array('url' => $controle->getRawValue()->getLien(), 'message' => $controle->getRawValue()->getMessage(), 'type' => 'error')) ?>">
                            <span class="glyphicon glyphicon-warning-sign"></span> <?php echo $controle->getRawValue()->getMessage() ?>
                        </a>
                    <?php else: ?>
                        <span class="glyphicon glyphicon-warning-sign"></span> <?php echo $controle->getRawValue() ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<?php if ($validation->hasVigilances()): ?>
    <h3>Points de vigilance <a title="Les points de vigilance vous permettent de repérer d'éventuels oublis ou erreurs de saisie.  Cependant ils ne vous empêchent pas de valider votre déclaration." data-placement="auto" data-toggle="tooltip" class="btn-tooltip btn btn-lg"><span class="glyphicon glyphicon-question-sign"></span></a></h3>
        <div class="alert alert-warning" role="alert">
            <ul class="list-unstyled">
                <?php foreach ($validation->getPoints(DRValidation::TYPE_WARNING) as $controle): ?>
                    <li>
                        <?php if ($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) : ?>
                            <span class="glyphicon glyphicon-warning-sign"></span>
                            <a style="<?php if(isset($noLink)): ?>cursor: initial;<?php endif; ?>" <?php if(isset($noLink)): ?>onclick="return false;"<?php endif; ?>  class="alert-link" href="<?php echo url_for('setflash', array('url' => $controle->getRawValue()->getLien(), 'message' => $controle->getRawValue()->getInfo() . " : " .$controle->getRawValue()->getMessage(), 'type' => 'warning')) ?>">
                                <?php echo $controle->getRawValue()->getInfo() ?></a> : <?php echo $controle->getRawValue()->getMessage() ?>
                        <?php elseif ($controle->getRawValue()->getLien()): ?>
                            <span class="glyphicon glyphicon-warning-sign">
                            <a style="<?php if(isset($noLink)): ?>cursor: initial;<?php endif; ?>" <?php if(isset($noLink)): ?>onclick="return false;"<?php endif; ?>  class="alert-link" href="<?php echo url_for('setflash', array('url' => $controle->getRawValue()->getLien(), 'message' => $controle->getRawValue()->getMessage(), 'type' => 'warning')) ?>">
                                </span>  <?php echo $controle->getRawValue()->getMessage() ?>
                            </a>
                        <?php else: ?>
                            <span class="glyphicon glyphicon-warning-sign"></span>  <?php echo $controle->getRawValue() ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
