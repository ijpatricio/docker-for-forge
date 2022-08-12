Prepare your repo locally
```
composer require ijpatricio/docker-for-forge --dev
php artisan dockerEnv:install
```

Configure Forge
Add site

Deploy script
```
cd /home/forge/FOLDER
git pull origin $FORGE_SITE_BRANCH

#Make sure `docker-compose.yml` is Git ignored in your project
cp docker-compose.prod.yml docker-compose.yml

docker-compose build

docker-compose up -d --remove-orphans

./Taskfile forgeCmd "php artisan migrate --force"
```
