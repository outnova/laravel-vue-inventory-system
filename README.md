# 📦 Inventory Pro - Laravel & Vue 3

Sistema de gestión de inventario moderno, escalable y altamente modular, construido con **Laravel 12** y **Vue 3 (Composition API)**.

Este proyecto implementa una arquitectura limpia separando lógica de negocio y componentes visuales, facilitando mantenimiento, escalabilidad y reutilización del código.

---

# 🇪🇸 Español

---

## 🚀 Características

- 📊 **Dashboard de Estadísticas** con animaciones numéricas.
- 📦 **CRUD completo de productos** (Crear, Leer, Actualizar, Eliminar).
- 🧩 **Arquitectura desacoplada** en componentes reutilizables.
- 🔍 **Filtros en tiempo real** por nombre y categoría.
- 🔔 **Sistema global de notificaciones (Toast)**.
- 📱 **Diseño 100% responsivo** con Tailwind CSS.
- ⚡ Arquitectura **SPA + API REST**.

---

## 🛠️ Stack Tecnológico

- **Backend:** Laravel 12 (PHP 8.3)
- **Frontend:** Vue 3 (Composition API) + Vite
- **Estilos:** Tailwind CSS
- **Base de Datos:** MySQL
- **Entorno:** Docker con Laravel Sail

---

# 📥 Instalación y Configuración

## 1️⃣ Clonar el repositorio

```bash
git clone https://github.com/outnova/laravel-vue-inventory-system
cd laravel-vue-inventory-system
```

---

## 2️⃣ Instalar dependencias PHP (sin PHP local)

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

---

## 3️⃣ Levantar entorno con Docker Sail

```bash
./vendor/bin/sail up -d
```

Verificar contenedores:

```bash
./vendor/bin/sail ps
```

---

## 4️⃣ Configurar variables de entorno

```bash
cp .env.example .env
```

Editar el archivo `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=sail
DB_PASSWORD=password
```

---

## 5️⃣ Configuración de la aplicación

Generar clave:

```bash
./vendor/bin/sail artisan key:generate
```

Ejecutar migraciones y seeders:

```bash
./vendor/bin/sail artisan migrate --seed
```

---

## 6️⃣ Configuración del Frontend

Instalar dependencias:

```bash
./vendor/bin/sail npm install
```

Modo desarrollo:

```bash
./vendor/bin/sail npm run dev
```

Build producción:

```bash
./vendor/bin/sail npm run build
```

---

## 🌐 Acceso al sistema

http://localhost

---

# 📂 Estructura del Frontend

```
src/
│
├── views/
│   └── ProductList.vue
│
├── components/
│   ├── products/
│   │   ├── ProductTable.vue
│   │   ├── ProductModal.vue
│   │   └── ProductFilters.vue
│   │
│   └── common/
│       └── Toast.vue
│
└── utils/
    └── formatters.js
```

---

# 🧠 Buenas Prácticas Implementadas

- Separación clara entre lógica y presentación.
- Componentes reutilizables y modulares.
- Validación reactiva.
- Arquitectura escalable.
- Código limpio y mantenible.

---

---

# 🇺🇸 English

---

## 🚀 Features

- 📊 **Statistics Dashboard** with animated counters.
- 📦 Full **Product CRUD** (Create, Read, Update, Delete).
- 🧩 **Decoupled component architecture**.
- 🔍 Real-time search and category filtering.
- 🔔 Global Toast notification system.
- 📱 Fully responsive design with Tailwind CSS.
- ⚡ **SPA + REST API architecture**.

---

## 🛠️ Tech Stack

- **Backend:** Laravel 12 (PHP 8.3)
- **Frontend:** Vue 3 (Composition API) + Vite
- **Styling:** Tailwind CSS
- **Database:** MySQL
- **Environment:** Docker with Laravel Sail

---

# 📥 Installation & Setup

## 1️⃣ Clone the repository

```bash
git clone https://github.com/outnova/laravel-vue-inventory-system
cd laravel-vue-inventory-system
```

---

## 2️⃣ Install PHP dependencies (without local PHP)

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

---

## 3️⃣ Start Docker Sail environment

```bash
./vendor/bin/sail up -d
```

Check containers:

```bash
./vendor/bin/sail ps
```

---

## 4️⃣ Configure environment variables

```bash
cp .env.example .env
```

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=inventory_db
DB_USERNAME=sail
DB_PASSWORD=password
```

---

## 5️⃣ Application setup

Generate key:

```bash
./vendor/bin/sail artisan key:generate
```

Run migrations and seeders:

```bash
./vendor/bin/sail artisan migrate --seed
```

---

## 6️⃣ Frontend setup

Install dependencies:

```bash
./vendor/bin/sail npm install
```

Development mode:

```bash
./vendor/bin/sail npm run dev
```

Production build:

```bash
./vendor/bin/sail npm run build
```

---

## 🌐 Access the system

http://localhost