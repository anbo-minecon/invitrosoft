# README for SQL Scripts

Este proyecto contiene los scripts SQL necesarios para crear y poblar la base de datos del sistema Invitrosoft. A continuación se detallan los archivos incluidos en la carpeta `sql`:

## Archivos

1. **01_create_tables.sql**: 
   - Este script contiene las instrucciones para crear las siguientes tablas:
     - `usuarios`: Almacena la información de los usuarios del sistema.
     - `categorias`: Contiene las categorías de reactivos.
     - `parametros`: Guarda los parámetros utilizados en el sistema.
     - `reactivos`: Registra los reactivos disponibles en el sistema.
     - `tipo_parametro`: Define los tipos de parámetros que se pueden utilizar.

2. **02_insert_sample_data.sql**: 
   - Este script inserta datos de ejemplo en las tablas creadas por el script anterior. Esto es útil para facilitar las pruebas y el desarrollo del sistema.

## Ejecución de Scripts

Para ejecutar los scripts SQL, siga estos pasos:

1. Abra su cliente de base de datos (por ejemplo, phpMyAdmin, MySQL Workbench, etc.).
2. Conéctese a su servidor de base de datos.
3. Seleccione la base de datos donde desea crear las tablas.
4. Ejecute el script `01_create_tables.sql` para crear las tablas.
5. Luego, ejecute el script `02_insert_sample_data.sql` para insertar los datos de ejemplo.

## Estructura de la Base de Datos

La base de datos está diseñada para gestionar la información relacionada con los usuarios, reactivos y parámetros del sistema, permitiendo una fácil administración y acceso a los datos necesarios para el funcionamiento del sistema Invitrosoft.