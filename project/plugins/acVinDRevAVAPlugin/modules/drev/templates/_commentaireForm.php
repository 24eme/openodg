<div class="col">
  <style>
    textarea{
      resize: none;
    }
  </style>
  <h3 class="">Commentaire interne</h3>
  <div class="col-xs-8 col-xs-offset-1">
      <?php echo $commentaireForm['commentaire']->render(array('class' => 'form-control input-rounded text-left', "")) ?>
  </div>
  <button name="comment-btn" class="btn btn-warning btn-sm" type="submit">Commenter&nbsp;<span class="eleganticon icon_chat"></span></button>
</div>
