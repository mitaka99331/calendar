# Calendar

## Requirements
- [Docker](https://docs.docker.com/engine/install/)
- [Docker compose](https://docs.docker.com/compose/install/linux/#install-using-the-repository)

## Steps to run the project
1. Run `docker compose up -d`
3. Run `docker exec calendar-php-1 php bin/console doctrine:migrations:migrate`
4. Run `docker exec calendar-php-1 npm install`
4. Run `docker exec calendar-php-1 npm run build`
