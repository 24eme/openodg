# Authentification CAS

OpenODG utilise le projet SSO CAS pour gérer l'authentification.

La documentation de ce projet open source est disponible ici : https://www.apereo.org/projects/cas

De très nombreux "client CAS" sont disponibles dans de nombreux languages et plateformes. La liste des clients directements supportés par le projet est disponible ici : https://apereo.github.io/cas/4.2.x/integration/CAS-Clients.html

## Comprendre le fonctionnement de CAS

Imaginons un CAS qui serait accessible depuis l'url **https://login.example.org/cas/**

Imaginons que l'application cherchant à savoir si un utilisateur est identifié est **site.example.org**. Pour s'authentifier l'utilisateur utilise *http://site.example.org/connexion/*.

### L'application redirige l'utilisateur vers le CAS

Pour que l'application sache si l'utilisateur est identifié il faut qu'elle le redirige (HTTP 302) vers l'url suivante :

    https://login.example.org/cas/login?service=http://site.example.org/connexion/

### Authentifié, l'utilisateur est redirigé vers l'application

Un fois authentifié, l'application CAS redirigera l'utilisateur vers la page spécifiée dans l'argument service en lui ajoutant un argument ticket :

    http://site.example.org/connexion?ticket=ST-Y-XXXXXXXXXXXXXXXX-cas

### L'application vérifie le ticket

Pour savoir si le ticket est valable, l'application doit maintenant elle même interroger le serveur CAS pour lui demander si le ticket et valable. 

Voici l'url à construire :

    https://login.example.org/cas/serviceValidate?ticket=ST-Y-XXXXXXXXXXXXXXXX-cas&service=http://site.example.org/connexion/

Attention, l'argument service doit être strictement le même que celui fourni par l'utilisateur lors du login.

Un XML est retourné avec la raison de l'erreur si le ticket est erroné ou des infos concernant l'utilisateur :

    <cas:serviceResponse xmlns:cas='http://www.yale.edu/tp/cas'>
         <cas:authenticationSuccess>
              <cas:user>login</cas:user>
              <cas:attributes>
                  <cas:email>email@example.org</cas:email>
                  <cas:nom>Nom Utilisateur</cas:nom>
              </cas:attributes>
         </cas:authenticationSuccess>
    </cas:serviceResponse>

