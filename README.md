
#  Prueba de Despliegue con Docker Compose

##  Proxy inverso + Balanceo Nginx + Servicios PHP + Base de Datos
##  1. Descripción General

  

Este proyecto implementa un despliegue completo utilizando **Docker Compose**, compuesto por:

  

- Un **proxy inverso Nginx** que enruta peticiones según el dominio:

-  `www.freedomforLinares.com` → Servicio de Encuesta (balanceado en 3 réplicas)

-  `www.chiquito.com` → Servicio de Chistes

- Tres instancias del servicio **Encuesta**, balanceadas con pesos distintos.

- Un servicio **Chiste** escrito en PHP.

- Una base de datos **MySQL**, encargada de almacenar de forma global los votos de todas las réplicas.

- Redes, volúmenes y Dockerfiles independientes por servicio.

  

##  2. Estructura del Proyecto

 - chiste 
	 - Dockerfile 
	 - index.php 
 - db 
	 - Dockerfile 
	 - init.sql 
 - encuesta    
	 - Dockerfile 
	 - index.php 
 - nginx
	 - nginx.proxy.conf 
 - .dockerignore    
 - docker-compose.yml 
 - README.md

  

##  3. Configuración del Proxy (Nginx)

  

Archivo: `nginx/nginx.proxy.conf`

  

- Balanceo de carga hacia las 3 réplicas del servicio encuesta.

- Peso diferente para una de las réplicas.

- Virtual hosts separados por dominio.

  

Ejemplo:

  

~~~
upstream encuesta_servers {
    server encuesta1 weight=3 max_fails=3 fail_timeout=30s;
    server encuesta2 weight=1 max_fails=3 fail_timeout=30s;
    server encuesta3 weight=1 max_fails=3 fail_timeout=30s;
}

server {
    listen 80;
    server_name www.freedomforLinares.com;

    location / {
        proxy_pass http://encuesta_servers;
    }
}

server {
    listen 80;
    server_name www.chiquito.com;

    location / {
        proxy_pass http://chiste;
    }
}

server {
    listen 80 default_server;
    server_name _;
    return 404;
}
~~~

##  4. Servicios PHP
###  4.1 Servicio Encuesta

 - Implementado en PHP con **PDO + MySQL**.
 - Comparte un mismo backend de base de datos, lo que permite un contador global.
 - Muestra el `hostname` del contenedor para comprobar el balanceo.
 Dockerfile:
~~~
FROM php:7.4-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY index.php /var/www/html/
~~~
###  4.2 Servicio Chiste

 - Devuelve un chiste aleatorio en cada petición.
 - No requiere base de datos.
 Dockerfile:
~~~
FROM php:7.4-apache
COPY index.php /var/www/html/
~~~
##  5. Base de Datos (MySQL)
 Dockerfile:
~~~
FROM mysql:8.0
COPY ./init.sql /docker-entrypoint-initdb.d/
~~~
init.sql:
~~~
CREATE DATABASE IF NOT EXISTS voto;

USE voto;

CREATE TABLE IF NOT EXISTS votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    si INT DEFAULT 0,
    no INT DEFAULT 0
);

INSERT INTO votos (si, no) VALUES (0, 0);
~~~
Esto garantiza que todas las réplicas de Encuesta compartan los mismos datos.
## 6. docker-compose.yml

Incluye:

 - Red interna `backend`
 - Volumen persistente para MySQL
 - Balanceo de carga mediante nombres de servicio
 - build individual por servicio

## 7. Configuración local (Windows)
Hay que editar el archivo:
`C:\Windows\System32\drivers\etc\hosts`
Y añadir:
~~~
127.0.0.1 www.freedomforLinares.com
127.0.0.1 www.chiquito.com
~~~
## 8. Ejecución del Proyecto
Ejecutar en el terminal (ubicado en la carpeta donde está el docker-compose.yml):
`docker compose up --build -d`

## 9. Pruebas
###  9.1 Comprobar balanceo:

 1. Abrir `http://www.freedomforLinares.com`
 2. Recargar varias veces
 3. Observar el valor: `Servidor: <hostname>`
 
 La réplica con `weight=3` debe aparecer más veces.
###  9.2 Comprobar el chiste:
Visitar: `http://www.chiquito.com`
Cada recarga devuelve un chiste distinto.