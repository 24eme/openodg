<?php

class declarationActions extends sfActions {

    public function executeIndex(sfWebRequest $request) {
      $usurpation = $request->getParameter('usurpation',null);
      $login = $request->getParameter('login',null);
      if($usurpation && $login){
          $this->getUser()->usurpationOn($login, $request->getReferer());
      }
      $this->regionParam = $request->getParameter('region',null);
      if(!$this->regionParam && $this->getUser() && ($region = $this->getUser()->getTeledeclarationDrevRegion())){
        $regionRadixProduits = DrevConfiguration::getInstance()->getOdgProduits($region);
        if($regionRadixProduits){
            $params = $request->getGetParameters();
            $params['region'] = $region;
           return $this->redirect('declaration', $params);
        }
      }
      if($this->regionParam){  //&& $this->getUser()->getTeledeclarationDrevRegion()){
        $regionRadixProduits = DrevConfiguration::getInstance()->getOdgProduits($this->regionParam);
        if($regionRadixProduits){
          $request->setParameter('produits-filtre',$regionRadixProduits);
        }
      }


        $this->buildSearch($request);
        $nbResultatsParPage = 15;
        $this->nbResultats = count($this->docs);
        $this->page = $request->getParameter('page', 1);
        $this->nbPage = ceil($this->nbResultats / $nbResultatsParPage);
        $this->docs = array_slice($this->docs, ($this->page - 1) * $nbResultatsParPage, $nbResultatsParPage);

        if(class_exists("EtablissementChoiceForm")) {
            $this->form = new EtablissementChoiceForm(sfConfig::get('app_interpro', 'INTERPRO-declaration'), array(), true);
        } elseif(class_exists("LoginForm")) {
            $this->form = new LoginForm();
        }

        if (!$request->isMethod(sfWebRequest::POST)) {

            return sfView::SUCCESS;
        }

        $this->form->bind($request->getParameter($this->form->getName()));

        if(!$this->form->isValid()) {

            return sfView::SUCCESS;
        }

        return $this->redirect('declaration_etablissement', $this->form->getValue('etablissement'));
    }

    public function executeDoc(sfWebRequest $request) {
        $doc_id = $request->getParameter("id");
        $this->regionParam = $request->getParameter('region',null);

        if(!preg_match("/^([A-Z]+[1-2]{0,2})-([^-]+)/", $doc_id, $matches)) {

            return $this->forward404();
        }

        $doc_type = $matches[1];

        if ($doc_type == "DEGUSTATION") {
            return $this->redirect('degustation_visualisation', ['id' => $doc_id]);
        }

        $etablissement = EtablissementClient::getInstance()->find("ETABLISSEMENT-".$matches[2]);

        if(!$etablissement) {
           return $this->forward404();
        }

        if($doc_type == "DREV") {
            $params = array("id" => $doc_id);
            if($this->regionParam){
              $params = array_merge($params,array("region" => $this->regionParam));
            }
            return $this->redirect("drev_visualisation", $params);
        }

        if($doc_type == "DREVMARC") {

            return $this->redirect("drevmarc_visualisation", array("id" => $doc_id));
        }

        if(in_array($doc_type, array("PARCELLAIRE", "PARCELLAIRECREMANT", "INTENTIONCREMANT"))) {

            return $this->redirect("parcellaire_visualisation", array("id" => $doc_id));
        }

        if(in_array($doc_type, array("PARCELLAIREAFFECTATION", "PARCELLAIREAFFECTATIONCREMANT"))) {

            return $this->redirect("parcellaireaffectation_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "TIRAGE") {

            return $this->redirect("tirage_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "TRAVAUXMARC") {

            return $this->redirect("travauxmarc_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "PARCELLAIREIRRIGABLE") {

            return $this->redirect("parcellaireirrigable_visualisation", array("id" => $doc_id));
        }

        if($doc_type == "CHGTDENOM") {

            return $this->redirect("chgtdenom_visualisation", array("id" => $doc_id));
        }

        if ($doc_type == "CONDITIONNEMENT" ) {
            return $this->redirect("conditionnement_visualisation", array("id" => $doc_id));
        }

        if ($doc_type == "TRANSACTION" ) {
            return $this->redirect("transaction_visualisation", array("id" => $doc_id));
        }

        if ($doc_type == "SV12") {
            return $this->redirect('get_fichier', ['id' => $doc_id]);
        }

        if ($doc_type == "DR") {
            if (DRConfiguration::getInstance()->hasValidationDR()) {
                return $this->redirect('dr_visualisation', ['id' => $doc_id]);
            }

            return $this->redirect('get_fichier', ['id' => $doc_id]);
        }

        // Doc sans page de visu
        if($doc_type == "PARCELLAIREIRRIGUE") {

            return $this->redirect('pieces_historique', array('sf_subject' => $etablissement, 'categorie' => 'parcellaireirrigue'));
        }

        // Doc sans page de visu et ne remontant pas dans les documents
        if(in_array($doc_type, array("PARCELLAIREINTENTIONAFFECTATION"))) {

            return $this->redirect('declaration_etablissement', $etablissement);
        }

        return $this->forward404();
    }

    public function executeExport(sfWebRequest $request) {

        $this->regionParam = null;
        if($this->getUser() && $this->getUser()->getTeledeclarationDrevRegion()){
          $this->regionParam = $this->getUser()->getTeledeclarationDrevRegion();
        }

        if($this->regionParam){
          $regionRadixProduits = DrevConfiguration::getInstance()->getOdgProduits($this->regionParam);
          if($regionRadixProduits){
            $request->setParameter('produits-filtre',$regionRadixProduits);
          }
        }

        $this->buildSearch($request);
        $this->setLayout(false);
        $attachement = sprintf("attachment; filename=export_declarations_%s.csv", date('YmdHis'));
        $this->response->setContentType('text/csv');
        $this->response->setHttpHeader('Content-Disposition',$attachement );
    }

    public function executeEtablissementSelection(sfWebRequest $request) {
        $form = new EtablissementChoiceForm(sfConfig::get('app_interpro', 'INTERPRO-declaration'), array(), true);
        $form->bind($request->getParameter($form->getName()));
        if (!$form->isValid()) {

            return $this->redirect('declaration');
        }

        return $this->redirect('declaration_etablissement', $form->getEtablissement());
    }

    public function executeEtablissement(sfWebRequest $request) {
        $usurpation = $request->getParameter('usurpation',null);
        $login = $request->getParameter('login',null);
        if($usurpation && $login){
            $this->getUser()->usurpationOn($login, $request->getReferer());
        }

        $this->etablissement = $this->getRoute()->getEtablissement();

        $this->secureEtablissement($this->etablissement);


        if(class_exists("EtablissementChoiceForm")) {
            $this->etablissementChoiceForm = new SocieteEtablissementChoiceForm($this->etablissement);
            $this->form = new EtablissementChoiceForm(sfConfig::get('app_interpro', 'INTERPRO-declaration'), array('identifiant' => $this->etablissement->identifiant), true);
        }

        $this->campagne = $request->getParameter('campagne', ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent());
        $this->periode = preg_replace('/-.*/', '', $this->campagne);
        if(!$this->getUser()->hasDrevAdmin() && intval($this->campagne) < intval(ConfigurationClient::getInstance()->getCampagneManager(CampagneManager::FORMAT_COMPLET)->getCurrent()) - 1 ) {

            return $this->forwardSecure();
        }
    }

    protected function buildSearch(sfWebRequest $request) {

        $hasProduitsFilter = (class_exists("DrevConfiguration") && DrevConfiguration::getInstance()->hasOdgProduits());
        $this->query = $request->getParameter('query', array());
        if (!$this->query){
            $this->query = array();
        }
        $this->facets = array(
             "Type" => array(),
             "Statut" => array(),
             "Campagne" => array(),
             "Mode" => array()
        );
        $campagne_view = acCouchdbManager::getClient()
                 ->group(true)
                 ->group_level(2);
         if (isset($this->query['Type'])) {
             $campagne_view = $campagne_view->startkey(array($this->query['Type'], ''))->endkey(array($this->query['Type'], 'zzzzzz'));
         }
        $rows_campagne = $campagne_view->getView('declaration', 'tous')->rows;
        foreach($rows_campagne as $row) {
            if (!isset($this->facets["Campagne"][$row->key[DeclarationTousView::KEY_CAMPAGNE]])) {
                $this->facets["Campagne"][$row->key[DeclarationTousView::KEY_CAMPAGNE]] = 0;
            }
            $this->facets["Campagne"][$row->key[DeclarationTousView::KEY_CAMPAGNE]] += $row->value;
            if (!isset($this->facets["Type"][$row->key[DeclarationTousView::KEY_TYPE]])) {
                $this->facets["Type"][$row->key[DeclarationTousView::KEY_TYPE]] = 0;
            }
            $this->facets["Type"][$row->key[DeclarationTousView::KEY_TYPE]] += $row->value;
        }
        if (!isset($this->query['Campagne'])){
            ksort($this->facets["Campagne"]);
            $campagnes = array_keys($this->facets["Campagne"]);
            $this->query['Campagne'] = "".array_pop($campagnes);
        }

        if (isset($this->query['Type'])) {
            $types = array($this->query['Type']);
        }else{
            $types = array_keys($this->facets["Type"]);
        }

        $rows = array();
        foreach($types as $type) {
            $view = acCouchdbManager::getClient()
                    ->reduce(false);
            $view = $view->startkey(array($type, $this->query['Campagne'], ''))->endkey(array($type, $this->query['Campagne'], 'zzzzzzz'));
            $rows = array_merge($rows, $view->getView('declaration', 'tous')->rows);
        }
        foreach($this->facets['Campagne'] as $annee => $nb) {
            if ($annee == $this->query['Campagne']) {
                $this->facets['Campagne'][$annee] = 0;
            }else{
                $this->facets['Campagne'][$annee] = '?';
            }
        }
        $this->facets['Type'] = array();
        $facetToRowKey = array("Type" => DeclarationTousView::KEY_TYPE, "Campagne" => DeclarationTousView::KEY_CAMPAGNE, "Mode" => DeclarationTousView::KEY_MODE, "Statut" => DeclarationTousView::KEY_STATUT);

        if($hasProduitsFilter){
          $this->facets = array_merge($this->facets, array("Produit" => array()));
          $facetToRowKey = array_merge($facetToRowKey,array("Produit" => DeclarationTousView::KEY_PRODUIT));
        }

        $this->produitsFiltre = $request->getParameter('produits-filtre', null);

        $this->docs = array();
        $nbDocs = 0;
        $documentsCounter = array();
        $configurations = array();
        $this->produitsLibelles = array();
        foreach($rows as $row) {
            if($hasProduitsFilter){
              $not_in_result = false;
              if($this->produitsFiltre){
                $not_in_result = true;
                foreach ($this->produitsFiltre as $filtre) {
                  $filtre = str_replace("/","\/",$filtre);
                  if(preg_match("/".$filtre."/",$row->key[DeclarationTousView::KEY_PRODUIT])){
                    $not_in_result = false;
                    break;
                  }
                }
              }
              if($not_in_result){
                continue;
              }
              $campagne = $row->key[DeclarationTousView::KEY_CAMPAGNE].'-'.($row->key[DeclarationTousView::KEY_CAMPAGNE]+1);
              if(!array_key_exists($campagne,$configurations)){
                 $configurations[$campagne] = ConfigurationClient::getConfigurationByCampagne($campagne);
              }
              if($row->key[DeclarationTousView::KEY_PRODUIT] && !isset($this->produitsLibelles[$row->key[DeclarationTousView::KEY_PRODUIT]])) {
                  $this->produitsLibelles[$row->key[DeclarationTousView::KEY_PRODUIT]] =  $configurations[$campagne]->declaration->get($row->key[DeclarationTousView::KEY_PRODUIT])->getLibelleComplet();
              }
            }
            $nbDocs += $row->value;

            foreach($this->facets as $facetNom => $items) {
                $find = true;
                if($this->query) {
                    foreach($this->query as $queryKey => $queryValue) {
                        if($queryValue != $row->key[$facetToRowKey[$queryKey]]) {
                            $find = false;
                            break;
                        }
                    }
                }
                if(!$find) {
                    continue;
                }
                $facetKey = $facetToRowKey[$facetNom];
                if (!isset($this->facets[$facetNom])) {
                    $this->facets[$facetNom] = array();
                }
                if(isset($row->key[$facetKey]) && !isset($this->facets[$facetNom][$row->key[$facetKey]])) {
                    $this->facets[$facetNom][$row->key[$facetKey]] = 0;
                }
                if(isset($row->key[$facetKey]) && !isset($documentsCounter[DeclarationTousView::constructIdentifiantDocument($row,$row->key[$facetKey])])){
                  $this->facets[$facetNom][$row->key[$facetKey]] += 1;
                  $documentsCounter[DeclarationTousView::constructIdentifiantDocument($row,$row->key[$facetKey])] = 1;
                }
                $this->docs[$row->id] = $row;
            }
        }
        if($hasProduitsFilter){
          $tmp_docs = array();
          foreach ($this->docs as $key => $value) {
            $identifiantDocument = DeclarationTousView::constructIdentifiantDocument($value);
            if(!array_key_exists($identifiantDocument,$tmp_docs)) {
              if($this->produitsFiltre){
                $found = false;
                foreach ($this->produitsFiltre as $filtre) {
                  $filtre = str_replace("/","\/",$filtre);
                  if(preg_match("/".$filtre."/",$value->key[DeclarationTousView::KEY_PRODUIT])){
                    $found = true;
                    break;
                  }
                }
                if($found){
                  $tmp_docs[$identifiantDocument] = $value;
                }
              }else{
                $tmp_docs[$identifiantDocument] = $value;
              }
            }
          }
          $this->docs = $tmp_docs;
        }

        krsort($this->facets["Campagne"]);
        ksort($this->facets["Statut"]);
        ksort($this->facets["Type"]);
        krsort($this->facets["Mode"]);
        if($hasProduitsFilter){
            ksort($this->facets["Produit"]);
        }
        uasort($this->docs, function($a, $b) {

            return $a->key[DeclarationTousView::KEY_DATE] < $b->key[DeclarationTousView::KEY_DATE];
        });

    }

    protected function secureEtablissement($etablissement) {
        if (!EtablissementSecurity::getInstance($this->getUser(), $etablissement)->isAuthorized(array())) {

            return $this->forwardSecure();
        }
    }

    protected function forwardSecure() {
        $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

        throw new sfStopException();
    }

    protected function convertHashToProduitName($campagne,$hash){
      $configuration = ConfigurationClient::getInstance()->getConfigurationByCampagne($campagne);
      return $configuration->declaration->get($hash)->getLibelleComplet();
    }

}
