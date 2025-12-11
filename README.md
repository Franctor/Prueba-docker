# Prueba de Despliegue con Docker Compose  
## Proxy inverso + Balanceo Nginx + Servicios PHP + Base de Datos

---

## 1. Descripción General

Este proyecto implementa un despliegue completo utilizando **Docker Compose**, compuesto por:

- Un **proxy inverso Nginx** que enruta peticiones según el dominio:
  - <www.freedomforLinares.com> → Servicio de Encuesta (balanceado en 3 réplicas)
  - <www.freedomforLinares.com> → Servicio de Chistes
- Tres instancias del servicio **Encuesta**, balanceadas con pesos distintos.
- Un servicio **Chiste** escrito en PHP.
- Una base de datos **MySQL**, encargada de almacenar de forma global los votos de todas las réplicas.
- Redes, volúmenes y Dockerfiles independientes por servicio.

## 2. Estructura del Proyecto
chiste
    Dockerfile
    index.php
db
    Dockerfile
    init.sql
encuesta
    Dockerfile
    index.php
nginx
    nginx.proxy.conf
.dockerignore
docker-compose.yml
README.md

## 3. Configuración del Proxy (Nginx)

Archivo: `nginx/nginx.proxy.conf`

- Balanceo de carga hacia las 3 réplicas del servicio encuesta.
- Peso diferente para una de las réplicas.
- Virtual hosts separados por dominio.

Ejemplo:

~~~
nginx
upstream encuesta_servers {
    server encuesta1 weight=3;
    server encuesta2 weight=1;
    server encuesta3 weight=1;
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
    return 404;
}
~~~
