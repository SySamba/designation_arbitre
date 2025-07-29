# Système de Désignation d'Arbitres

Un système complet de gestion et de désignation d'arbitres pour les compétitions sportives, développé en PHP et MySQL.

## Fonctionnalités Principales

### 🏆 Gestion des Matchs
- **Ajout de matchs** avec toutes les informations requises
- **Modification et suppression** des matchs
- **Désignation complète** des arbitres (Principal, Assistants, 4ème Officiel, Assesseur)
- **Contraintes automatiques** de disponibilité des arbitres

### 👨‍⚖️ Gestion des Arbitres
- **Ajout, modification et suppression** des arbitres
- **Informations complètes** : nom, prénom, adresse, email
- **Historique des arbitrages** par arbitre

### ⚽ Gestion des Équipes
- **Ajout, modification et suppression** des équipes
- **Informations** : nom et ville
- **Historique des matchs** par équipe

### 📧 Système de Publication
- **Option de publication** (Oui/Non) pour chaque match
- **Envoi automatique d'emails** aux arbitres désignés
- **Génération de rapports** de désignation

### 🔒 Contraintes de Désignation
- **Contrainte même jour** : Un arbitre ne peut pas être désigné pour deux matchs le même jour
- **Contrainte de rôles** : Un arbitre ne peut pas être sélectionné pour plusieurs rôles dans le même match
- **Avertissement équipes même jour** : Le système signale si un arbitre a déjà arbitré une équipe le même jour (avertissement seulement)
- **Avertissement historique équipes** : Le système signale si un arbitre a déjà arbitré une équipe dans le passé (avertissement seulement)
- **Vérification en temps réel** avant l'enregistrement du match

## Structure de la Base de Données

### Tables Principales

#### `ligues`
- Gestion des ligues (CNP par défaut)

#### `arbitres`
- Informations des arbitres (nom, prénom, adresse, email)
- Statut actif/inactif

#### `equipes`
- Informations des équipes (nom, ville)
- Statut actif/inactif

#### `matchs`
- Programmation complète des matchs
- Désignation de tous les arbitres
- Option de publication

#### `arbitrage_equipe`
- Historique des arbitrages par équipe
- Utilisé pour les contraintes

## Installation

### Prérequis
- Serveur web (Apache/Nginx)
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- XAMPP (recommandé pour le développement)

### Étapes d'installation

1. **Télécharger le projet**
   ```bash
   # Placer les fichiers dans votre dossier web
   # Exemple: C:/xampp/htdocs/gestion_arbitre/
   ```

2. **Configurer la base de données**
   - Ouvrir phpMyAdmin
   - Créer une nouvelle base de données nommée `gestion_arbitre`
   - Importer le fichier `database/schema.sql`

3. **Configurer la connexion**
   - Modifier le fichier `config/database.php` avec vos paramètres :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'gestion_arbitre');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

4. **Accéder au système**
   - Ouvrir votre navigateur
   - Aller à `http://localhost/gestion_arbitre/login.php`

## Authentification

### Identifiants de connexion
- **Email** : `sambasy837@gmail.com`
- **Mot de passe** : `admin123`

### Sécurité
- Toutes les pages du système sont protégées par authentification
- Session automatique avec déconnexion
- Protection des fichiers sensibles (config, classes)
- Redirection automatique vers la page de connexion

## Utilisation

### Page Principale (`index.php`)
- **Formulaire d'ajout de match** avec tous les champs requis
- **Liste des matchs** avec actions (modifier, télécharger, supprimer)
- **Validation automatique** des contraintes

### Gestion des Arbitres (`arbitres.php`)
- **Ajout d'arbitres** avec informations complètes
- **Modification et suppression** des arbitres
- **Historique des arbitrages** par arbitre

### Gestion des Équipes (`equipes.php`)
- **Ajout d'équipes** avec nom et ville
- **Modification et suppression** des équipes
- **Historique des matchs** par équipe

### Modification de Match (`modifier_match.php`)
- **Interface complète** de modification
- **Validation des contraintes** en temps réel
- **Préservation des données** existantes

### Téléchargement de Désignation (`telecharger_designation.php`)
- **Génération de rapport** HTML formaté
- **Informations complètes** du match et des arbitres
- **Design professionnel** pour impression

## Contraintes Implémentées

### Contrainte Même Heure
```sql
SELECT COUNT(*) as nb_matchs 
FROM matchs 
WHERE (arbitre_id = ? OR assistant_1_id = ? OR assistant_2_id = ? 
       OR officiel_4_id = ? OR assesseur_id = ?)
AND date_match = ?
AND heure_match = ?
AND id != ?
```

### Avertissement Même Jour
```sql
SELECT COUNT(*) as nb_matchs 
FROM matchs 
WHERE (arbitre_id = ? OR assistant_1_id = ? OR assistant_2_id = ? 
       OR officiel_4_id = ? OR assesseur_id = ?)
AND (equipe_a_id = ? OR equipe_b_id = ?)
AND date_match = ?
```

## Messages d'Erreur et Avertissements

Le système affiche des messages clairs en cas de violation des contraintes :

### ❌ Erreurs bloquantes :
- "Cet arbitre est déjà désigné pour un autre match le même jour"
- "L'arbitre [Nom] est sélectionné pour plusieurs rôles"

### ⚠️ Avertissements (non bloquants) :
- "Cet arbitre a déjà arbitré l'équipe [Nom] le même jour"
- "Cet arbitre a déjà arbitré l'équipe [Nom] X fois dans le passé"

**Vérification en temps réel :** Le système vérifie automatiquement les contraintes avant l'enregistrement du match et affiche les erreurs immédiatement. Les avertissements sont affichés avec une confirmation pour continuer ou annuler.

## Fonctionnalités Avancées

### Système d'Email
- **Envoi automatique** lors de la publication
- **Template d'email** professionnel
- **Informations complètes** du match

### Génération de Rapports
- **Format HTML** pour impression
- **Design professionnel** avec logo
- **Informations structurées** et lisibles

### Interface Utilisateur
- **Design moderne** avec Bootstrap 5
- **Interface responsive** pour tous les écrans
- **Navigation intuitive** entre les sections

## Sécurité

- **Requêtes préparées** pour éviter les injections SQL
- **Validation des données** côté serveur
- **Gestion des erreurs** avec try/catch
- **Échappement HTML** pour l'affichage

## Structure des Fichiers

```
gestion_arbitre/
├── config/
│   └── database.php              # Configuration de la base de données
├── classes/
│   ├── MatchManager.php          # Gestion des matchs et contraintes
│   ├── ArbitreManager.php        # Gestion des arbitres
│   └── EquipeManager.php         # Gestion des équipes
├── database/
│   └── schema.sql               # Schéma de la base de données
├── index.php                    # Page principale - Ajout de matchs
├── arbitres.php                 # Gestion des arbitres
├── equipes.php                  # Gestion des équipes
├── modifier_match.php           # Modification de matchs
├── telecharger_designation.php  # Téléchargement des rapports
└── README.md                    # Ce fichier
```

## Développement

### Ajouter de Nouvelles Contraintes
Pour ajouter de nouvelles contraintes, modifier la méthode `verifierDisponibiliteArbitre()` dans `classes/MatchManager.php`.

### Personnalisation de l'Interface
L'interface utilise Bootstrap 5 et Font Awesome. Modifier les fichiers CSS/JS pour personnaliser l'apparence.

### Ajouter de Nouvelles Fonctionnalités
Le système est modulaire et extensible. Ajouter de nouvelles classes dans le dossier `classes/` pour de nouvelles fonctionnalités.

## Support

Pour toute question ou problème :
1. Vérifier la configuration de la base de données
2. Consulter les logs d'erreur PHP
3. Vérifier que toutes les tables sont créées correctement
4. S'assurer que les permissions sont correctes

## Licence

Ce projet est fourni à des fins éducatives et de démonstration.

---

**Développé avec ❤️ pour la gestion d'arbitres sportifs** 