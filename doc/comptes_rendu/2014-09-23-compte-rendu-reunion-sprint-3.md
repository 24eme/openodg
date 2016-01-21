Compte rendu de la réunion du Sprint #3 le 23 Septembre 2014
================================================================

Besoins généraux
----------------

### Autres applications

#### Liste des dégustateurs
* Actuellement gére en parallèle dans extravitis et dans un tableur, le document qui fera foi sera le tableur
* Un dégustateur pourra être affecté à un ou plusieurs produit
* les dégustateurs GC sont affectés à 4 ou 5 GC => Chaque dégustateur pourra être associé à des hash produits (exemple AOC Alsace,  AOC Alsace Riesling, AOC Grand Cru Brand, AOC Lieu dit rouge, etc)

#### Module de gestion de contact

* Réfléchir où l'inclure (devis ou avec dégustation)
* Dégustateurs
* Opérateurs
* Syndicat viticole
* Agents de prélèvements
* Douanes ou contact avec d'autres administrations
* LDAP pour synchro avec messagerie

### Deadline

#### DRev

* Mise en production le 28 octobre pour ouverture en même temps que la déclaration de récolte
* Envoi de la DRev papier fin octobre
* Courrier entre le 15 octobre et 1er novembre dépendant des captures d'écran

#### Identification

* Peut attendre peut être faire un mini module de contact avec les infos des DI en parallèle de la dégustation
* Le vrai besoin est de garder la traçabilité des modifications d'un opérateur, le document en soit n'est pas important
* peut être démarré à partir du 15 octobre
* Il est important de pouvoir vérifier si un opérateur idéntifié est également habilité.

#### Dégustation

* à partir du 15 janvier on peut aller jusqu'à février au cas où.

#### Parcellaire

* 1er Mars

#### Facturation

* Avril

Les développements
------------------

### Global

* Préférence pour un jaune plus clair/"moins sale" pour les boutons d'ajout / modif / continuer
* Réléchir à une autre couleur pour le pyjama (zèbre)
* Réfléchir à une couleur moins fade pour les blocs gris
* Foncer un peu le texte en gris des titres de page (class="text-muted")
* Peut être mettre une fine ligne séparatrice entre la saisie et les boutons
* Étudier le fait de : encadrer le bas de page "A propos | Contact | Mentions" dans un cadre à part un peu plus bas
* Créer des utilisateurs de test sur la plateforme du CIVA/AVA à l'image du "7523700100" au CIVA : BOESCH Léon pour le récoltant et Wolfberger pour le négociant

### Accueil

* Mettre les titres en entier sur les blocs
* La campagne "2014-2015" doit être mise au format millésime "2014" => (Déclaration de revendication 2014)
* A priori on sépare les deux blocs historiques. (A cause du "Marc Gewurtz", à tester)
* Bloc de la DRev Marc bien prendre en compte la validation

### DRev

#### Récupération de la DR

* La récupération est obligatoire dans un premier temps. Si elle ne fonctionne pas ou si l'utilisateur refuse, on propose le débraillage
* Bug de récupération de la DR avec BOESH Léon (1207)
* Reprendre toute les appellations même s'il n'a pas de volume sur place (exemple : Boxler reprise du crémant qui n'est pas dans la déclaration de récolte en volume sur place)

#### Exploitation

* Identifier à l'import la récupération du numéro de téléphone privé en plus des numéros de portable, fax, fixe

#### Revendication et saisie cépage

* Ordonner la liste des appellations et des cépages par rapport à la configuration.
* Séparer les colonnes VT/SGN
* Gérer les cépages qui ne peuvent pas avoir de VT/SGN

#### Lots

* Libellé de l'Assemblage dans les Grand Cru : "Assemblage (Edel)" => "Assemblage"
* Désactiver les VT/SGN pour les lots idéalement, juste pour l'appellation Alsace Je ne sais plus si c'est de ça dont il s'agit, mais on avait décidé d'enlever la colonne pour les lots VT/SGN en Alsace et en GC, non ? Oui, c'est vrai mais nous comptions regarder tout de même si le travaille pour désactiver les VT/SGN uniquement pour l'AOC Alsace n'est pas trop compliqué.

#### Validation

* Marquer la différence entre les deux tableaux récap et prélèvement (peut être en retirant le gris du titre)
* Dans le récap mettre des cases de tableaux déroulantes pour les personnes qui ont rempli le cépage
* Piéce jointe : par défault la déclaration de récolte doit être coché
* Bug sur le lien du point bloquant vers les lots Grands Crus
* Ajouter le picto "Boostrap glyphicon glyphicon-repeat" à coté des liens des points bloquants/vigilances tout en maintenant la possibilité de cliquer sur la partie soulignée pour revenir sur la page bloquante
* Libellé "Cuve, fût ou bouteille" pour le libellé du VT/SGN dans le tableau de récap des prélèvements
* Le carnet de pressoir à joindre est conditionné au fait que l'appellation crémant ait une superficie non nulle et un volume total non nul (vente de moût + sur place)
* Ordonner la colonne prélèvement avec "Dégustation conseil" en premier

#### PDF

* Ajouter une troisième page pour la déclaration des cépages 

### DRev Marc

#### Exploitation

* Finalement on enregistre bien les informations dans l'établissement, (on se débrouillera pour retrouver la liste des modifications qu'il faudra injecter dans la DI) ?? Le but est de pouvoir sortir les modifications réalisées lors de la saisie des DRev, afin de mettre à jour la DI
* Mettre le bouton "Modifier" aligner avec les autres boutons (à tester)
* Mettre des blocs autour des formulaires de modifications

#### Revendication

* Champ date ajouter le même picto que dans la DRev
* On peut choisir n'importe quelle date (on n'est pas limité au Lundi)
* Mettre les "placeholder" à la fin des champs (sauf pour les dates) (placeholder = légende grise dans les champs textes lorsqu'il est vide)
* Quantité : "en Kg" (retirer "minimum") 
* "En ° (minimum 40°)" : "°"
* "Le volume d'alcool pur semble être exprimé en hl" : remplacer "hl" par "litre"

#### PDF

* Centrer toutes le texte des cases blanches
* Changer la couleur bleu par du rose pour différenciation

#### Reprise d'historique

* Reprendre l'historique DRevMarc

### Authentification

* Mettre en avant "Même login et mot de passe que civa"

* Mettre les éléments suivants avec la mention "Prochainement":
    * Biblio document
    * Carto
    * Décl. Identification
    * Décl. Affectation Parcellaire
    * Déclaration de tirage pour le crémant suivant la réponse que vous me donnerez ! Oui :-) Ok nous revenons vers toi en début de semaine prochaine sauf si tu as besoin avant ?

Objectifs
----------

### Développement

* Mise en préprod
* Eplucher les données opérateurs (cf précédent compte rendu) /!\ SIRET
* Reprise DRevMarc
* Modification graphique de l'interface (prioritaire pour test)
* Ticket restant du précédent compte rendu
* Ticketing du pad
* Modification et création de compte (avec le CIVA)
* Mettre en place l'intégration du CAS depuis celui du CIVA
* Moins prio pour la saisie papier et contrôle :
    * Gestion des pièces jointes
    * Validation partielle

### Administratif

* Acheter un certificat SSL
* Pointage des noms de domaine

Dates à retenir
---------------

* Présenter au moins les modifications d'interface à partir du mercredi 1 ou Jeudi 2 octobre au pire pour la semaine du 6
* Prochaine réunion le 9/10 octobre à Actualys : Fine tuning
* Ouverture de l'appli en même temps que la DR aux alentours du 28/31 octobre
* Prévoir une prochaine réunion vers fin octobre pour ouverture DRev / Mockup DI et/ou Dégustation se caler avec Dominique

A ne pas oublier pour l'ouverture
---------------------------------

* Envoyer la liste des codes de créations

