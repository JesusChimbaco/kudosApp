# 🎯 KudosApp

Una aplicación de seguimiento de hábitos construida con Laravel, Inertia.js, Vue 3 y PostgreSQL.

## 📖 Descripción

KudosApp es una aplicación web que te ayuda a crear, seguir y mantener hábitos saludables. Permite a los usuarios:

- ✅ Crear hábitos personalizados organizados por categorías
- 📊 Registrar el progreso diario de cada hábito
- 🔔 Configurar recordatorios automáticos
- 🏆 Desbloquear logros según tus avances
- 📈 Visualizar estadísticas de tus hábitos

## 🛠️ Stack Tecnológico

### Backend
- **Laravel 12.x** - Framework PHP
- **PostgreSQL 17.6** - Base de datos
- **Laravel Fortify** - Autenticación

### Frontend
- **Vue 3** - Framework JavaScript
- **Inertia.js** - Capa de conexión SPA
- **TypeScript** - Tipado estático
- **Vite** - Build tool y dev server

## 🚀 Instalación

### Instalación Rápida (Recomendada)

**Windows PowerShell:**
```powershell
.\setup.ps1
```

**Linux/Mac:**
```bash
chmod +x setup.sh
./setup.sh
```

### Instalación Manual

Para una guía completa paso a paso, consulta **[INSTALACION.md](INSTALACION.md)**.

### Resumen Rápido

```bash
# 1. Clonar el repositorio
git clone https://github.com/JesusChimbaco/kudosApp.git
cd kudosApp

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Crear base de datos en PostgreSQL (nombre: kudosBD)
# 5. Configurar credenciales en .env

# 6. Migrar y poblar datos
php artisan migrate
php artisan db:seed --class=CategoriaSeeder
php artisan db:seed --class=LogroSeeder

# 7. Iniciar servidor
php artisan serve

# 8. En otra terminal, compilar assets
npm run dev
```

Visita: **http://localhost:8000**

## 📁 Estructura del Proyecto

```
kudosApp/
├── app/
│   ├── Http/Controllers/     # Controladores
│   ├── Models/               # Modelos Eloquent
│   │   ├── User.php
│   │   ├── Categoria.php
│   │   ├── Habito.php
│   │   ├── RegistroDiario.php
│   │   ├── Recordatorio.php
│   │   └── Logro.php
│   └── Providers/
├── database/
│   ├── migrations/           # Migraciones de BD
│   ├── seeders/             # Datos iniciales
│   └── factories/           # Factories para tests
├── resources/
│   ├── js/                  # Vue 3 + TypeScript
│   │   ├── components/
│   │   ├── pages/
│   │   └── layouts/
│   └── views/               # Vistas Blade
└── routes/
    ├── web.php              # Rutas web
    └── auth.php             # Rutas de autenticación
```

## 🗄️ Modelos y Relaciones

### User (Usuario)
- Tiene muchos `Habitos`
- Tiene muchos `Logros` (relación muchos a muchos)

### Categoria (Categoría)
- Tiene muchos `Habitos`

### Habito (Hábito)
- Pertenece a un `User`
- Pertenece a una `Categoria`
- Tiene muchos `RegistroDiarios`
- Tiene muchos `Recordatorios`

### RegistroDiario (Registro Diario)
- Pertenece a un `Habito`

### Recordatorio
- Pertenece a un `Habito`

### Logro (Achievement)
- Tiene muchos `Users` (relación muchos a muchos)

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar tests con cobertura
php artisan test --coverage

# Ejecutar tests específicos
php artisan test --filter=DashboardTest
```

## 📚 Documentación Adicional

- **[INSTALACION.md](INSTALACION.md)** - Guía completa de instalación
- **[GUIA_MIGRACIONES.md](GUIA_MIGRACIONES.md)** - Guía de migraciones de Laravel
- **[GUIA_MODELOS_ELOQUENT.md](GUIA_MODELOS_ELOQUENT.md)** - Guía de modelos Eloquent
- **[GUIA_AUTENTICACION.md](GUIA_AUTENTICACION.md)** - Sistema de autenticación
- **[BasedeDatos.md](BasedeDatos.md)** - Esquema de base de datos

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Haz fork del proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add: nueva funcionalidad'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Comandos Útiles

```bash
# Desarrollo
php artisan serve              # Servidor de desarrollo
npm run dev                    # Compilar assets en modo desarrollo
php artisan tinker             # Consola interactiva

# Base de datos
php artisan migrate            # Ejecutar migraciones
php artisan migrate:fresh      # Reiniciar migraciones
php artisan db:seed            # Ejecutar seeders
php artisan db:show            # Mostrar info de la BD

# Caché
php artisan cache:clear        # Limpiar caché
php artisan config:clear       # Limpiar caché de configuración
php artisan route:clear        # Limpiar caché de rutas
php artisan view:clear         # Limpiar caché de vistas

# Producción
npm run build                  # Compilar para producción
php artisan optimize           # Optimizar aplicación
```

## ⚙️ Requisitos del Sistema

- PHP >= 8.2
- Composer >= 2.0
- Node.js >= 18.x
- PostgreSQL >= 12
- Extensiones PHP requeridas:
  - PDO
  - pdo_pgsql
  - pgsql
  - mbstring
  - openssl
  - tokenizer
  - xml
  - ctype
  - json
  - bcmath

## 🐛 Solución de Problemas

Si encuentras problemas durante la instalación, consulta la sección de "Solución de Problemas Comunes" en **[INSTALACION.md](INSTALACION.md)**.

## 📄 Licencia

Este proyecto es de código abierto.

## 👨‍💻 Autor

**Jesus Chimbaco** - [GitHub](https://github.com/JesusChimbaco)

---

⭐ Si este proyecto te resulta útil, ¡no olvides darle una estrella!
