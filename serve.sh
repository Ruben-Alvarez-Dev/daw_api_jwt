#!/bin/bash

# Matar cualquier proceso que esté usando el puerto 8000
lsof -ti:8000 | xargs kill -9 2>/dev/null

# Iniciar el servidor Laravel
php artisan serve
