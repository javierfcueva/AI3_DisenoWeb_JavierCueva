# Registro de auditorías de landing pages

Sistema web para registrar auditorías de rendimiento y calidad de landing pages. Cada auditoría guarda las métricas Core Web Vitals de una página (LCP, INP y CLS) y los puntos aprobados de un checklist de calidad de 17 puntos. El sistema clasifica cada auditoría y muestra un resumen con promedios y porcentaje de aprobación.

Está hecho en PHP sin frameworks, con una arquitectura MVC ligera, PDO con sentencias preparadas y MariaDB/MySQL.

Actividad Integradora 3 de Programación de Sistemas Web, Universidad ECOTEC.

## Funciones

- Registrar auditorías, eligiendo un cliente existente o creando uno nuevo en el mismo formulario.
- Consultar las auditorías en una tabla responsiva, que en pantallas angostas se muestra como tarjetas.
- Editar y eliminar auditorías.
- Clasificar cada auditoría como Aprobada, Mejorable o Crítica según los umbrales de Core Web Vitals.
- Ver un resumen con el porcentaje de aprobación, el conteo por clasificación y los promedios de cada métrica.
- Tema claro y oscuro.

## Requisitos

- [XAMPP](https://www.apachefriends.org/) con Apache, PHP y MariaDB. Se probó con PHP 8.3.36 y MariaDB 10.11.16 en Windows.
- Extensión `pdo_mysql` de PHP, incluida y activa en XAMPP.
- Módulo `mod_rewrite` de Apache y `AllowOverride All` en `htdocs`, ambos activos en la configuración predeterminada de XAMPP.
- Git, para clonar el repositorio.

## Despliegue local

### 1. Iniciar los servicios

En el panel de control de XAMPP, iniciar **Apache** y **MySQL**.

### 2. Clonar el repositorio en `htdocs`

```powershell
cd C:\xampp\htdocs
git clone https://github.com/javierfcueva/AI3_DisenoWeb_JavierCueva.git
cd AI3_DisenoWeb_JavierCueva
```

La carpeta puede tener otro nombre: los enlaces se calculan a partir de la ubicación del proyecto.

### 3. Configurar la conexión

Copiar `.env.example` como `.env`:

```powershell
Copy-Item .env.example .env
```

Los valores de `.env.example` corresponden a una instalación predeterminada de XAMPP (usuario `root` sin contraseña). Si el servidor de base de datos usa otro usuario, contraseña o puerto, ajustarlos en `.env`:

```ini
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=db_sistema_web
DB_USER=root
DB_PASSWORD=
```

El archivo `.env` contiene credenciales y Git lo ignora: nunca se sube al repositorio.

### 4. Crear la base de datos

El script `database/db_sistema_web.sql` crea la base `db_sistema_web` y sus tablas. Se puede ejecutar más de una vez sin error, porque usa `IF NOT EXISTS`.

**Con phpMyAdmin:** abrir `http://localhost/phpmyadmin/`, entrar en la pestaña **Importar** sin seleccionar ninguna base, elegir el archivo `database/db_sistema_web.sql` y pulsar **Importar**. phpMyAdmin puede mostrar avisos "#1046 No database selected" junto a algunas sentencias. No afectan la importación: lo que confirma el resultado es el mensaje "Import has been successfully finished" al principio de la página.

**Desde la consola**, en la carpeta del proyecto:

```powershell
C:\xampp\mysql\bin\mysql.exe --user=root --execute="source database/db_sistema_web.sql"
```

Si `root` tiene contraseña, agregar `--password` para que la pida.

### 5. Cargar los datos de prueba (opcional)

El script `database/datos_prueba.sql` agrega 6 clientes y 12 auditorías ficticias que cubren las tres clasificaciones. Las direcciones usan el dominio reservado `.example`, que no pertenece a ninguna empresa.

Se ejecuta después del paso anterior y solo carga los datos si las tablas `clientes` y `auditorias` están vacías. Si ya tienen registros, por ejemplo al ejecutarlo por segunda vez, no agrega nada, así que los datos nunca se repiten.

Al terminar, el script muestra una columna `resultado` con uno de estos mensajes:

- `Datos cargados: 6 clientes y 12 auditorías.`
- `No se cargó nada: las tablas ya tienen registros.`

**Con phpMyAdmin:** igual que en el paso anterior, con el archivo `database/datos_prueba.sql`. El mensaje aparece al final de la página.

**Desde la consola:**

```powershell
C:\xampp\mysql\bin\mysql.exe --user=root --execute="source database/datos_prueba.sql"
```

Para volver a cargar los datos, por ejemplo si se registraron auditorías antes de este paso, hay que vaciar primero las dos tablas, empezando por `auditorias`. Se pierden todos los registros, y los nuevos continúan la numeración desde el último número usado:

```powershell
C:\xampp\mysql\bin\mysql.exe --user=root --execute="DELETE FROM db_sistema_web.auditorias; DELETE FROM db_sistema_web.clientes;"
```

### 6. Abrir el sistema

```
http://localhost/AI3_DisenoWeb_JavierCueva/
```

Si la carpeta tiene otro nombre, reemplazarlo en la dirección.

## Rutas

| Método | Ruta | Acción |
|---|---|---|
| GET | `/` | Portada con los umbrales y la regla de clasificación |
| GET | `/auditorias` | Resumen y listado de auditorías |
| GET | `/auditorias/nueva` | Formulario de registro |
| POST | `/auditorias/nueva` | Guarda una auditoría nueva |
| GET | `/auditorias/editar?id=N` | Formulario de edición |
| POST | `/auditorias/editar` | Guarda los cambios |
| POST | `/auditorias/eliminar` | Elimina una auditoría |

## Clasificación

Cada métrica se califica con los umbrales de Core Web Vitals:

| Métrica | Buena | Por mejorar | Deficiente |
|---|---|---|---|
| LCP (carga del contenido principal) | ≤ 2,5 s | ≤ 4 s | > 4 s |
| INP (respuesta a la interacción) | ≤ 200 ms | ≤ 500 ms | > 500 ms |
| CLS (estabilidad visual) | ≤ 0,1 | ≤ 0,25 | > 0,25 |

La auditoría es **Crítica** si alguna métrica es deficiente, **Aprobada** si las tres son buenas y **Mejorable** en los demás casos. El checklist de calidad se informa aparte y no cambia la clasificación.

La clasificación no se guarda en la base: se calcula al mostrar los datos, así que siempre corresponde a los valores actuales.

## Estructura

```
AI3_DisenoWeb_JavierCueva/
├── .htaccess              Envía las peticiones a public/ y bloquea el resto
├── .env.example           Plantilla de la configuración de la base de datos
├── app/
│   ├── controllers/       InicioController y AuditoriaController
│   ├── core/              Enrutador, funciones auxiliares y sesión
│   ├── models/            Acceso a datos, validación y clasificación
│   └── views/             Plantilla y vistas
├── config/
│   └── conexion.php       Conexión PDO
├── database/
│   ├── db_sistema_web.sql Estructura de la base (DDL)
│   └── datos_prueba.sql   Datos de prueba ficticios
└── public/
    ├── index.php          Controlador frontal
    ├── css/               Estilos
    └── js/                Tema, ayuda del formulario y confirmación
```

## Base de datos

- **`clientes`:** `id`, `nombre` (único), `sector` y `fecha_creacion`.
- **`auditorias`:** `id`, `cliente_id`, `url`, `lcp_segundos`, `inp_ms`, `cls`, `checks_aprobados`, `notas`, `fecha_creacion` y `fecha_actualizacion`.

`auditorias.cliente_id` es una clave foránea a `clientes`: un cliente con auditorías no se puede eliminar. Las restricciones `CHECK` impiden valores negativos de LCP y CLS y más de 17 puntos del checklist.

## Seguridad

- **Inyección SQL:** todas las consultas con datos del usuario usan sentencias preparadas de PDO (`prepare` y `execute`), con la emulación de sentencias preparadas desactivada.
- **XSS:** todo dato dinámico se escapa con `htmlspecialchars()` al mostrarse. Solo las direcciones `http` y `https` se convierten en enlaces.
- **Validación:** el navegador valida el formulario como ayuda, pero el servidor vuelve a validar cada campo (formato, rango y longitud) aunque la petición no venga del formulario.
- **Modo estricto:** la conexión activa el modo estricto de SQL en cada sesión, para que la base rechace los valores fuera de rango en lugar de recortarlos.
- **CSRF:** registrar, editar y eliminar solo se aceptan por POST y con un token por sesión.
- **Errores:** si la base de datos no responde, el usuario ve un mensaje genérico; el detalle queda en el log, sin mostrar credenciales ni mensajes del sistema.
- **Archivos protegidos:** Apache bloquea el acceso directo a `.env`, `.git`, `app/`, `config/` y `database/`. Solo `public/` es accesible.
- **Transacciones:** el alta o la edición de una auditoría con un cliente nuevo se guarda en una sola transacción: se guardan los dos o ninguno. Al editar, la fila queda bloqueada hasta guardar: si otra petición la elimina en ese momento, la edición responde con el error 404 y no deja un cliente sin auditorías.
- **Cabeceras:** Content Security Policy sin scripts en línea, `X-Content-Type-Options` y cookie de sesión `HttpOnly` con `SameSite=Lax`.

## Limitaciones

- No hay inicio de sesión: cualquiera que acceda al servidor puede registrar, editar y eliminar. Está pensado para uso local. Con la configuración predeterminada de XAMPP, Apache también acepta conexiones de otros equipos de la red, salvo que el firewall las bloquee.
- La configuración de ejemplo usa el usuario `root` de XAMPP. Fuera de un entorno de pruebas conviene crear un usuario propio con solo los permisos `SELECT`, `INSERT`, `UPDATE` y `DELETE` sobre `db_sistema_web`, y escribir sus datos en `.env`.
- Si dos personas editan la misma auditoría a la vez, se conservan los cambios de la última que guarda, sin aviso.
- El listado no tiene paginación.
- Los clientes se crean desde el formulario de auditorías, pero no se pueden editar ni eliminar desde la interfaz.
- Sin JavaScript, la eliminación funciona, pero no pide confirmación.

## Autor

Javier Cueva. Programación de Sistemas Web, Universidad ECOTEC, noviembre de 2026.
