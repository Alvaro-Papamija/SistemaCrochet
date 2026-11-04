<?php
declare(strict_types=1);

// admin/historico.php - Auditoría Histórica y Control de Cambios con Liquibase
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Exigir inicio de sesión
requireAdminAuth();

$baseUrl = getBaseUrl();
$adminUser = getAdminUser();

// 1. Obtener registros de control de cambios de Liquibase
$liquibaseChanges = [];
$liquibaseLock = null;
$liquibaseError = null;

try {
    $liquibaseChanges = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`databasechangelog` 
        ORDER BY `DATEEXECUTED` DESC, `ORDEREXECUTED` DESC
    ")->fetchAll();
    $liquibaseLock = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`databasechangeloglock` LIMIT 1
    ")->fetch();
} catch (Throwable $e) {
    $liquibaseError = $e->getMessage();
}

// 2. Obtener registros de las tablas históricas
$histPedidos = [];
$histInventario = [];
$histComprobantes = [];
$histPrecios = [];
$histUsuarios = [];
$triggers = [];

try {
    $histPedidos = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`historico_pedidos` 
        ORDER BY `created_at` DESC LIMIT 50
    ")->fetchAll();
    $histInventario = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`historico_inventario` 
        ORDER BY `created_at` DESC LIMIT 50
    ")->fetchAll();
    $histComprobantes = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`historico_comprobantes` 
        ORDER BY `created_at` DESC LIMIT 50
    ")->fetchAll();
    $histPrecios = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`historico_precios` 
        ORDER BY `created_at` DESC LIMIT 50
    ")->fetchAll();
    $histUsuarios = $pdo->query("
        SELECT * FROM `marana_crochet_historico`.`historico_auditoria_usuarios` 
        ORDER BY `created_at` DESC LIMIT 50
    ")->fetchAll();
    $triggers = $pdo->query("
        SHOW TRIGGERS FROM `marana_crochet`
    ")->fetchAll();
} catch (Throwable $e) {
    // Si alguna tabla aún no tiene datos o no existe
}

$activeTab = $_GET['tab'] ?? 'liquibase';
$pageTitle = 'Histórico & Liquibase | Taller Maraña';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="padding-top: 2rem; padding-bottom: 5rem;">

  <!-- Sub-Navegación del Backoffice -->
  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem; border-bottom: 1px solid var(--color-neutral-border); padding-bottom: 1rem;">
    <div>
      <h1 style="font-size: 1.65rem; margin-bottom: 0.25rem;">Control de Cambios &amp; Base de Datos Histórica</h1>
      <p style="font-size: 0.85rem;">Trazabilidad inmutable de pedidos, inventario, auditoría y versionamiento con <strong>Liquibase</strong>.</p>
    </div>

    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center;">
      <a href="<?= $baseUrl ?>/admin/orders.php" class="btn btn-outlined btn-sm">Pedidos y Pagos</a>
      <a href="<?= $baseUrl ?>/admin/inventory.php" class="btn btn-outlined btn-sm">Inventario de Lanas</a>
      <a href="<?= $baseUrl ?>/admin/reviews.php" class="btn btn-outlined btn-sm">Moderación de Reseñas</a>
      <a href="<?= $baseUrl ?>/admin/historico.php" class="btn btn-primary btn-sm" style="background: #059669; border-color: #059669;">📜 Histórico &amp; Liquibase</a>
      <a href="<?= $baseUrl ?>/admin/status.php" class="btn btn-outlined btn-sm" style="border-color: #9D4EDD; color: #7B2CBF;">⚡ Motor PHP 8.2+</a>
      <a href="<?= $baseUrl ?>/index.php" class="btn btn-outlined btn-sm">Ver Tienda</a>

      <!-- Usuario Logueado & Botón Cerrar Sesión -->
      <div style="margin-left: 0.5rem; display: flex; align-items: center; gap: 0.5rem; background: var(--color-primary-bg); border: 1px solid var(--color-primary-border); padding: 0.35rem 0.65rem; border-radius: var(--radius-sm); font-size: 0.78rem;">
        <span style="font-weight: 700; color: var(--color-primary);">👤 <?= h($adminUser['name'] ?? 'Admin') ?> (Neiva)</span>
        <a href="<?= $baseUrl ?>/admin/logout.php" title="Cerrar Sesión" style="color: var(--color-danger); text-decoration: none; font-weight: 700; border-left: 1px solid var(--color-primary-border); padding-left: 0.5rem;">
          Salir
        </a>
      </div>
    </div>
  </div>

  <!-- Banner de Estado Liquibase -->
  <div style="background: linear-gradient(135deg, #064E3B 0%, #059669 100%); color: #fff; padding: 1.75rem 2rem; border-radius: 12px; margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
      <div style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.85;">DevOps &amp; Database Migrations</div>
      <h2 style="font-size: 1.5rem; margin-top: 0.2rem; font-weight: 700;">Liquibase 4.29.2 · marana_crochet_historico</h2>
      <p style="font-size: 0.85rem; opacity: 0.9; margin-top: 0.35rem;">
        Control de cambios declarativo con ChangeSets XML, hash de verificación MD5, y triggers reactivos en MySQL/MariaDB.
      </p>
    </div>
    <div style="background: rgba(255,255,255,0.15); padding: 0.8rem 1.2rem; border-radius: 8px; text-align: right; backdrop-filter: blur(4px);">
      <div style="font-size: 0.75rem; text-transform: uppercase; opacity: 0.85;">Total ChangeSets Ejecutados</div>
      <div style="font-size: 1.8rem; font-weight: 800;"><?= count($liquibaseChanges) ?></div>
      <div style="font-size: 0.72rem; opacity: 0.9;">Lock: <?= ($liquibaseLock && !$liquibaseLock['LOCKED']) ? '🔓 Desbloqueado' : '🔒 Bloqueado' ?></div>
    </div>
  </div>

  <?php if ($liquibaseError): ?>
    <div class="alert alert-warning" style="margin-bottom: 2rem;">
      <strong>Advertencia de conexión:</strong> <?= h($liquibaseError) ?>
      <p style="margin-top: 0.5rem; font-size: 0.85rem;">Asegúrate de haber ejecutado <code>liquibase update</code> o importado el archivo <code>database/schema_historico.sql</code>.</p>
    </div>
  <?php endif; ?>

  <!-- KPI Cards -->
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid #059669;">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #059669;">Liquibase Changesets</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark);"><?= count($liquibaseChanges) ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light);">Versiones aplicadas</div>
    </div>
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid #3B82F6;">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #3B82F6;">Histórico Pedidos</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark);"><?= count($histPedidos) ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light);">Transiciones de estado</div>
    </div>
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid #F59E0B;">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #F59E0B;">Histórico Inventario</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark);"><?= count($histInventario) ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light);">Movimientos de stock</div>
    </div>
    <div class="card-panel" style="margin-bottom: 0; padding: 1.25rem; border-left: 3px solid #8B5CF6;">
      <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #8B5CF6;">Triggers Activos</div>
      <div style="font-size: 1.65rem; font-weight: 800; color: var(--color-neutral-dark);"><?= count($triggers) ?></div>
      <div style="font-size: 0.75rem; color: var(--color-neutral-light);">En marana_crochet</div>
    </div>
  </div>

  <!-- Pestañas de navegación -->
  <div class="filters-bar" style="margin-top: 0; margin-bottom: 1.5rem;">
    <div class="category-pills">
      <a href="?tab=liquibase" class="cat-pill <?= ($activeTab === 'liquibase') ? 'active' : '' ?>">Liquibase Changelog (<?= count($liquibaseChanges) ?>)</a>
      <a href="?tab=pedidos" class="cat-pill <?= ($activeTab === 'pedidos') ? 'active' : '' ?>">Histórico de Pedidos (<?= count($histPedidos) ?>)</a>
      <a href="?tab=inventario" class="cat-pill <?= ($activeTab === 'inventario') ? 'active' : '' ?>">Movimientos Inventario (<?= count($histInventario) ?>)</a>
      <a href="?tab=precios" class="cat-pill <?= ($activeTab === 'precios') ? 'active' : '' ?>">Histórico Precios (<?= count($histPrecios) ?>)</a>
      <a href="?tab=triggers" class="cat-pill <?= ($activeTab === 'triggers') ? 'active' : '' ?>">Triggers MySQL (<?= count($triggers) ?>)</a>
    </div>
  </div>

  <!-- Contenido de las Pestañas -->
  <?php if ($activeTab === 'liquibase'): ?>
    <div class="card-panel">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
        <h3 style="margin: 0; font-size: 1.1rem; color: var(--color-neutral-dark);">Registro de Versionamiento (databasechangelog)</h3>
        <span style="font-size: 0.78rem; color: var(--color-neutral-light); font-family: monospace;">Esquema: marana_crochet_historico</span>
      </div>

      <div style="overflow-x: auto;">
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <thead>
            <tr style="background: var(--color-neutral-bg); border-bottom: 2px solid var(--color-neutral-border); text-align: left;">
              <th style="padding: 0.75rem 1rem;">ID ChangeSet</th>
              <th style="padding: 0.75rem 1rem;">Autor</th>
              <th style="padding: 0.75rem 1rem;">Archivo Changelog</th>
              <th style="padding: 0.75rem 1rem;">Fecha Ejecución</th>
              <th style="padding: 0.75rem 1rem;">MD5 Sum</th>
              <th style="padding: 0.75rem 1rem;">Descripción</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($liquibaseChanges)): ?>
              <tr>
                <td colspan="6" style="padding: 2rem; text-align: center; color: var(--color-neutral-light);">
                  No hay registros en databasechangelog aún.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($liquibaseChanges as $c): ?>
                <tr style="border-bottom: 1px solid var(--color-neutral-border);">
                  <td style="padding: 0.75rem 1rem; font-weight: 700; color: #059669; font-family: monospace;">
                    <?= h($c['ID']) ?>
                  </td>
                  <td style="padding: 0.75rem 1rem;">
                    <span style="background: var(--color-neutral-bg); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">
                      <?= h($c['AUTHOR']) ?>
                    </span>
                  </td>
                  <td style="padding: 0.75rem 1rem; font-family: monospace; font-size: 0.78rem; color: var(--color-neutral-dark);">
                    <?= h($c['FILENAME']) ?>
                  </td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.78rem; color: var(--color-neutral-light);">
                    <?= h($c['DATEEXECUTED']) ?>
                  </td>
                  <td style="padding: 0.75rem 1rem; font-family: monospace; font-size: 0.72rem; color: var(--color-neutral-light);">
                    <?= h(substr($c['MD5SUM'] ?? '', 0, 16)) ?>...
                  </td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.8rem; color: var(--color-neutral-dark);">
                    <?= h($c['DESCRIPTION'] ?? $c['COMMENTS'] ?? '-') ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php elseif ($activeTab === 'pedidos'): ?>
    <div class="card-panel">
      <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--color-neutral-dark);">Auditoría Histórica de Pedidos (historico_pedidos)</h3>
      <div style="overflow-x: auto;">
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <thead>
            <tr style="background: var(--color-neutral-bg); border-bottom: 2px solid var(--color-neutral-border); text-align: left;">
              <th style="padding: 0.75rem 1rem;">ID</th>
              <th style="padding: 0.75rem 1rem;">Orden / Pedido</th>
              <th style="padding: 0.75rem 1rem;">Cliente</th>
              <th style="padding: 0.75rem 1rem;">Transición Estado</th>
              <th style="padding: 0.75rem 1rem;">Motivo / Origen</th>
              <th style="padding: 0.75rem 1rem;">Responsable</th>
              <th style="padding: 0.75rem 1rem;">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($histPedidos)): ?>
              <tr>
                <td colspan="7" style="padding: 2rem; text-align: center; color: var(--color-neutral-light);">
                  No hay transiciones de pedidos registradas aún. Al cambiar el estado de un pedido en el panel o por compras, el trigger lo registrará automáticamente.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($histPedidos as $hp): ?>
                <tr style="border-bottom: 1px solid var(--color-neutral-border);">
                  <td style="padding: 0.75rem 1rem; font-weight: 700; color: var(--color-neutral-light);">#<?= h((string)$hp['id']) ?></td>
                  <td style="padding: 0.75rem 1rem; font-weight: 700; color: var(--color-primary); font-family: monospace;"><?= h($hp['pedido_id']) ?></td>
                  <td style="padding: 0.75rem 1rem;"><?= h($hp['cliente_nombre'] ?? 'Invitado') ?></td>
                  <td style="padding: 0.75rem 1rem;">
                    <span style="text-decoration: line-through; opacity: 0.6; font-size: 0.75rem;"><?= h($hp['estado_anterior'] ?: 'N/A') ?></span>
                    &rarr;
                    <span style="font-weight: 700; color: #059669;"><?= h($hp['estado_nuevo']) ?></span>
                  </td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.8rem;"><?= h($hp['motivo_cambio'] ?? '-') ?></td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.8rem;"><span class="badge" style="background: var(--color-primary-bg); color: var(--color-primary);"><?= h($hp['usuario_responsable'] ?? 'Sistema') ?></span></td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.78rem; color: var(--color-neutral-light);"><?= h($hp['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php elseif ($activeTab === 'inventario'): ?>
    <div class="card-panel">
      <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--color-neutral-dark);">Movimientos Históricos de Inventario (historico_inventario)</h3>
      <div style="overflow-x: auto;">
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <thead>
            <tr style="background: var(--color-neutral-bg); border-bottom: 2px solid var(--color-neutral-border); text-align: left;">
              <th style="padding: 0.75rem 1rem;">ID</th>
              <th style="padding: 0.75rem 1rem;">SKU</th>
              <th style="padding: 0.75rem 1rem;">Producto</th>
              <th style="padding: 0.75rem 1rem;">Stock Anterior</th>
              <th style="padding: 0.75rem 1rem;">Stock Nuevo</th>
              <th style="padding: 0.75rem 1rem;">Movimiento</th>
              <th style="padding: 0.75rem 1rem;">Tipo</th>
              <th style="padding: 0.75rem 1rem;">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($histInventario)): ?>
              <tr>
                <td colspan="8" style="padding: 2rem; text-align: center; color: var(--color-neutral-light);">
                  No hay movimientos de inventario registrados aún.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($histInventario as $hi): ?>
                <tr style="border-bottom: 1px solid var(--color-neutral-border);">
                  <td style="padding: 0.75rem 1rem; font-weight: 700; color: var(--color-neutral-light);">#<?= h((string)$hi['id']) ?></td>
                  <td style="padding: 0.75rem 1rem; font-family: monospace; font-weight: 700; color: var(--color-primary);"><?= h($hi['sku']) ?></td>
                  <td style="padding: 0.75rem 1rem;"><?= h($hi['nombre_producto'] ?? '-') ?></td>
                  <td style="padding: 0.75rem 1rem; text-align: center;"><?= h((string)$hi['stock_anterior']) ?></td>
                  <td style="padding: 0.75rem 1rem; text-align: center; font-weight: 700;"><?= h((string)$hi['stock_nuevo']) ?></td>
                  <td style="padding: 0.75rem 1rem; text-align: center;">
                    <span style="font-weight: 800; color: <?= ((int)$hi['cantidad_movimiento'] < 0) ? '#DC2626' : '#059669' ?>;">
                      <?= ((int)$hi['cantidad_movimiento'] > 0 ? '+' : '') . h((string)$hi['cantidad_movimiento']) ?>
                    </span>
                  </td>
                  <td style="padding: 0.75rem 1rem;">
                    <span style="font-size: 0.72rem; padding: 0.2rem 0.5rem; border-radius: 4px; background: var(--color-neutral-bg); font-weight: 600;">
                      <?= h($hi['tipo_movimiento']) ?>
                    </span>
                  </td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.78rem; color: var(--color-neutral-light);"><?= h($hi['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php elseif ($activeTab === 'precios'): ?>
    <div class="card-panel">
      <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--color-neutral-dark);">Histórico de Precios &amp; Insumos (historico_precios)</h3>
      <div style="overflow-x: auto;">
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <thead>
            <tr style="background: var(--color-neutral-bg); border-bottom: 2px solid var(--color-neutral-border); text-align: left;">
              <th style="padding: 0.75rem 1rem;">ID</th>
              <th style="padding: 0.75rem 1rem;">SKU</th>
              <th style="padding: 0.75rem 1rem;">Producto</th>
              <th style="padding: 0.75rem 1rem;">Precio Anterior</th>
              <th style="padding: 0.75rem 1rem;">Precio Nuevo</th>
              <th style="padding: 0.75rem 1rem;">Motivo</th>
              <th style="padding: 0.75rem 1rem;">Fecha</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($histPrecios)): ?>
              <tr>
                <td colspan="7" style="padding: 2rem; text-align: center; color: var(--color-neutral-light);">
                  No hay cambios de precios registrados aún.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($histPrecios as $hp): ?>
                <tr style="border-bottom: 1px solid var(--color-neutral-border);">
                  <td style="padding: 0.75rem 1rem;">#<?= h((string)$hp['id']) ?></td>
                  <td style="padding: 0.75rem 1rem; font-family: monospace; font-weight: 700;"><?= h($hp['sku']) ?></td>
                  <td style="padding: 0.75rem 1rem;"><?= h($hp['nombre_producto'] ?? '-') ?></td>
                  <td style="padding: 0.75rem 1rem; color: var(--color-neutral-light); text-decoration: line-through;"><?= formatCOP((int)$hp['precio_anterior_cop']) ?></td>
                  <td style="padding: 0.75rem 1rem; font-weight: 700; color: #059669;"><?= formatCOP((int)$hp['precio_nuevo_cop']) ?></td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.8rem;"><?= h($hp['motivo'] ?? '-') ?></td>
                  <td style="padding: 0.75rem 1rem; font-size: 0.78rem; color: var(--color-neutral-light);"><?= h($hp['created_at']) ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php elseif ($activeTab === 'triggers'): ?>
    <div class="card-panel">
      <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--color-neutral-dark);">Triggers Reactivos en Base de Datos (marana_crochet)</h3>
      <p style="font-size: 0.85rem; color: var(--color-neutral-light); margin-bottom: 1rem;">
        Estos triggers sincronizan instantáneamente cada UPDATE en las tablas operacionales hacia la base de datos de histórico.
      </p>
      <div style="overflow-x: auto;">
        <table class="table-custom" style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
          <thead>
            <tr style="background: var(--color-neutral-bg); border-bottom: 2px solid var(--color-neutral-border); text-align: left;">
              <th style="padding: 0.75rem 1rem;">Nombre del Trigger</th>
              <th style="padding: 0.75rem 1rem;">Evento</th>
              <th style="padding: 0.75rem 1rem;">Tabla Origen</th>
              <th style="padding: 0.75rem 1rem;">Timing</th>
              <th style="padding: 0.75rem 1rem;">Estado</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($triggers as $trg): ?>
              <tr style="border-bottom: 1px solid var(--color-neutral-border);">
                <td style="padding: 0.75rem 1rem; font-family: monospace; font-weight: 700; color: #059669;"><?= h($trg['Trigger']) ?></td>
                <td style="padding: 0.75rem 1rem;"><span class="badge" style="background: var(--color-neutral-bg);"><?= h($trg['Event']) ?></span></td>
                <td style="padding: 0.75rem 1rem; font-weight: 700; font-family: monospace;"><?= h($trg['Table']) ?></td>
                <td style="padding: 0.75rem 1rem;"><?= h($trg['Timing']) ?></td>
                <td style="padding: 0.75rem 1rem;">
                  <span style="color: #059669; font-weight: 700; font-size: 0.78rem;">✓ Activo y Sincronizado</span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- Guía de Comandos Liquibase -->
  <div class="card-panel" style="margin-top: 2rem; background: #0f172a; color: #e2e8f0; border-radius: 12px; padding: 1.5rem;">
    <h4 style="margin: 0 0 0.75rem 0; color: #38bdf8; font-size: 0.95rem;">Guía Rápida de Comandos Liquibase (CLI)</h4>
    <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 1rem;">
      Puedes ejecutar cualquiera de las siguientes operaciones desde la carpeta <code>liquibase/</code> del proyecto:
    </p>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; font-family: monospace; font-size: 0.78rem;">
      <div style="background: #1e293b; padding: 0.85rem; border-radius: 6px; border-left: 3px solid #38bdf8;">
        <strong style="color: #f1f5f9;">Aplicar cambios pendientes:</strong>
        <pre style="margin: 0.35rem 0 0 0; color: #a5f3fc;">liquibase update</pre>
      </div>
      <div style="background: #1e293b; padding: 0.85rem; border-radius: 6px; border-left: 3px solid #a855f7;">
        <strong style="color: #f1f5f9;">Ver estado y cambios por aplicar:</strong>
        <pre style="margin: 0.35rem 0 0 0; color: #e9d5ff;">liquibase status</pre>
      </div>
      <div style="background: #1e293b; padding: 0.85rem; border-radius: 6px; border-left: 3px solid #10b981;">
        <strong style="color: #f1f5f9;">Ver historial de despliegues:</strong>
        <pre style="margin: 0.35rem 0 0 0; color: #6ee7b7;">liquibase history</pre>
      </div>
      <div style="background: #1e293b; padding: 0.85rem; border-radius: 6px; border-left: 3px solid #f43f5e;">
        <strong style="color: #f1f5f9;">Revertir el último ChangeSet:</strong>
        <pre style="margin: 0.35rem 0 0 0; color: #fecdd3;">liquibase rollback-count 1</pre>
      </div>
    </div>
  </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

