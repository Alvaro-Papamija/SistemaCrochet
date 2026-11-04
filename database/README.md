# Esquemas de Base de Datos — Maraña Crochet

Este directorio contiene las definiciones DDL y datos semilla para el sistema Maraña Crochet:

1. **`schema.sql`**: Esquema operacional principal (`marana_crochet`)
   - Catálogo de productos y amigurumis (`products`)
   - Cuentas de clientes (`customers`)
   - Registro de pedidos y comprobantes (`orders`)
   - Reseñas verificadas (`reviews`)
   - Datos semilla iniciales en pesos colombianos (COP).

2. **`schema_historico.sql`**: Esquema de auditoría y base de datos histórica (`marana_crochet_historico`)
   - Control de cambios versionado con Liquibase (`databasechangelog`, `databasechangeloglock`)
   - Historial de pedidos (`historico_pedidos`)
   - Historial de inventario y movimientos de lana (`historico_inventario`)
   - Historial de comprobantes de pago (`historico_comprobantes`)
   - Historial de variaciones de precios (`historico_precios`)
   - Auditoría de seguridad y accesos (`historico_auditoria_usuarios`).

> **Nota sobre Migraciones:** Para gestionar cambios en `marana_crochet_historico` se recomienda utilizar **Liquibase** ubicado en la carpeta `liquibase/`.

