# Projet : Le Blog de Batman
## Installation
### Cloner le projet

``` git clone https://github.com/Kevins713/leblogdebatman.git```

### Modifier les paramètres d'environnement dans le fichier .env :
- Décommenter la ligne DATABASE mysql
- Mettre en commentaire la ligne DATABSE postgresql
- Changer user_db et password_db

```
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
```

### Déplacer le terminal dans le projet cloné

```
cd leblogdebatman
```

### Taper les commandes suivantes
 ```
 composer install
 symfony console doctrine:datase:create
 symfony console make:migration
 symfony console doctrine:migrations:migrate
 
 ```

### Démarrer le serveur web
``` 
symfony server:start
```