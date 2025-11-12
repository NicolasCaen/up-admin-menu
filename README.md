# Upcoder Admin Menu

Simplifiez votre expérience WordPress en personnalisant l'affichage de votre menu administratif. Ce plugin vous permet de masquer ou afficher les éléments du menu selon vos besoins, offrant une interface d'administration plus claire et efficace.

## Description

Upcoder Admin Menu est un plugin WordPress qui permet aux administrateurs de personnaliser leur interface d'administration en contrôlant la visibilité des éléments du menu. 

## Caractéristiques

- Interface simple et intuitive
- Personnalisation des menus visibles/cachés
- Sauvegarde des préférences par utilisateur
- Sélecteur dans la barre d’admin pour basculer de configuration
- Portées d’application: moi uniquement, tous, ou par rôle
- Possibilité de définir une configuration par défaut
- Propriété des configurations personnelles (visibles/choisissables uniquement par leur propriétaire)
- Lien « Réglages » visible uniquement par les administrateurs
- Option « Standard » qui n’applique aucune configuration globale/rôle/défaut
- Suppression et réorganisation (glisser‑déposer) des configurations
- Compatible avec les dernières versions de WordPress

## Installation

1. Téléchargez le plugin
2. Uploadez-le dans le dossier `/wp-content/plugins/`
3. Activez le plugin dans le menu 'Extensions' de WordPress

## Configuration minimale requise

- WordPress 5.2 ou supérieur
- PHP 7.2 ou supérieur

## Auteur

- GEHIN Nicolas
- Site web : [upcoder.fr](https://upcoder.fr)

## Licence

Ce plugin est sous licence [GNU GPL v2](https://www.gnu.org/licenses/gpl-2.0.html) ou ultérieure.

### Pourquoi la GPL ?

WordPress est distribué sous licence GPL v2+, et en tant qu'extension de WordPress, ce plugin doit également utiliser une licence compatible. La licence GPL garantit quatre libertés fondamentales :

1. La liberté d'utiliser le logiciel pour n'importe quel usage
2. La liberté d'étudier le fonctionnement du programme et de l'adapter à vos besoins
3. La liberté de redistribuer des copies
4. La liberté d'améliorer le programme et de rendre publiques ces améliorations

Copyright (C) 2024 GEHIN Nicolas

## Changelog

### 1.5.0
- Ajout des portées de configuration: `user` (moi uniquement), `all` (tous), `role` (par rôle)
- Possibilité de marquer une configuration comme « par défaut »
- Sécurité et UX: les configs `user` ne sont visibles/choisissables que par leur propriétaire
- Lien « Réglages » dans la barre d’admin visible uniquement par les administrateurs
- Choix « Standard » force l’absence de configuration globale/rôle/défaut
- UI: sélecteur, bouton d’application et icône réglages sur une seule ligne (barre d’admin)
- UI réglages: suppression et ordre des configurations par glisser‑déposer

### 1.4.1
- Déplacement des styles vers un fichier CSS compilé (`style.css`) avec source SCSS dans `assets/scss/style.scss`.
- Intégration d'icônes SVG pour l'action de changement et l'accès aux réglages dans la barre d'administration.
- Ajustements d'interface (espacement, couleurs de survol, padding) pour le sélecteur de configuration.
- Mise à jour du lien "Réglages" vers une icône dédiée.

### 1.4.0
- Ajout d'un sélecteur dans la barre d'administration pour choisir une configuration de menu personnalisée.
- Gestion de plusieurs configurations de menus enregistrées côté administrateur avec duplication rapide depuis l'interface d'options.
- Sauvegarde de la configuration sélectionnée par utilisateur via AJAX et rechargement automatique de l'administration.
- Améliorations visuelles du sélecteur (affichage flex, styles dédiés pour le bouton et la liste déroulante).

### 1.0.0
- Version initiale du plugin
