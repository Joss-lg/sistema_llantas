# Sistema Llantas

Sistema web para administrar una operación de venta de llantas con varias sucursales.
Incluye inventario, punto de venta, flujo de caja, clientes, empleados, permisos y reportes.

---

## Tecnologías

| Componente | Versión usada en desarrollo |
|---|---|
| Laravel | 12.61.0 |
| PHP | 8.3.33 (mínimo 8.2) |
| Base de datos | MySQL 8 |
| Node.js | 24.15.0 |
| npm | 11.12.1 |
| Front-end | Blade + Tailwind CSS 3 + Alpine.js |
| Compilador de assets | Vite 7 |

---

## Instalación desde cero

Sigue los pasos **en orden**. No uses `composer setup`: ese script corre las migraciones
antes de que puedas configurar tu base de datos, y falla.

### 1. Clonar el repositorio

```bash
git clone https://github.com/Joss-lg/sistema_llantas.git
cd sistema_llantas
```

### 2. Instalar las dependencias de PHP

```bash
composer install
```

### 3. Crear la base de datos

Entra a tu MySQL y crea la base vacía:

```sql
CREATE DATABASE laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Puedes usar otro nombre; solo recuerda ponerlo en el paso siguiente.

### 4. Crear y configurar el archivo .env

Copia la plantilla:

```bash
copy .env.example .env
```

(En Linux o Mac: `cp .env.example .env`)

Abre `.env` y revisa estas líneas:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

Pon tu usuario y contraseña de MySQL. Si tu MySQL no tiene contraseña, deja
`DB_PASSWORD=` vacío.

**Opcional pero recomendado:** define la contraseña del usuario administrador antes
de correr los seeders:

```
ADMIN_PASSWORD=TuContrasenaSegura
```

Si la dejas vacía, el seeder genera una contraseña aleatoria y la muestra en pantalla
**una sola vez**. Anótala en ese momento.

### 5. Generar la llave de la aplicación

```bash
php artisan key:generate
```

### 6. Crear las tablas

```bash
php artisan migrate
```

Si quieres ver qué va a hacer antes de ejecutarlo:

```bash
php artisan migrate --pretend
```

### 7. Cargar los datos iniciales

```bash
php artisan db:seed
```

Esto crea los roles, los permisos, las sucursales y el usuario administrador.
**Este paso es obligatorio**: sin él no hay con qué iniciar sesión.

### 8. Instalar las dependencias de JavaScript

```bash
npm install
```

### 9. Compilar los estilos

```bash
npm run build
```

**Este paso no se puede saltar.** Ver la sección "Sobre los estilos" más abajo.

### 10. Levantar el servidor

```bash
php artisan serve
```

Abre `http://127.0.0.1:8000` en el navegador.

---

## Sobre los estilos (importante)

El CSS se carga con `@vite` y se compila en la carpeta `public/build`, que **está
ignorada por Git**. Es decir, esa carpeta no viaja en el repositorio.

Esto significa que **tienes que correr `npm run build`**:

- Al instalar el proyecto por primera vez.
- Al clonarlo en otra computadora.
- Al subirlo a un servidor.
- **Cada vez que una vista use clases de Tailwind que antes no existían.**

Si te saltas este paso, la pantalla se ve rota: sin colores, sin espaciado, el texto
todo amontonado.

### Mientras desarrollas

En lugar de compilar a mano cada vez, puedes dejar Vite escuchando cambios:

```bash
npm run dev
```

Deja esa terminal abierta y abre otra para `php artisan serve`.

---

## Cómo está organizado el sistema

### Sucursales y permisos

El sistema trabaja con **varias sucursales**. La regla principal es:

- El rol **Administrador General** ve y opera todas las sucursales.
- **Cualquier otro usuario** (incluidos los administradores de cada sucursal) ve
  únicamente la suya, según la columna `sucursal_id` de su cuenta.

Los permisos se guardan **por usuario**, no por rol, en la tabla `permiso_user`.
El método `User::tienePermiso()` siempre deja pasar al Administrador General; a los
demás les revisa esa tabla.

Las rutas protegidas usan el middleware `permiso`, que valida **por nombre de ruta**.
Por eso los permisos registrados en `ModulosSeeder` tienen que coincidir exactamente
con los nombres de las rutas en `routes/web.php`.

### Sucursales que crea el seeder

| ID | Nombre |
|---|---|
| 1 | Administración General |
| 2 | Chalco |
| 3 | Atlanta |
| 4 | Las Torres |
| 5 | Valle de Chalco |

### Usuario administrador

El seeder crea `admin@llantas.com` con el rol Administrador General y la sucursal
Administración General. La contraseña sale de `ADMIN_PASSWORD` en el `.env`, o se
genera aleatoria si esa variable está vacía.

---

## Dónde vive cada cosa

### El stock NO está en la tabla productos

Esto confunde seguido. La tabla `productos` guarda los datos del producto
(marca, medida, descripción, costo, precio público, precio mayoreo, estado).

**Las existencias viven en la tabla `stock_sucursal`**, con el modelo `StockSucursal`:
`producto_id`, `sucursal_id`, `cantidad`, `stock_minimo`.

Un mismo producto tiene una cantidad distinta en cada sucursal.

### Movimientos de inventario

La tabla `movimientos_inventario` (modelo `MovimientoInventario`) registra cada
entrada y salida: `producto_id`, `sucursal_id`, `usuario_id`, `tipo`, `cantidad`,
`motivo`, `costo_unitario`, `observaciones`, `fecha`.

La columna `tipo` solo acepta `entrada` o `salida`. El campo `motivo` admite máximo
100 caracteres.

**Las ventas son salidas de inventario**: al cobrar en el punto de venta, el sistema
descuenta el stock y crea un movimiento tipo `salida` con el motivo `Venta <folio>`.

### Caja

Las rutas `ventas.index` y `ventas.store` están dentro del middleware `caja.abierta`:
no se puede vender sin un turno de caja abierto.

Al cerrar, se guarda el efectivo contado y la diferencia contra lo esperado
(`diferencia = contado - esperado`). Las operaciones de abrir y cerrar usan
transacciones con bloqueo para evitar dos cierres simultáneos.

Se puede vender en una sucursal distinta a la del turno de caja; cuando pasa, el
historial lo marca con una etiqueta amarilla.

### Helper de sucursal

El trait `ResuelveContextoSucursal` (en `app/Http/Controllers/Concerns`) centraliza
la lógica de qué sucursal corresponde a cada usuario. Métodos disponibles:
`usuarioEsAdmin()`, `sucursalDelUsuario()`, `sucursalSeleccionada($request)`,
`sucursalParaOperar()`, `sucursalesDisponibles()`.

Úsalo en lugar de repetir la validación en cada controlador.

---

## Importación de inventario desde Excel

Pantalla: **Inventario → Importar**.

El proceso tiene dos momentos: primero una **vista previa** que analiza el archivo
sin guardar nada, y después la **confirmación** que aplica los cambios.

El archivo se guarda temporalmente en `storage/app/private/importaciones`, y la
sucursal, el modo y el archivo quedan guardados en la sesión. Así no se pueden
cambiar entre la vista previa y la confirmación.

### Formatos aceptados

`.xlsx`, `.xls`, `.csv`

### Columnas que detecta

MARCA, MEDIDA, DESCRIPCIÓN, CATEGORÍA o TIPO, PRECIO PÚBLICO, MAYOREO, COSTO, STOCK.

Los encabezados pueden estar en cualquier fila, no necesariamente la primera.

### Modos

- **Sumar** (por defecto): suma la cantidad del Excel al stock actual y registra una
  entrada con el motivo "Importación de Excel".
- **Reemplazar**: el stock queda igual al del Excel, y la diferencia se registra como
  entrada o salida con el motivo "Ajuste por importación".

### Reglas

**Se salta la fila (ERROR):** falta marca o medida, número inválido, precio o costo
menor o igual a 0, stock negativo o con decimales, producto nuevo sin precio público,
fila repetida dentro del archivo.

**Se importa pero avisa (AVISO):** stock mayor o igual a 10,000, precio público menor
que el de mayoreo, producto nuevo sin costo o sin mayoreo (quedan en 0).

**Celdas vacías:** si STOCK viene vacío, no se toca el stock. Si un precio o costo
viene vacío, se conserva el que ya tenía el producto.

---

## Reportes

Pantalla: **Reportes**. Se puede ver en el navegador o descargar en PDF.

Los periodos disponibles son Hoy, Semana, Mes y Personalizado. Cada uno se compara
contra un periodo anterior equivalente: Hoy contra ayer, Semana contra los mismos
días de la semana pasada, Mes contra los mismos días del mes pasado, Personalizado
contra el mismo número de días justo antes.

Semana y Mes se calculan **hasta hoy**, no hasta el final del periodo.

### Limitaciones conocidas

- **"Nuevos clientes" no se puede filtrar por sucursal**, porque la tabla `clientes`
  no tiene columna `sucursal_id`. En pantalla y en el PDF aparece la leyenda
  "De todo el negocio (no se separa por sucursal)".
- **"Productos más vendidos" suma ingresos sin IVA**, mientras que "Ingresos totales"
  sí lo incluye. La diferencia entre ambos números es el IVA de las ventas facturadas.

### Validaciones

Las fechas deben tener formato AAAA-MM-DD, no pueden ser futuras, "desde" no puede ser
mayor que "hasta" y el rango máximo es de 366 días. Los errores salen en español en un
cuadro rojo, no como error 500.

Un usuario que no es Administrador General y no tiene sucursal asignada recibe un 403.

---

## Comandos útiles del día a día

```bash
# Levantar el servidor
php artisan serve

# Compilar estilos (obligatorio al agregar clases de Tailwind nuevas)
npm run build

# Vite en modo desarrollo (recompila solo)
npm run dev

# Limpiar cachés cuando algo no se refleja
php artisan view:clear
php artisan route:clear
php artisan config:clear

# Ver todas las rutas registradas
php artisan route:list

# Buscar una ruta específica
php artisan route:list --name=caja

# Ver qué haría una migración sin ejecutarla
php artisan migrate --pretend
```

### Dónde ver los errores

```
storage/logs/laravel.log
```

Los errores inesperados muestran un mensaje genérico al usuario y se guardan
completos en ese archivo.

---

## Notas de mantenimiento

### Cuidado con config:cache

Si corres `php artisan config:cache`, la variable `ADMIN_PASSWORD` deja de leerse del
`.env` y el seeder genera una contraseña aleatoria. No es un riesgo de seguridad,
pero hay que tenerlo presente al preparar un servidor.

### Idioma de los mensajes de validación

El `.env` de desarrollo tiene `APP_LOCALE=es`, pero el proyecto **no tiene la carpeta
`lang` publicada**. Por eso los mensajes automáticos de validación de Laravel salen en
inglés (se van al idioma de respaldo). Los mensajes escritos a mano en los
controladores sí están en español.

Para traducirlos habría que correr `php artisan lang:publish` e instalar los archivos
de español.

### Librerías de Excel

El proyecto usa **dos** y ambas son necesarias:

- `maatwebsite/excel` → para **exportar** (`app/Exports/InventarioExport.php`)
- `rap2hpoutre/fast-excel` → para **importar** (`app/Services/Importacion/ExcelInventarioImporter.php`)

### Generación de PDF

Se usa `barryvdh/laravel-dompdf` en `ExportInventarioController` y en
`ReportesController`.

### Códigos QR

El QR del ticket de venta se genera con JavaScript desde un CDN, dentro de
`resources/views/ventas/ticket.blade.php`. **No** usa una librería de PHP.
