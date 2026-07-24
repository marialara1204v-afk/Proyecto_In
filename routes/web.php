<?php

use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\DigitalMenuController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\TicketController;
use App\Livewire\Admin\AdminDashboard;
use App\Livewire\Admin\AuditLogPanel;
use App\Livewire\Admin\InventoryManager;
use App\Livewire\Admin\ProductManager;
use App\Livewire\Admin\UserManager;
use App\Livewire\Cashier\CashierPanel;
use App\Livewire\Kitchen\KitchenBoard;
use App\Livewire\Waiter\TableMap;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('login'));

Route::get('/menu/{table?}', MenuController::class)->name('menu');

// ── MENÚ DIGITAL (Público, sin autenticación) ───────────────
Route::prefix('qr-menu')->name('menu.')->group(function () {
    Route::get('/', [DigitalMenuController::class, 'index'])->name('digital');
    Route::get('/categoria/{category}', [DigitalMenuController::class, 'category'])->name('category');
    Route::get('/producto/{product}', [DigitalMenuController::class, 'product'])->name('product');
});

// ── AUTO-SERVICIO QR (Público, por mesa) ────────────────────
Route::get('/qr-menu/{table}', \App\Livewire\Guest\SelfServiceOrder::class)
    ->middleware('throttle:60,1')
    ->name('menu.selfservice');

require __DIR__ . '/auth.php';

// ── Mesero ──────────────────────────────────────────────────
Route::middleware(['auth', 'role:mesero,admin'])
    ->prefix('mesero')->name('waiter.')
    ->group(function () {
        Route::get('/mesas', TableMap::class)->name('tables');
    });

// ── Cocina / Barra ──────────────────────────────────────────
Route::middleware(['auth', 'role:cocina,bartender,admin'])
    ->prefix('cocina')->name('kitchen.')
    ->group(function () {
        Route::get('/comandas', KitchenBoard::class)->name('orders');
    });

// ── Caja ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:mesero,admin'])
    ->prefix('caja')->name('cashier.')
    ->group(function () {
        Route::get('/', CashierPanel::class)->name('index');
    });

// ── Administrador ───────────────────────────────────────────
Route::middleware(['auth', 'role:admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/dashboard',  AdminDashboard::class)->name('dashboard');
        Route::get('/cierre-caja', \App\Livewire\Admin\CashClosingPanel::class)->name('cash-closing');
        Route::get('/inventario',  InventoryManager::class)->name('inventory');
        Route::get('/productos',  ProductManager::class)->name('products');
        Route::get('/menu-digital', \App\Livewire\Admin\QRCodeManager::class)->name('menu-digital');
        Route::get('/reporte-diario', AdminReportController::class)->name('report');
        Route::get('/reporte-diario/export', [AdminReportController::class, 'export'])
            ->middleware('throttle:30,1')
            ->name('report.export');
        Route::get('/reporte-diario/pdf', [AdminReportController::class, 'exportPdf'])
            ->middleware('throttle:30,1')
            ->name('report.pdf');
        Route::get('/usuarios',   UserManager::class)->name('users');
        Route::get('/auditoria',  AuditLogPanel::class)->name('audit-log');
        
        // Rutas para cargar imágenes de productos
        Route::get('/cargar-imagenes', [ProductImageController::class, 'uploadForm'])->name('upload-images-form');
        Route::post('/cargar-imagen', [ProductImageController::class, 'upload'])->name('cargar-imagen');
    });

// ── Ticket (Impresión) ──────────────────────────────────────
Route::middleware(['auth'])
    ->get('/ticket/{orderId}/print', [TicketController::class, 'print'])
    ->name('ticket.print');