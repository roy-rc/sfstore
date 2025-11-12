# 🛍️ SFStore - Symfony 7.3 Ecommerce

Un sistema de comercio electrónico completo desarrollado con **Symfony 7.3**, **Bootstrap 5** y **MySQL**, que incluye panel administrativo y tienda pública con gestión de productos, categorías, carritos y órdenes.

## 📋 Características Principales

### 🏪 Tienda Pública
- **Página principal** con productos destacados
- **Catálogo de productos** con sistema de filtros
- **Navegación por categorías** con jerarquía
- **Páginas de producto** individuales con detalles
- **Sistema de carrito de compras**
- **Proceso de checkout** para finalizar compras
- **Autenticación de clientes**

### 🔧 Panel Administrativo
- **Dashboard** con estadísticas y métricas
- **Gestión de categorías** (CRUD completo)
- **Gestión de productos** (CRUD completo)
- **Gestión de órdenes** y seguimiento
- **Gestión de carritos** activos
- **Sistema de autenticación admin**
- **Interfaz responsiva** con Bootstrap 5

### 🎨 Características Técnicas
- **Symfony 7.3** como framework principal
- **Doctrine ORM 3.5** para la base de datos
- **Bootstrap 5** para el diseño responsivo
- **Webpack Encore** para la gestión de assets
- **Twig** como motor de plantillas
- **Sistema de seguridad** con roles diferenciados
- **Fixtures** con datos de prueba
- **Validaciones** de formularios

## 🛠️ Tecnologías Utilizadas

| Tecnología | Versión | Propósito |
|------------|---------|-----------|
| **PHP** | ≥ 8.2 | Lenguaje backend |
| **Symfony** | 7.3.* | Framework PHP |
| **Doctrine ORM** | ^3.5 | Mapeo objeto-relacional |
| **MySQL** | 8.0+ | Base de datos |
| **Bootstrap** | 5.x | Framework CSS |
| **Webpack Encore** | ^2.3 | Gestión de assets |
| **Twig** | ^3.0 | Motor de plantillas |
| **FontAwesome** | 6.x | Iconografía |

## 📁 Estructura del Proyecto

```
sfstore/
├── 📁 config/              # Configuraciones de Symfony
├── 📁 public/              # Punto de entrada web
│   └── index.php           # Front controller
├── 📁 src/
│   ├── 📁 Controller/      # Controladores
│   │   ├── 📁 Admin/       # Controladores administrativos
│   │   │   ├── AdminProductController.php
│   │   │   ├── AdminCategoryController.php
│   │   │   ├── AdminOrderController.php
│   │   │   ├── AdminCartController.php
│   │   │   ├── AdminSecurityController.php
│   │   │   └── DashboardController.php
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   ├── CategoryController.php
│   │   ├── CartController.php
│   │   ├── CheckoutController.php
│   │   └── CustomerController.php
│   ├── 📁 Entity/          # Entidades Doctrine
│   │   ├── User.php        # Usuario admin
│   │   ├── Customer.php    # Cliente de la tienda
│   │   ├── Category.php    # Categorías de productos
│   │   ├── Product.php     # Productos
│   │   ├── Cart.php        # Carritos de compra
│   │   ├── CartItem.php    # Items del carrito
│   │   ├── Order.php       # Órdenes de compra
│   │   └── OrderItem.php   # Items de las órdenes
│   ├── 📁 Repository/      # Repositorios Doctrine
│   ├── 📁 DataFixtures/    # Datos de prueba
│   └── Kernel.php
├── 📁 templates/           # Plantillas Twig
│   ├── 📁 admin/          # Templates administrativos
│   │   ├── 📁 category/   # CRUD categorías
│   │   ├── 📁 product/    # CRUD productos
│   │   ├── 📁 order/      # Gestión órdenes
│   │   ├── 📁 cart/       # Gestión carritos
│   │   └── base.html.twig # Layout admin
│   ├── 📁 category/       # Templates públicos categorías
│   ├── 📁 product/        # Templates públicos productos
│   ├── 📁 cart/           # Templates carrito
│   ├── 📁 customer/       # Templates clientes
│   └── base.html.twig     # Layout público
├── 📁 var/                # Cache y logs
├── 📁 vendor/             # Dependencias Composer
├── .env                   # Variables de entorno
├── composer.json          # Dependencias PHP
└── README.md             # Este archivo
```

## 🚀 Instalación y Configuración

### 📋 Prerrequisitos

- **PHP** ≥ 8.2
- **Composer** 2.x
- **MySQL** 8.0+ o **MariaDB** 10.4+
- **Node.js** y **npm** (para assets)
- **Servidor web** (Apache/Nginx) o **Symfony CLI**

### 🔧 Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone <repository-url>
cd sfstore
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Configurar base de datos**
```bash
# Editar el archivo .env y configurar DATABASE_URL
DATABASE_URL="mysql://usuario:contraseña@127.0.0.1:3306/sfstore?serverVersion=8.0&charset=utf8mb4"
```

4. **Crear la base de datos**
```bash
php bin/console doctrine:database:create
```

5. **Crear el esquema de base de datos**
```bash
php bin/console doctrine:schema:create
```

6. **Cargar datos de prueba**
```bash
php bin/console doctrine:fixtures:load --no-interaction
```

7. **Instalar y compilar assets**
```bash
npm install
npm run build
```

8. **Iniciar el servidor de desarrollo**
```bash
php bin/console server:run
# O usando Symfony CLI
symfony serve
```

## 🗄️ Base de Datos

### 📊 Modelo de Datos

El sistema utiliza las siguientes entidades principales:

- **User** - Usuarios administradores del sistema
- **Customer** - Clientes de la tienda
- **Category** - Categorías de productos (con jerarquía)
- **Product** - Productos de la tienda
- **Cart** - Carritos de compra (sesión/cliente)
- **CartItem** - Items individuales del carrito
- **Order** - Órdenes de compra finalizadas
- **OrderItem** - Items de las órdenes

### 🔗 Relaciones Principales

```
Category (1:N) Product
Product (N:M) Category (categorización múltiple)
Customer (1:N) Cart
Cart (1:N) CartItem
CartItem (N:1) Product
Customer (1:N) Order
Order (1:N) OrderItem
OrderItem (N:1) Product
```

## 👤 Usuarios y Autenticación

### 🔐 Credenciales por Defecto

**Administrador:**
- **Email:** `admin@sfstore.com`
- **Contraseña:** `admin123`
- **Rol:** `ROLE_ADMIN`

### 🎭 Roles del Sistema

- **ROLE_ADMIN** - Acceso completo al panel administrativo
- **ROLE_CUSTOMER** - Cliente de la tienda pública

## 🌐 Rutas Principales

### 🏪 Tienda Pública

| Ruta | Descripción |
|------|-------------|
| `/` | Página principal |
| `/category/{slug}` | Ver categoría |
| `/product/{slug}` | Ver producto |
| `/cart` | Ver carrito |
| `/checkout` | Proceso de compra |
| `/customer/login` | Login de clientes |
| `/customer/register` | Registro de clientes |

### 🔧 Panel Administrativo

| Ruta | Descripción |
|------|-------------|
| `/admin` | Dashboard principal |
| `/admin/login` | Login administrador |
| `/admin/categories` | Gestión de categorías |
| `/admin/products` | Gestión de productos |
| `/admin/orders` | Gestión de órdenes |
| `/admin/carts` | Gestión de carritos |

## 📝 Funcionalidades Implementadas

### ✅ Completadas

- [x] **Sistema de autenticación** dual (admin/clientes)
- [x] **Panel administrativo** completo con Bootstrap 5
- [x] **CRUD de categorías** con jerarquía
- [x] **CRUD de productos** con imágenes y categorización
- [x] **Tienda pública** responsive
- [x] **Sistema de carrito** básico
- [x] **Base de datos** con relaciones completas
- [x] **Fixtures** con datos de prueba
- [x] **Templates** responsive para admin y público
- [x] **Validaciones** de formularios
- [x] **Sistema de assets** con Webpack Encore

### 🚧 En Desarrollo

- [ ] **Proceso de checkout** completo
- [ ] **Gestión de órdenes** avanzada
- [ ] **Sistema de pagos**
- [ ] **Notificaciones** por email
- [ ] **Reportes** y estadísticas
- [ ] **Búsqueda** avanzada de productos
- [ ] **Sistema de reviews** y calificaciones
- [ ] **Gestión de inventario**

## 🎨 Interfaz de Usuario

### 🖥️ Panel Administrativo

- **Sidebar** de navegación con secciones organizadas
- **Dashboard** con métricas y estadísticas
- **Formularios** con validación en tiempo real
- **Tablas** con filtros y búsqueda
- **Modales** para confirmaciones
- **Vista previa** en tiempo real para productos/categorías
- **Responsive design** para móviles y tablets

### 🛍️ Tienda Pública

- **Header** con navegación y carrito
- **Homepage** con productos destacados
- **Catálogo** con filtros por categoría y precio
- **Páginas de producto** con imágenes y detalles
- **Carrito** con gestión de cantidades
- **Footer** informativo
- **Mobile-first** responsive design

## 🔧 Configuración

### ⚙️ Variables de Entorno

```env
# Entorno de la aplicación
APP_ENV=dev
APP_SECRET=your_secret_key_here

# Base de datos
DATABASE_URL="mysql://user:password@127.0.0.1:3306/sfstore?serverVersion=8.0&charset=utf8mb4"

# URL por defecto para comandos CLI
DEFAULT_URI=http://localhost
```

### 🛡️ Seguridad

El sistema implementa:

- **Firewalls** separados para admin y clientes
- **Encriptación** de contraseñas con bcrypt
- **Tokens CSRF** en formularios
- **Validación** de entrada en todos los endpoints
- **Control de acceso** basado en roles

## 🐛 Problemas Conocidos

### ⚠️ Issues Actuales

1. **Proceso de checkout** - No completamente implementado
2. **Gestión de stock** - Requiere validación adicional  
3. **Emails** - Sistema de notificaciones pendiente
4. **Búsqueda** - Funcionalidad de búsqueda básica
5. **Productos relacionados** - Lógica de recomendación simple

### 🔨 En Proceso de Corrección

- Validaciones adicionales en formularios
- Optimización de consultas a la base de datos
- Mejoras en la experiencia de usuario
- Tests unitarios y funcionales

## 🚀 Próximos Pasos

1. **Completar checkout** y sistema de pagos
2. **Implementar notificaciones** por email
3. **Agregar sistema de reviews**
4. **Optimizar rendimiento** de consultas
5. **Agregar tests** automatizados
6. **Implementar búsqueda** avanzada
7. **Dashboard analytics** con métricas detalladas

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para detalles.

## 📞 Soporte

Si encuentras algún problema o tienes preguntas:

1. Revisa los **issues conocidos** arriba
2. Verifica la **configuración** de la base de datos
3. Ejecuta `php bin/console cache:clear`
4. Verifica los **logs** en `var/log/`

---

**Desarrollado con ❤️ usando Symfony 7.3**