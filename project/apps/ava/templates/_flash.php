<?php if($sf_user->hasFlash('warning')): ?>
<p class="alert alert-warning">
    <span class="glyphicon glyphicon-warning-sign"></span> <?php echo $sf_user->getFlash('warning'); ?>
</p>
<?php endif; ?>

<?php if($sf_user->hasFlash('error')): ?>
<p class="alert alert-danger">
    <span class="glyphicon glyphicon-warning-sign"></span> <?php echo $sf_user->getFlash('error'); ?>
</p>
<?php endif; ?>

<?php if($sf_user->hasFlash('success')): ?>
<p class="alert alert-success">
    <span class="glyphicon glyphicon-check-ok"></span> <?php echo $sf_user->getFlash('success'); ?>
</p>
<?php endif; ?>

<?php if ($sf_user->hasFlash('notice')): ?>
    <p class="alert alert-success">
        <span class="glyphicon glyphicon-check-ok"></span> <?php echo $sf_user->getFlash('notice'); ?>
    </p>
<?php endif; ?>
