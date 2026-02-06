# Instrucciones Rápidas - Solución Base de Datos

## El Problema
✅ **Servidor funcionando correctamente**  
✅ **API funcionando correctamente**  
❌ **Base de datos vacía** (sin datos)

## Solución Rápida

### Paso 1: Subir Archivos Nuevos
Sube estos dos archivos a tu servidor InfinityFree en la carpeta `/backend/`:

1. `backend/diagnose.php` - Para verificar el sistema
2. `backend/seed_data.php` - Para agregar datos de ejemplo

### Paso 2: Verificar Base de Datos
Accede a: `https://petdocs-miguel.lovestoblog.com/backend/diagnose.php`

Esto te mostrará:
- ✅ Si las tablas existen
- ✅ Estado de la conexión
- ✅ Estructura de las tablas

### Paso 3: Poblar con Datos de Ejemplo
Accede a: `https://petdocs-miguel.lovestoblog.com/backend/seed_data.php`

Esto insertará 3 mascotas de ejemplo:
- Max (Labrador)
- Luna (Siamés)
- Rocky (Pastor Alemán)

### Paso 4: Verificar
Recarga: `https://petdocs-miguel.lovestoblog.com/`

Deberías ver las 3 mascotas de ejemplo.

---

## Si las Tablas No Existen

Accede a **phpMyAdmin** en InfinityFree y ejecuta:

```sql
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    species VARCHAR(50) NOT NULL,
    breed VARCHAR(100),
    birth_date DATE,
    owner_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pet_id INT NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pet_id) REFERENCES pets(id) ON DELETE CASCADE
);
```

Luego ejecuta el Paso 3 para poblar con datos.
