# UNICEF — Application de collecte de dons

Application web de collecte de dons financiers pour lutter contre la famine au Cameroun et en Afrique subsaharienne.

## Stack

- **Backend** : PHP 8.4 natif + PDO
- **Frontend** : HTML5, CSS3, JavaScript natif (sans framework)
- **Base de données** :
  - **Replit (dev)** : SQLite — fichier `db/database.sqlite` créé automatiquement
  - **WampServer (prod locale)** : MySQL — importer `db/schema_mysql.sql` dans phpMyAdmin
  - Bascule via la constante `DB_DRIVER` dans `includes/config.php` (ou variable d'env `DB_DRIVER=mysql`)

## Workflow Replit

`Start application` : `php -S 0.0.0.0:5000 -t .` (port 5000, webview)

## Structure

```
/
├── index.php              # Page d'accueil (hero, stats, réalisations, partage)
├── don.php                # Formulaire de don en 3 étapes
├── paiement.php           # Suivi temps réel (compteur 8min + polling 3s)
├── merci.php              # Page de remerciement
├── api/
│   ├── donate.php         # POST : création du don + appel API paiement
│   ├── status.php         # GET  : vérification du statut (polling)
│   └── webhook.php        # POST : notification entrante du fournisseur
├── admin/                 # (à venir) interface administrateur
├── includes/
│   ├── config.php         # Configuration DB + API + contacts
│   ├── db.php             # Connexion PDO + auto-init SQLite
│   ├── functions.php      # Helpers (e, t, getStats, formatMoney, generateReference)
│   ├── lang.php           # Traductions FR/EN + bascule client-side
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/style.css
│   ├── js/{app.js, don.js, paiement.js}
│   └── img/{logo, countries}/
└── db/
    ├── database.sqlite    # SQLite local (auto-créé, gitignored)
    ├── schema_sqlite.sql
    └── schema_mysql.sql   # Pour import phpMyAdmin sous WampServer
```

## Fonctionnalités livrées

### Côté utilisateur ✅
- Page d'accueil : hero impactant, transparence financière (collecté/objectif/donateurs/%), barre de progression animée
- 12 pays avec photos locales d'enfants, drapeaux, descriptions
- Formulaire de don 3 étapes : type donateur (anonyme/identifié) → méthode (mobile/carte) → confirmation
- Paiement mobile : 16 pays africains, opérateurs filtrés par pays
- Paiement carte : Mastercard avec bouton "Copier"
- **Suivi temps réel** : countdown 8 min + polling 3s automatique, redirection auto si validé
- Page de remerciement avec récapitulatif et boutons de partage
- **Thème** sombre/clair (cookie, sans flash)
- **Traduction FR/EN instantanée** côté client (sans rechargement)
- Partage social avec libellés (WhatsApp, Facebook, Telegram, TikTok)
- Responsive mobile/tablette/desktop
- Favicon + logo UNICEF dans header/footer

### Côté admin ⏳ (à venir)
- Connexion sécurisée (`/admin/login.php`, identifiants : `admin` / `Admin@2024`)
- Dashboard KPIs, graphiques JS natif, historique avec filtres, export CSV, gestion objectif

## Sécurité & conventions

- Clé API du prestataire de paiement utilisée **uniquement côté serveur** (jamais exposée au navigateur)
- Le nom du prestataire n'apparaît nulle part côté utilisateur (mention "Paiement sécurisé" uniquement)
- Aucun lien vers l'admin sur le site public
- Toutes les requêtes SQL utilisent PDO préparé
- Mots de passe hashés avec `password_hash` (bcrypt)
- Pour passer en MySQL : `export DB_DRIVER=mysql DB_HOST=localhost DB_NAME=unicef_dons DB_USER=root DB_PASS=`
