<?php if ($validation->hasErreurs()): ?>
    <h3>Points bloquants</h3>
    <div class="alert alert-danger" role="alert">
        <ul class="list-unstyled">
            <?php foreach ($validation->getPoints(ChgtDenomValidation::TYPE_ERROR) as $controle): ?>
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
