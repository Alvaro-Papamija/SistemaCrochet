# Control de Cambios y Versionamiento con Liquibase
**Maraña Crochet Atelier — Taller Artesanal de Amigurumis (Neiva, Huila)**

Este directorio contiene la arquitectura de **Control de Cambios y Versionamiento Declarativo de Base de Datos** utilizando **Liquibase 4.29.2**, aplicada sobre el esquema de auditoría e histórico inmutable: `marana_crochet_historico`.

---

## 📁 Estructura del Directorio

```text
liquibase/
├── changelog/
│   ├── changelog-master.xml               # Archivo maestro que orquesta todas las versiones
│   ├── v1.0/
│   │   ├── 01-create-historico-pedidos.xml
│   │   ├── 02-create-historico-inventario.xml
│   │   ├── 03-create-historico-comprobantes.xml
│   │   ├── 04-create-historico-precios.xml
│   │   ├── 05-create-historico-auditoria-usuarios.xml
│   │   └── 06-create-triggers-historico.xml
│   └── v1.1/
│       └── 01-create-triggers-english-schema.xml  # Triggers para sincronizar orders y products
├── liquibase.properties                  # Configuración JDBC y credenciales de conexión
├── liquibase-update.bat                  # Script para aplicar cambios pendientes
├── liquibase-status.bat                  # Script para validar estado de migraciones
├── liquibase-history.bat                 # Script para auditar el historial de despliegues
└── README.md                             # Documentación de referencia
```

---

## 🗄️ Esquema Histórico: `marana_crochet_historico`

| Tabla | Propósito | Desencadenador / Origen |
| :--- | :--- | :--- |
| `databasechangelog` | Registro inmutable de cada changeset ejecutado por Liquibase, autor, hash MD5 y fecha. | Liquibase Core |
| `databasechangeloglock` | Semáforo de concurrencia para evitar ejecuciones simultáneas de migraciones. | Liquibase Core |
| `historico_pedidos` | Auditoría de transiciones de estado de pedidos (`revision` -> `taller` -> `despachado` -> `entregado`). | Trigger MySQL en `orders` y `pedidos` |
| `historico_inventario` | Trazabilidad de entradas, salidas, ventas y consumo de lanas e insumos. | Trigger MySQL en `products` y `productos` |
| `historico_comprobantes` | Auditoría de aprobación o rechazo de comprobantes TEF Bancolombia / Wompi. | Trigger MySQL en `comprobantes` |
| `historico_precios` | Registro histórico de variaciones de precios y costo de insumos en COP. | Trigger MySQL en `products` |
| `historico_auditoria_usuarios` | Registro de eventos de autenticación, inicios de sesión y seguridad. | Trigger / Capa de Seguridad PHP |

---

## 🚀 Comandos Rápidos (CLI)

Ejecuta estos comandos situándote en la carpeta `liquibase/`:

### 1. Aplicar cambios pendientes a la base de datos
```bash
liquibase update
# O haciendo doble clic en liquibase-update.bat
```

### 2. Verificar si hay cambios pendientes por aplicar
```bash
liquibase status
# O haciendo doble clic en liquibase-status.bat
```

### 3. Consultar el historial de ejecuciones y despliegues
```bash
liquibase history
# O haciendo doble clic en liquibase-history.bat
```

### 4. Revertir el último ChangeSet aplicado (Rollback)
```bash
liquibase rollback-count 1
```

---

## 🌐 Visualización en la Aplicación Web

El estado de Liquibase y las tablas históricas se visualizan en tiempo real desde el panel de administración:
- **URL**: `http://localhost/marana/php_app/admin/historico.php`
- Incluye métricas KPI, lista completa de ChangeSets, tabla de triggers activos y detalle de cada histórico.

