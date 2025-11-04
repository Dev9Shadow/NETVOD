#!/bin/bash

# Script de création de la structure du projet NetVOD

echo "Création de la structure du projet NetVOD..."

# Création du répertoire principal
mkdir -p NetVOD
cd NetVOD

# Création des répertoires videos
mkdir -p videos/films
mkdir -p videos/series
mkdir -p videos/documentaires

# Création de la structure src/classes
mkdir -p src/classes/action
mkdir -p src/classes/video
mkdir -p src/classes/user
mkdir -p src/classes/catalogue
mkdir -p src/classes/liste
mkdir -p src/classes/interaction
mkdir -p src/classes/auth
mkdir -p src/classes/dispatch
mkdir -p src/classes/render
mkdir -p src/classes/repository

# Création du répertoire vendor
mkdir -p vendor

# Création des fichiers dans action/
touch src/classes/action/Action.php
touch src/classes/action/DefaultAction.php
touch src/classes/action/SigninAction.php
touch src/classes/action/SignoutAction.php
touch src/classes/action/AddUserAction.php
touch src/classes/action/DisplayCatalogueAction.php
touch src/classes/action/DisplayVideoAction.php
touch src/classes/action/CreateCompteNommeAction.php
touch src/classes/action/AddToListAction.php
touch src/classes/action/AddCommentAction.php
touch src/classes/action/ManageListsAction.php

# Création des fichiers dans video/
touch src/classes/video/Video.php
touch src/classes/video/Film.php
touch src/classes/video/Serie.php
touch src/classes/video/Documentaire.php
touch src/classes/video/Episode.php
touch src/classes/video/Saison.php

# Création des fichiers dans user/
touch src/classes/user/Utilisateur.php
touch src/classes/user/CompteNomme.php
touch src/classes/user/Profil.php
touch src/classes/user/InfosPaiement.php

# Création des fichiers dans catalogue/
touch src/classes/catalogue/Catalogue.php
touch src/classes/catalogue/Genre.php
touch src/classes/catalogue/TypePublic.php

# Création des fichiers dans liste/
touch src/classes/liste/Liste.php
touch src/classes/liste/ListePreferees.php
touch src/classes/liste/ListeVisionnees.php
touch src/classes/liste/ListeEnCours.php

# Création des fichiers dans interaction/
touch src/classes/interaction/Commentaire.php
touch src/classes/interaction/Avis.php

# Création des fichiers dans auth/
touch src/classes/auth/AuthnProvider.php
touch src/classes/auth/Authz.php

# Création des fichiers dans dispatch/
touch src/classes/dispatch/Dispatcher.php

# Création des fichiers dans render/
touch src/classes/render/Renderer.php
touch src/classes/render/HomeRenderer.php
touch src/classes/render/CatalogueRenderer.php
touch src/classes/render/VideoRenderer.php
touch src/classes/render/ProfilRenderer.php
touch src/classes/render/ListeRenderer.php

# Création des fichiers dans repository/
touch src/classes/repository/UtilisateurRepository.php
touch src/classes/repository/VideoRepository.php
touch src/classes/repository/CatalogueRepository.php
touch src/classes/repository/CommentaireRepository.php

# Création des fichiers à la racine
touch composer.json
touch composer.lock
touch create_tables.sql
touch db.config.ini
touch index.php
touch vendor/autoload.php

echo "Structure du projet NetVOD créée avec succès !"
echo ""
echo "Pour vérifier la structure, utilisez : tree NetVOD"
echo "Ou bien : find NetVOD -type f -o -type d | sort"