Compte rendu de la réunion du Sprint #2 le 24 et 25 Juillet 2014
================================================================

Les développements
------------------

### Général

* Mettre en avant le titre dans chacune des étapes

* Retirer le fond gris

### Étape Exploitation

* La modification des données de l'exploitation ne modifie pas les données de l'établissement (opérateur) elle impacte seulement la déclaration de revendication. A la fin de la période de saisie nous répercuterons les modifications effectués dans les DRev, ce qui permettra de détecter les changement de SIRET et Raison Sociale.

* Ajouter l'adresse email en visualisation avec un bouton modifier qui renvoi dans mon compte.

* Ajouter le numéro de téléphone portable ainsi que le numéro de fixe privé

### Étape Revendication

* Indiquer l'etat de la ligne et/ou des cases manquantes

### Étape Dégustation conseil

* Mettre une séparation avec le lieu de prélévements

* Le chai est uniquement représenté par son adresse.

* Ajouter un bouton "Modifier le chai" qui proposera une liste déroulante des chais de l'opérateur (Si celui-ci en a plusieurs)

* Par défaut on sélectionne le chai qui à la même adresse que l'exploitation

### Étape Lots

* Réduire la largeur des champs textes

* Le libellé du cépage "Pinot noir" doit être différencié entre "Pinoit noir rouge" et "Pinot noir rosé"

* Renommer "Assemblage" => "Asssemblage (Edel)"

* Si le nombre de lot n'est pas saisi pour un cépage issu de la DR => un point de vigilance sera levé à la validation

* Retirer le bouton "Étape précédente"

* Le bouton "Ajouter un produit +" => "+ Ajouter un produit"

* Réfléchir à une autre couleur pour le bouton ajouter

### Étape Contrôle externe

* La date de la dégustation d'AOC Alsace Bouteille doit être strictement supérieur à la date de dégustation d'AOC Alsace Cuve

* Cacher le bloc grand cru si aucun volume grand cru n'a été revendiqué

* Supprimer l'onglet "Prélèvement en bouteille", car il n'y a qu'un seul onglet

### Étape Validation

* Dans le tableau des prélèvements placer la colonne "A partir du" à la fin et ordonner les lignes comme celui de la saisie

### Pièce à joindre

* Informer l'utilisateur de l'adresse postal et e-mail d'envoi des pièces jointes : sur le PDF, les écrans de confirmation / visualisation et dans le mail.

* Le carnet de pressoir n'apparaît que si la superficie crémant est non nulle.

* La copie de la SV12 n'apparaît que pour les négociants

### PDF

#### Prélevements

* Le libellé de l'entête de colonne "Date (à partir de ..." devient "A parti du"

* Corrigé le titre du PDF : Déclaration de Revendicaton => "Déclaration de revendicat*i*on" 

* Mettre juste l'adresse du lieu de prélèvement.

* Remettre les prélèvements dans l'ordre de saisi

* Rosifier les cases lots des prélèvements bouteilles non vt/sgn

#### 2ème page (Le détail des lots)

* Le titre devient : "Déclaration des lots susceptibles d'être prélevés pour la dégustation conseil"

#### Entêtes

* Retirer la ligne "Commune de déclaration : ..."

* Pour les déclarations papier : préciser la mention "Déclaration *reçu* le ..." au lieu de "Déclaration *validé* le ..."

### Page d’accueil de la déclaration de Revendication

* Le bouton "Supprimer" => "Supprimer le brouillon"

Mockups de la déclaration de Revendication de Marc
--------------------------------------------------

### Page d’accueil

Mutualisation de l'espace avec celle de la DRev.

L'espace sera donc divisé en deux colonnes pour chacune des déclarations.

Les blocs d'un type de déclaration seront placés les uns en dessous des autres.

### Étape Exploitation

Faire attention au cas d'un dizaine de distillateur qui n'ont pas de CVI.

Pour résoudre le problème on ajoutera manuellement leurs comptes dans l'annuaire LDAP avec pour identifiant le SIREN/SIRET

Ce point est ré-abordé dans la partie "Etudes des données"

### Étape Revendication

Point bloquant si le volume a été saisi en litre (étudier les données pour trouver les volumes minimums)

Etudier le ratio poid / volume qui pourrait aussi faire l'objet d'un contrôle

### Étape Validation

* Point de vigilance si la date de fin est au dessus du 30 avril

* Point bloquant si le titre alcoolémique est inférieur strictement à 42

Étude des données (extravitis)
-------------------------------

Les opérateurs faisant une DRev sont ceux qui ont la mention "Vinificateur" comme attribut

Les opérateurs faisant une DRev Marc sont ceux qui ont la mention "Distillation" comme attribut

Un opérateur fait une DRev Récoltant si il a l'attribut "Producteur de raisin" et "Négociant" sinon.

Visiblement on est pas obligé d'utiliser les données de la table "Coordonnées" car les infos sont redondantes avec les infos EVV

Les opérateurs ayant des numéros EVV notés comme "Virtuel" sont visiblement des dégustateurs

Une dizaine d'opérateurs possèdent des numéros EVV étranges (ne commençant pas par "6"), la liste a été envoyée pendant la réunion 

Bien intégrer les champs : fixe privé, fixe bureau, fax, mobile et site internet.

Éplucher les données :

* La liste des opérateurs "Vinificateur" et "Producteur de raisin"

* La liste des opérateurs "Vinificateurs" et non "Producteur de raisin"

* La liste des distillateur

* Confronter ces listes aux comptes CIVA

* Confronter ces liste aux DRev précédentes pour comprendre la notion d'inactif

* Retrouver les numéros SIRET (introuvable pour 682370010 mais existant pour 6831801700 - N°Extravitis 1207)

Réunion utilisateurs
--------------------

### Étape revendication

* Précisez "VT / SGN inclus"

* "Volume total" => "Récolte total"

* "Volume sur place" => "Volume vinifié sur place"

* "DR" => "Déclaration de récolte"

### Étape Dégustation

* La séparation entre VTSGN et AOC Alsace doit être plus franche (le "trait souligné" ne suffit pas), idem pour l'étape Contrôle Externe

### Étape Lots :

* "Lots Hors VT / SGN" => "Nombre de lots (hors VT / SGN)"

* "Lots VT / SGN" => "Nombre de lots VT / SGN"

* Pour les lots AOC Alsace, mettre plus en valeur l'inclusion des AOC Alsace Communale et Lieu-dit.

### Étape Contrôle Externe

* Bien préciser "*Tous cepages et* appellations confondus" pour la saisi du nombre de lots VT / SGN

* Le sous titre "Prélévement après mise en bouteille" semble plus exact que "Prélèvement en bouteille"

### Étape de validation

* Précisez la mention "Cliquer sur chacun des points bloquants" en dessous du cadre des points bloquants.

* Ajouter les totaux en dessous du tableau de revendication

* Changer le picto avec un picto "document" du bouton "Prévisualiser"

### PDF

* Retirer les traits aux titres, ajouter un peu d'espace au dessus à la place 

### Accueil

Précisez que l'on peut quitter la déclaration à tout moment

Saisis des cépages pour les négociants
-------------------------------------------------

Étude des DRev avec beaucoup de produits : 50 produits max avec une moyenne de 20

* Pour les négociant on rajoute en option la saisi des volumes sur place hors usages industriels par cépages. La saisi sera organisée par onglet, un onglet par appellation.

Cartographie
------------------

* chaque zone définit les cépages conseillés autorisés ou interdits et des caractéristiques techniques

* des infos sur les sols sont également indiqués

* une interface "à la NosFinancesLocales.fr" semble adaptée

* la majorité des données sont éclatées dans une arborescence où un répertoires a été créé par commune (il y en a 119 communes)

* la publication de ces informations est conditionnée à la validation des choix de cépages par parcelle par l'INAO

Objectifs
----------

* Envoyer le compte rendu
* Corriger les interfaces
* Les échanges avec la DR
* Le nom de domaine mardi ou mercredi
* Les données (retrouver les siret, comparer les numeros CVI avec CIVA, ratio hl / kg pour la DRev marc)  Le fichiers final des opérateurs
* Retour pour export données DR etude d'extravitis
* Modification graphique d'interfaces (gris)
* Enregistrement automatique en ajax (au file de la saisi)
* Saisie coop et négoce des données par cépage
* Demander un devis a Typhon pour le certificat SSL
* Répartition des des points bloquants à la fin et au fil de la saisie
* Validation final de la Déclaration de revendication
* Déclaration de Marc
* Import des Déclarations  de revendication

Prochaine réunion
------------------------

* Au alentour du 15 septembre (Fixer une date précise)
* Fine tuning à mi-octobre
* Se pose la question de commencer la DI en septembre ou en octobre
