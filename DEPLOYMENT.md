# GUÍA DE DESPLIEGUE - 000WebHost

**Desarrollado por Miguel Jesús Arias Cañete**

## Paso 1: Crear Cuenta en 000WebHost

1. Ve a [www.000webhost.com](https://www.000webhost.com)
2. Haz clic en "Sign Up Free"
3. Completa el registro con tu email

## Paso 2: Crear Sitio Web

1. En el panel, haz clic en "Create Website"
2. Elige "Empty Website"
3. Elige un nombre para tu sitio (ej: `mipetdocs`)
4. Tu URL será: `https://mipetdocs.000webhostapp.com`

## Paso 3: Crear Base de Datos

1. En el panel lateral, ve a "Tools" → "Database Manager"
2. Haz clic en "New Database"
3. Completa:
   - **Database name**: `id12345_petdocs` (000WebHost añade un prefijo automático)
   - **Database username**: Se genera automáticamente
   - **Password**: Elige una contraseña segura
4. Haz clic en "Create Database"
5. **IMPORTANTE**: Anota estas credenciales, las necesitarás

## Paso 4: Importar Esquema SQL

1. En "Database Manager", haz clic en "Manage" junto a tu base de datos
2. Se abrirá phpMyAdmin
3. Haz clic en tu base de datos en el panel izquierdo
4. Ve a la pestaña "SQL"
5. Abre el archivo `database/schema.sql` de tu proyecto
6. Copia TODO el contenido
7. Pégalo en el cuadro de texto de phpMyAdmin
8. Haz clic en "Go" (Ejecutar)
9. Deberías ver un mensaje de éxito

## Paso 5: Configurar Credenciales

1. Abre el archivo `api/config.php` en tu ordenador
2. Reemplaza las líneas 14-17 con tus credenciales de 000WebHost:

```php
define('DB_HOST', 'localhost');                    // Siempre es 'localhost'
define('DB_NAME', 'id12345_petdocs');              // Tu nombre de BD (con prefijo)
define('DB_USER', 'id12345_petdocsuser');          // Tu usuario de BD
define('DB_PASS', 'tu_contraseña_aqui');           // La contraseña que elegiste
```

3. Guarda el archivo

## Paso 6: Subir Archivos

### Opción A: File Manager (Recomendado para principiantes)

1. En el panel de 000WebHost, ve a "Tools" → "File Manager"
2. Verás una carpeta llamada `public_html`
3. **Elimina** todos los archivos que vienen por defecto (index.php, etc.)
4. Sube los archivos de la carpeta `public/` de tu proyecto:
   - Arrastra `index.html` a `public_html/`
   - Crea una carpeta `css` y sube `style.css`
   - Crea una carpeta `js` y sube `app.js`
   - Crea una carpeta `uploads` (vacía, para los documentos)
5. **Importante**: Crea una carpeta `api` **fuera** de `public_html`:
   - Haz clic en "Back" para salir de `public_html`
   - Crea una carpeta llamada `api`
   - Sube los archivos PHP (`config.php`, `pets.php`, `documents.php`)

### Opción B: FTP (Para usuarios avanzados)

1. Descarga FileZilla o cualquier cliente FTP
2. Usa estas credenciales (las encuentras en "Tools" → "FTP"):
   - **Host**: `files.000webhost.com`
   - **Username**: Tu nombre de usuario de 000WebHost
   - **Password**: Tu contraseña de 000WebHost
   - **Port**: 21
3. Sube los archivos como se indica en la Opción A

## Paso 7: Configurar Permisos

1. En File Manager, navega a `public_html/uploads`
2. Haz clic derecho → "Change Permissions"
3. Marca todas las casillas (777)
4. Haz clic en "Change"

## Paso 8: Ajustar Rutas en JavaScript

1. Abre `public_html/js/app.js`
2. En la línea 6, cambia:

```javascript
const API_BASE = '../api/'; // Ruta relativa
```

Por:

```javascript
const API_BASE = 'https://mipetdocs.000webhostapp.com/api/'; // Tu URL completa
```

(Reemplaza `mipetdocs` por el nombre de tu sitio)

## Paso 9: Probar la Aplicación

1. Abre tu navegador
2. Ve a `https://tunombre.000webhostapp.com`
3. Deberías ver la aplicación PetDocs
4. Prueba a:
   - Añadir una mascota
   - Subir un documento
   - Ver los documentos

## Solución de Problemas

### Error: "Database connection failed"
- Verifica las credenciales en `api/config.php`
- Asegúrate de haber importado el esquema SQL

### Error: "Failed to upload file"
- Verifica los permisos de la carpeta `uploads` (deben ser 777)

### Error: "Cannot find API"
- Verifica que la ruta en `app.js` sea correcta
- Asegúrate de que la carpeta `api` esté en el lugar correcto

### Las imágenes no se ven
- Verifica que la carpeta `uploads` exista
- Comprueba los permisos de escritura

## Notas Importantes

- 000WebHost tiene un límite de 300MB de almacenamiento
- El sitio se desactiva si no hay visitas en 30 días
- No uses esta plataforma para datos sensibles en producción

---

**¿Necesitas ayuda?** Revisa el archivo README.md para más información sobre el proyecto.

Desarrollado por **Miguel Jesús Arias Cañete** - 2025
