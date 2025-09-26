# Invitrosoft Database Project

Este proyecto contiene la estructura de base de datos para el sistema Invitrosoft. Incluye las tablas necesarias para gestionar usuarios, categorías, parámetros, reactivos y tipos de parámetros.

## Estructura del Proyecto

El proyecto está organizado de la siguiente manera:

```
invitrosoft-db
├── sql
│   ├── 01_create_tables.sql       # Script para crear las tablas necesarias
│   ├── 02_insert_sample_data.sql   # Script para insertar datos de ejemplo
│   └── README.md                   # Documentación sobre los scripts SQL
└── README.md                       # Documentación general del proyecto
```

## Instrucciones de Uso

1. **Ejecutar el script de creación de tablas**:
   - Abre tu cliente de base de datos SQL.
   - Ejecuta el archivo `01_create_tables.sql` para crear las tablas necesarias.

2. **Insertar datos de ejemplo**:
   - Después de crear las tablas, ejecuta el archivo `02_insert_sample_data.sql` para insertar datos de ejemplo en las tablas.

3. **Documentación**:
   - Consulta el archivo `sql/README.md` para obtener más detalles sobre los scripts SQL y su uso.
   - Este archivo también contiene información sobre la estructura de la base de datos.

## Contribuciones

Las contribuciones son bienvenidas. Si deseas mejorar este proyecto, por favor abre un issue o un pull request en el repositorio correspondiente.