# Finanzas GT

Aplicación web de gestión de finanzas personales. Permite registrar ingresos, gastos, metas de ahorro, tarjetas de crédito y listas de compras, con reportes mensuales y soporte para modo oscuro e idioma ES/EN.

## Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Vue 3 + Inertia.js |
| Estilos | Tailwind CSS v3 + @tailwindcss/forms |
| Gráficas | Chart.js + vue-chartjs |
| Autenticación | Laravel Breeze + Sanctum (SPA) |
| Base de datos | MySQL |
| Build | Vite 7 |

## Requisitos previos

- PHP 8.2 o superior con extensiones: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`
- Composer
- Node.js 18+ y npm
- MySQL 8+

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repo> finanzas
cd finanzas
```

### 2. Instalar dependencias PHP

```bash
composer install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` y ajusta la conexión a tu base de datos:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=finanzas
DB_USERNAME=root
DB_PASSWORD=tu_password

APP_LOCALE=es
APP_FALLBACK_LOCALE=es
```

### 4. Crear la base de datos y correr migraciones

Crea la base de datos en MySQL y luego:

```bash
php artisan migrate
```

### 5. Instalar dependencias de Node y compilar assets

```bash
npm install
npm run build
```

### 6. Iniciar el servidor

```bash
php artisan serve
```

Abre [http://localhost:8000](http://localhost:8000) en tu navegador.

---

## Desarrollo local

Para correr todos los servicios en paralelo (servidor, queue, logs y Vite con HMR):

```bash
composer run dev
```

Esto levanta simultáneamente:
- `php artisan serve` — servidor PHP en el puerto 8000
- `php artisan queue:listen` — procesador de colas
- `php artisan pail` — visor de logs en tiempo real
- `npm run dev` — Vite con hot module replacement

---

## Estructura del proyecto

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/Finance/        # Ingresos, Gastos, Ahorros, Tarjetas, Compras, Reportes
│   │   └── Auth/               # Autenticación (Breeze)
│   └── Middleware/
│       ├── HandleInertiaRequests.php
│       └── SetLocale.php       # Detecta X-Locale header y traduce errores del servidor
resources/
├── js/
│   ├── Pages/
│   │   ├── Auth/               # Login, Register, ForgotPassword, ResetPassword, ConfirmPassword
│   │   └── Finance/            # Dashboard, Income, Expenses, SavingsGoals, CreditCards, Shopping, Reports
│   ├── Layouts/
│   │   ├── FinanceLayout.vue   # Layout principal con navegación lateral
│   │   └── GuestLayout.vue     # Layout para páginas de autenticación
│   ├── Components/             # ConfirmDialog, FinanceToast, InputError, etc.
│   └── composables/
│       ├── useLocale.js        # i18n reactivo ES/EN con persistencia en localStorage
│       ├── useDarkMode.js      # Modo oscuro persistente
│       └── useFinanceApi.js    # Cliente HTTP con retry automático de CSRF 419
lang/
├── en/                         # Mensajes de validación, auth y contraseñas en inglés
└── es/                         # Mensajes de validación, auth y contraseñas en español
database/
└── migrations/                 # users, incomes, expenses, savings_goals,
                                # credit_cards, credit_purchases,
                                # shopping_lists, shopping_items, savings_contributions
```

## Módulos

| Módulo | Descripción |
|--------|-------------|
| **Dashboard** | Resumen del mes: ingresos, gastos, ahorros y balance. Gráfica de barras de los últimos 6 meses y donut por categoría. |
| **Ingresos** | Registro de ingresos por tipo (Sueldo / Extra) con totales mensuales. |
| **Gastos** | Registro de gastos con categoría, filtros por mes y año. |
| **Metas de ahorro** | Metas con monto objetivo, fecha límite, color y aportes mensuales. Proyección a fin de año. |
| **Tarjetas de crédito** | Registro de tarjetas, compras pendientes, barra de uso del crédito y pago de saldo completo. |
| **Compras** | Listas de compras con artículos y precios estimados. Envío directo a Gastos al finalizar. |
| **Reportes** | Reporte mensual presupuestado vs. real por categoría. Permite copiar el resumen al portapapeles. |

## Características

- **Modo oscuro** — toggle en la barra de navegación, persiste entre sesiones
- **Idioma ES / EN** — toggle en la barra de navegación; los errores del servidor también se traducen automáticamente según el idioma activo
- **CSRF automático** — errores 419 refrescan el token y reintentan el request sin intervención del usuario
- **Autenticación completa** — registro, inicio de sesión, recuperación y restablecimiento de contraseña

## Variables de entorno relevantes

| Variable | Descripción | Valor recomendado |
|----------|-------------|-------------------|
| `APP_LOCALE` | Idioma del backend (`es` / `en`) | `es` |
| `APP_URL` | URL base de la aplicación | `http://localhost` |
| `DB_DATABASE` | Nombre de la base de datos | `finanzas` |
| `SESSION_DRIVER` | Driver de sesión | `database` |
| `MAIL_MAILER` | Driver de correo (para reset de contraseña) | `log` |

> En desarrollo, `MAIL_MAILER=log` escribe los correos en `storage/logs/laravel.log` en lugar de enviarlos.

## Comandos útiles

```bash
# Limpiar caché de configuración
php artisan config:clear

# Limpiar caché de la aplicación
php artisan cache:clear

# Ver logs en tiempo real
php artisan pail

# Correr migraciones frescas (elimina todos los datos)
php artisan migrate:fresh

# Correr tests
composer run test
```
