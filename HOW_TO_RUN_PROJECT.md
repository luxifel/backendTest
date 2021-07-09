## How to run project
1. From project root: `composer install`
2. `composer dump-autoload -o`
3. `cp .env.docker .env`
4. `cd .docker/`
5. `docker-compose up -d`
6. Connect to Mariadb Docker image:
   `docker exec -it $(docker ps -qf expose=3306) bash`
7. Import db to mariadb:
   `mysql -uroot -proot zssn < /usr/db.sql`
8. Once it's done, you can `exit` from machine
9. Import ./ZSSN.postman_collection.json file into your Postman app
10. If it's all done, you can try api calls from postman
11. You can connect to the database using this parameters:
    * DB_HOST=127.0.0.1
    * DB_PORT=3307
    * DB_DATABASE=zssn
    * DB_USERNAME=root
    * DB_PASSWORD=root
  