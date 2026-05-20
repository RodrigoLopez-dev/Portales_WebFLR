# 📘 Portal de Oraciones – README

## 🧭 Descripción del Proyecto

El **Portal de Oraciones** es una aplicación web desarrollada en PHP (compatible con **PHP 5.6**) que permite a los usuarios:

- Encender una vela virtual 🕯️
- Registrar una intención de oración
- Visualizar velas activas
- Compartir velas (ej: vía WhatsApp)
- Recibir confirmación por correo electrónico

Además, cuenta con un **panel administrativo** para gestión y monitoreo del sistema.

---

## 🏗️ Arquitectura General

- **Frontend:** PHP + HTML + CSS + JS (vanilla)
- **Backend:** PHP 5.6 (estructura modular)
- **Base de datos:** MySQL
- **Correo:** PHPMailer
- **Autenticación Admin:** Login con Google

---

## 📁 Estructura del Proyecto

```
/
├── index.php
├── enciende.php
├── velas.php
├── assets/
├── includes/
├── api/
├── admin/
├── vendor/
└── .env
```

---

## ⚙️ Configuración del Entorno

### Variables de entorno (.env)

```
APP_ENV=production
APP_URL=https://tu-dominio.cl

DB_HOST=localhost
DB_NAME=portal_de_oraciones
DB_USER=usuario
DB_PASS=clave
DB_CHARSET=utf8mb4

GA_MEASUREMENT_ID=G-XXXXXXXXXX
```

⚠️ Importante:
- `.env` NO debe ser accesible públicamente

---

## 🔥 Funcionalidades

### Encender vela
- Inserta en DB
- Envía correo
- Redirige a velas

### Visualización
- Listado dinámico
- Paginación
- Modal detalle

### Compartir
- Link único
- Integración WhatsApp

### Admin
- Login Google
- Gestión usuarios
- Logs
- Configuración

---

## 🎨 Diseño

- Color principal: #AB0A3D
- Tipografías: Raleway / Roboto

---

## 🛡️ Seguridad

- Protección .env
- Sanitización inputs
- Logs
- Validación backend

---

## 🧑‍💻 Consideraciones

- Compatible con PHP 5.6
- Evitar sintaxis moderna

---

## 🚀 Despliegue

1. Subir archivos
2. Configurar .env
3. Validar DB
4. Probar correos
