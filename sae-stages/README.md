# SAE Suivi de Stages — CY Tech

Application Laravel pour gérer les stages : comptes utilisateurs, offres, candidatures, conventions, suivi, évaluations, notifications et administration.

Ce guide explique quoi installer et comment lancer le site en local.

---

## 1. À installer sur le PC

Avant d'utiliser le site, installer les outils suivants.

### PHP 8.2 ou plus

Vérifier l'installation :

```bash
php -v
```

Si PHP n'est pas installé, installer PHP 8.2 ou plus avec les extensions courantes de Laravel :

- `pdo`
- `sqlite`
- `mbstring`
- `openssl`
- `fileinfo`
- `tokenizer`
- `xml`
- `ctype`
- `json`

Sur Windows, le plus simple est d'utiliser **XAMPP** ou **Laragon**.

### Composer

Composer sert à installer les dépendances PHP du projet.

Vérifier l'installation :

```bash
composer -V
```

Téléchargement :

```txt
https://getcomposer.org/download/
```

### SQLite ou MySQL

Le projet fonctionne simplement avec **SQLite** par défaut.

Pour une utilisation locale, SQLite suffit.

Si vous préférez MySQL, vous pouvez utiliser XAMPP, Laragon ou MariaDB.

### Git

Git permet de récupérer ou manipuler le projet.

Vérifier l'installation :

```bash
git --version
```

Téléchargement :

```txt
https://git-scm.com/downloads
```

---

## 2. Installation du projet

Ouvrir un terminal dans le dossier du projet, puis lancer :

```bash
composer install
```

Créer le fichier d'environnement si nécessaire :

```bash
cp .env.example .env
```

Générer la clé Laravel :

```bash
php artisan key:generate
```

Créer la base de données et ajouter les données de démonstration :

```bash
php artisan migrate:fresh --seed
```

Créer le lien de stockage :

```bash
php artisan storage:link
```

---

## 3. Lancer le site

Dans le dossier du projet :

```bash
php artisan serve
```

Ouvrir ensuite dans le navigateur :

```txt
http://127.0.0.1:8000
```

Si le port 8000 est déjà utilisé :

```bash
php artisan serve --port=8001
```

Puis ouvrir :

```txt
http://127.0.0.1:8001
```

---

## 4. Se connecter au site

Cliquer sur **Connexion**.

Utiliser un des comptes de démonstration ci-dessous.

| Profil | Email | Mot de passe | Rôle à choisir |
|---|---|---|---|
| Administrateur | `admin@sae.local` | `password` | `admin` |
| Étudiant | `etudiant@sae.local` | `password` | `etudiant` |
| Professeur | `prof@sae.local` | `password` | `professeur` |
| Jury | `jury@sae.local` | `password` | `jury` |
| Entreprise | `entreprise@sae.local` | `password` | `entreprise` |

Important : le rôle choisi doit correspondre au compte.

Exemple : pour `prof@sae.local`, choisir le rôle `professeur`.

---

## 5. Code de vérification par email

Par défaut, le code de vérification par email est **désactivé**.

Cela permet d'utiliser le site facilement en localhost, même si aucun email n'est configuré.

Donc, par défaut, la connexion demande seulement :

- email ;
- mot de passe ;
- rôle.

Pour activer ou désactiver l'A2F email :

1. se connecter en administrateur ;
2. aller dans **Admin > Paramètres** ;
3. activer ou désactiver **Authentification à deux facteurs par email**.

Si l'A2F est activée, le site envoie un code aléatoire à 6 chiffres par email.

---

## 6. Configurer les emails si nécessaire

Cette étape est facultative.

Elle est utile seulement si vous voulez envoyer de vrais emails pour :

- les codes de vérification ;
- les notifications ;
- les mots de passe oubliés.

Dans le fichier `.env`, configurer par exemple Gmail :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=adresse@gmail.com
MAIL_PASSWORD="mot_de_passe_application_google"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=adresse@gmail.com
MAIL_FROM_NAME="SAE Stages"
MAIL_TIMEOUT=10
NOTIFY_EMAIL_ENABLED=true
```

Important : avec Gmail, il faut utiliser un **mot de passe d'application Google**, pas le mot de passe normal du compte.

Après modification du `.env` :

```bash
php artisan config:clear
```

Tester l'envoi :

```bash
php artisan mail:test votre.email@example.com
```

---

## 7. Que peut-on faire sur le site ?

### Administrateur

- gérer les utilisateurs ;
- valider les entreprises ;
- gérer les formations ;
- affecter les tuteurs ;
- consulter les traces ;
- activer/désactiver l'A2F ;
- exporter les données.

### Étudiant

- consulter les offres ;
- candidater ;
- suivre son stage ;
- signer sa convention ;
- remplir son cahier de stage ;
- déposer des documents.

### Entreprise

- publier des offres ;
- gérer les candidatures ;
- suivre les stages ;
- signer les conventions ;
- ajouter des missions.

### Professeur

- suivre les stages affectés ;
- consulter les conventions ;
- ajouter des remarques ;
- signer les conventions.

### Jury

- consulter les stages à évaluer ;
- remplir la grille d'évaluation ;
- attribuer une note ;
- valider le stage.

---

## 8. Commandes utiles

Réinstaller complètement la base avec les données de démonstration :

```bash
php artisan migrate:fresh --seed
```

Lancer le site :

```bash
php artisan serve
```

Vider le cache de configuration :

```bash
php artisan config:clear
```

Tester les emails :

```bash
php artisan mail:test votre.email@example.com
```

Lancer les tests :

```bash
php artisan test
```

---

## 9. Problèmes fréquents

### Le site ne s'ouvre pas

Vérifier que cette commande est lancée :

```bash
php artisan serve
```

Puis ouvrir :

```txt
http://127.0.0.1:8000
```

### Erreur de rôle à la connexion

Le rôle choisi n'est pas le bon.

Exemple :

- `admin@sae.local` doit utiliser le rôle `admin` ;
- `etudiant@sae.local` doit utiliser le rôle `etudiant` ;
- `prof@sae.local` doit utiliser le rôle `professeur`.

### Aucun email n'est reçu

Vérifier que :

- l'A2F est activée dans **Admin > Paramètres** ;
- les paramètres SMTP sont corrects dans `.env` ;
- la commande `php artisan config:clear` a été lancée ;
- la commande `php artisan mail:test votre.email@example.com` fonctionne.

### Code de vérification invalide

Le code peut être invalide si :

- un nouveau code a été renvoyé ;
- le code a expiré ;
- ce n'est pas le dernier code reçu ;
- l'A2F a été désactivée.

---

## 10. Résumé très rapide

Pour lancer le site :

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Puis ouvrir :

```txt
http://127.0.0.1:8000
```

Compte admin :

```txt
Email : admin@sae.local
Mot de passe : password
Rôle : admin
```

L'A2F email est désactivée par défaut, donc aucun code email n'est nécessaire pour commencer.
