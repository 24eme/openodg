<?php if($validation->hasErreurs()): ?>
<h3>Points bloquants</h2>
<div class="alert alert-danger" role="alert">
    <ul>
    	<?php foreach ($validation->getPoints(DrevValidation::TYPE_ERROR) as $controle): ?>
    	<li>
                <?php if($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) :?>
                <?php echo $controle->getRawValue()->getMessage() ?> : <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
                <?php echo $controle->getRawValue()->getInfo() ?></a>
                <?php elseif($controle->getRawValue()->getLien()): ?>
                    <a CLASS="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
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
<?php if($validation->hasVigilances()): ?>
<h3>Points de vigilance</h2>
<div class="alert alert-warning" role="alert">
<ul>
	<?php foreach ($validation->getPoints(DrevValidation::TYPE_WARNING) as $controle): ?>
	<li>
        <?php if($controle->getRawValue()->getLien() && $controle->getRawValue()->getInfo()) :?>
        <?php echo $controle->getRawValue()->getMessage() ?> : <a class="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
        <?php echo $controle->getRawValue()->getInfo() ?></a>
        <?php elseif($controle->getRawValue()->getLien()): ?>
            <a CLASS="alert-link" href="<?php echo $controle->getRawValue()->getLien() ?>">
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