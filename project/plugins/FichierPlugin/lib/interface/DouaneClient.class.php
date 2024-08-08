<?php
class DouaneClient extends acCouchdbClient
{

    public static function getInstance()
    {
      return acCouchdbManager::getClient('DR');
    }

    public function getDocumentsDouaniers($etablissement, $periode, $ext =  null, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        $etablissements = $etablissement->getMeAndLiaisonOfType(EtablissementClient::TYPE_LIAISON_METAYER);
        $fichiers = array();
        foreach($etablissements as $e) {
            $f = $this->getDocumentDouanierEtablissement($ext, $periode, $e, $hydrate);
            if ($f) {
                $fichiers[] = $f;
            }
        }
        return $fichiers;
    }
    public function getDocumentDouanierEtablissement($ext, $periode, $etablissement, $hydrate = acCouchdbClient::HYDRATE_DOCUMENT) {
        if (!$etablissement) {
            throw new sfException('Etablissement mandatory');
        }
        if (!$periode) {
            throw new sfException('periode mandatory');
        }

        $identifiant = $etablissement->identifiant;

        $choices = array("DR", "SV12", "SV11");
        if ($etablissement) switch ($etablissement->famille) {
            case EtablissementFamilles::FAMILLE_NEGOCIANT:
            case EtablissementFamilles::FAMILLE_NEGOCIANT_VINIFICATEUR:
                $choices = array("SV12", "SV11", "DR");
                break;
            case EtablissementFamilles::FAMILLE_COOPERATIVE:
                $choices = array("SV11", "SV12", "DR");
                break;
            case EtablissementFamilles::FAMILLE_PRODUCTEUR:
            case EtablissementFamilles::FAMILLE_PRODUCTEUR_VINIFICATEUR:
            case EtablissementFamilles::FAMILLE_AUTRE:
            default:
                break;
        }
        foreach($choices as $type) {
            $fichier = FichierClient::getInstance()->findByArgs($type, $identifiant, $periode);
            if (!$fichier) {
                continue;
            }
            return ($ext)? $fichier->getFichier($ext) : $fichier;
        }

        return null;
    }

}
