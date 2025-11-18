# Sistema de Gestión Huevos Kikes SCM

Sistema de gestión completo para la administración de huevos Kikes, incluyendo inventarios, ventas, compras, proveedores y clientes.

## 🚀 Características

- **Gestión de Inventarios**: Control completo de productos y stock
- **Sistema de Ventas**: Registro y gestión de ventas con generación de PDFs
- **Gestión de Compras**: Control de proveedores y compras
- **Administración de Clientes**: Base de datos de clientes
- **Sistema de Usuarios**: Autenticación y recuperación de contraseña
- **Interfaz Moderna**: Diseño responsive con tema de huevos

## 🛠️ Tecnologías

- **Backend**: PHP 8.1
- **Base de Datos**: MySQL
- **Frontend**: HTML5, CSS3, JavaScript
- **Email**: PHPMailer con Gmail SMTP
- **Contenedor**: Docker & Docker Compose

## 📋 Requisitos

- Docker y Docker Compose
- PHP 8.1+
- MySQL 8.0+
- Composer (para dependencias PHP)

## 🚀 Instalación y Ejecución

### Opción 1: Con Docker (Recomendado)

1. **Clonar el repositorio**:
   ```bash
   git clone https://github.com/catalina05877/kikes_scm.git
   cd kikes_scm
   ```

2. **Crear archivo de variables de entorno**:
   ```bash
   cp .env.example .env
   ```
   Edita el archivo `.env` con tus configuraciones.

3. **Levantar los contenedores**:
   ```bash
   docker-compose up -d
   ```

4. **Acceder a la aplicación**:
   - Aplicación: http://localhost:8080
   - Base de datos: localhost:3306

### Opción 2: Instalación Local

1. **Instalar dependencias**:
   ```bash
   composer install
   ```

2. **Configurar base de datos**:
   - Crear base de datos MySQL
   - Ejecutar los scripts de creación de tablas

3. **Configurar servidor web**:
   - Apuntar el document root a la carpeta del proyecto
   - Asegurar que mod_rewrite esté habilitado

## 🔧 Configuración

### Variables de Entorno (.env)

```env
# Base de datos
DB_HOST=localhost
DB_NAME=kikes_scm
DB_USER=tu_usuario
DB_PASS=tu_password

# Gmail SMTP
GMAIL_USERNAME=tu_email@gmail.com
GMAIL_APP_PASSWORD=tu_app_password

# Aplicación
APP_NAME="Huevos Kikes SCM"
APP_URL=http://localhost/kikes_scm
```

### Configuración de Gmail

1. Ve a [Google Account Settings](https://myaccount.google.com/)
2. Activa la verificación en 2 pasos
3. Genera una "App Password" para el envío de emails
4. Usa esa contraseña en `GMAIL_APP_PASSWORD`

## 📁 Estructura del Proyecto

```
kikes_scm/
├── config/
│   └── db.php                 # Configuración de base de datos
├── modulos/
│   ├── inventarios/           # Gestión de inventarios
│   ├── ventas/                # Sistema de ventas
│   ├── compras/               # Gestión de compras
│   ├── proveedores/           # Administración de proveedores
│   ├── clientes/              # Gestión de clientes
│   └── caja/                  # Control de caja
├── vendor/                    # Dependencias (Composer)
├── img/                       # Imágenes del sistema
├── uploads/                   # Archivos subidos
├── index.php                  # Página de login
├── dashboard.php              # Panel principal
├── Dockerfile                 # Configuración Docker
├── docker-compose.yml         # Servicios Docker
└── README.md                  # Este archivo
```

## 🔐 Funcionalidades de Seguridad

- Autenticación de usuarios
- Recuperación de contraseña por email
- Protección CSRF en formularios
- Validación de datos de entrada
- Archivos sensibles excluidos de Git

## 📊 Base de Datos

El sistema incluye las siguientes tablas principales:
- `usuarios` - Usuarios del sistema
- `inventarios` - Productos y stock
- `ventas` - Registro de ventas
- `compras` - Registro de compras
- `proveedores` - Información de proveedores
- `clientes` - Información de clientes

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.

## 📞 Soporte

Para soporte técnico o preguntas, por favor contacta al equipo de desarrollo.

---

**Desarrollado con ❤️ para Huevos Kikes**
