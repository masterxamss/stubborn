# - Instructions de déploiement

Le processus d'installation doit être effectué dans l'ordre indiqué dans les lignes suivantes.

#### **1\. Installez les dépendances nécessaires sur le serveur :**

Assurez-vous que les éléments suivants sont installés sur le serveur :

* **PHP** : Version 8.3.15  
* **Composer** : Gestionnaire de dépendances PHP  
* **MySQL** : Version 8.0.40  
* **Serveur web** : Apache ou Nginx  
* **Symfony CLI** : Outil CLI de Symfony

#### 

#### **2\. Clonage du Répertoire Git**

Clonez le dépôt Git sur le serveur 

| bash |
| :---- |
| `git clone https://github.com/masterxamss/stubborn.git  cd stubborn` |

#### 

#### **3\. Configuration des variables d'environnement**

Modifiez le fichier **.env** ou **.env.local** pour définir les variables nécessaires :

| env |
| :---- |
| `STRIPE_PUBLIC_KEY=your_stripe_public_key STRIPE_SECRET_KEY=your_stripe_secret_key DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/stubborn?serverVersion=8.0.32&charset=utf8mb4" MAILER_DSN=smtp://user:password@smtp.exemple.com:587` |

Pour les tests, configurez le fichier **.env.test** avec les paramètres suivants :

| env |
| :---- |
| `DATABASE_URL="mysql://app:!ChangeMe!@127.0.0.1:3306/stubborn?serverVersion=8.0.32&charset=utf8mb4"` |

#### 

#### **4\. Installation des dépendances**

Installez les dépendances PHP nécessaires en exécutant la commande suivante dans le répertoire du projet :

| bash |
| :---- |
| `composer install` |

#### 

#### **Note :** Cette étape :

* #### Configure automatiquement la base de données.

* #### Charge les données de test via les fixtures.

Deux utilisateurs seront créés par défaut :

* **Admin User**  
  * Email : `superuser@mail.com`  
  * Mot de passe : `#SuperUser_123`  
* **Utilisateur Standard**  
  * Email : `jonhdoe@mail.com`  
  * Mot de passe : `#JohnDoe_123`

#### **5\. Démarrer le serveur**

Lancez le serveur Symfony pour tester le projet :

| bash |
| :---- |
| `composer start` |

Cette commande :

* Démarre le serveur Symfony.  
* Exécute les tests fonctionnels, affichant leurs résultats dans la console.
