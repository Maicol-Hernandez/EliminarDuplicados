
# Dirección donde debe quedar ubicado el archivo, dentro del servidor
url: /var/www/html/manager

# Comando para ejecutar el script
comando: php index.php

# Instalación
La instalación de Composer es bastante simple. Siga estos pasos para instalar Composer:

Primero, actualice su servidor:
# sudo yum -y update

Cambie a un directorio temporal (tmp):
# cd /tmp

Instale Composer usando cURL:
# sudo curl -sS https://getcomposer.org/installer | php

Si quiere hacer que Composer sea accesible de forma global ejecute:
# mv composer.phar /usr/local/bin/composer


# EliminarDuplicados
