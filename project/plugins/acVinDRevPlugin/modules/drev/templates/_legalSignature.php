<div id="drevModalCgu" class="modal fade" role="dialog">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="<?php echo url_for('drev_legal_signature', array('identifiant' => $etablissement->identifiant)) ?>" method="post">
        <div class="modal-header">
          <h4 class="modal-title">Activation de votre espace DREV</h4>
        </div>
        <div class="modal-body">
                  <?php echo $legalSignatureForm->renderHiddenFields(); ?>
                  <?php echo $legalSignatureForm->renderGlobalErrors(); ?>
                  <br/>
                  <p>
                    InterLoire met à votre disposition des outils de simplification déclarative sur son portail professionnel sécurisé : « vinsvaldeloire.pro ».
                  </p>
                  <br/>
                  <p>
                    La dématérialisation de la déclaration de revendication (DREV) est disponible depuis le 15 octobre 2019.
                  </p>
                  <br/>
                  <p>
                    Cette déclaration permet de revendiquer les volumes de vins à commercialiser pour les opérateurs identifiés et habilités en AOP et IGP du Val de Loire. Ce service offre une procédure d’enregistrement dématérialisée simple et rapide grâce à une interface conviviale et à des éléments pré-renseignés.
                  </p>
                  <br/>
                  <p>
                     Pour activer cet espace de dématérialisation, vous devez prendre connaissance et accepter le contrat d’inscription à la télédéclaration de la DREV. Pour cela, <a href="/odg/data/cgu_drev_<?php echo sfConfig::get('sf_app'); ?>.pdf" style="text-decoration: underline;">cliquez ici</a>.
                  </p>
                  <br/><?php echo $legalSignatureForm['terms']->render(array('required' => 'true')); ?>&nbsp;&nbsp;<label for="drev_legal_signature_terms">J’accepte le <a href="/odg/data/cgu_drev_<?php echo sfConfig::get('sf_app'); ?>.pdf">contrat d’inscription</a> à la télédéclaration de la DREV.</label>
                  <br/>
        </div>
        <div class="modal-footer">
          <button id="popup_confirm" type="submit" class="btn btn-success" style="float: right;" ><span>Activer mon espace</span></button>
        </div>
      </form>
    </div>
  </div>
</div>
