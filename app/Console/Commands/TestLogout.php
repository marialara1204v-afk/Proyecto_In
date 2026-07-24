<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class TestLogout extends Command
{
    protected $signature = 'test:logout';
    protected $description = 'Verifica que el logout esté configurado correctamente';

    public function handle()
    {
        $this->info('🔐 VERIFICANDO CONFIGURACIÓN DE LOGOUT\n');

        // 1. Verificar rutas
        $this->line('📋 VERIFICANDO RUTAS...');
        $routes = collect(Route::getRoutes()->getRoutes());
        
        $logoutRoutes = $routes->filter(function ($route) {
            return str_contains($route->uri, 'logout');
        });

        if ($logoutRoutes->count() === 0) {
            $this->error("  ❌ No hay rutas de logout configuradas");
            return 1;
        }

        foreach ($logoutRoutes as $route) {
            $methods = implode('|', $route->methods);
            $uri = $route->uri;
            $this->line("  ✓ {$methods} /{$uri}");
        }

        // 2. Verificar middleware
        $this->line('\n⚙️ VERIFICANDO MIDDLEWARE...');
        
        $hasAuthLogout = false;
        $hasSimpleLogout = false;

        foreach ($logoutRoutes as $route) {
            $uri = $route->uri;
            $middleware = $route->middleware();
            
            if ($uri === 'logout') {
                $hasAuthLogout = true;
                if (in_array('auth', $middleware)) {
                    $this->line("  ✓ POST /logout: Requiere autenticación");
                } else {
                    $this->warn("  ⚠️ POST /logout: No requiere autenticación");
                }
            }

            if (str_contains($uri, 'logout-simple')) {
                $hasSimpleLogout = true;
                if (!in_array('web', $middleware)) {
                    $this->line("  ✓ {$uri}: Sin middleware web (funciona sin sesión)");
                } else {
                    $this->line("  ℹ️ {$uri}: Tiene middleware web");
                }
            }
        }

        // 3. Verificar controlador
        $this->line('\n🎯 VERIFICANDO CONTROLADOR...');
        
        $controller = 'App\Http\Controllers\Auth\AuthenticatedSessionController';
        if (class_exists($controller)) {
            $this->line("  ✓ Controlador existe: {$controller}");
            
            $reflection = new \ReflectionClass($controller);
            $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
            $methodNames = array_map(fn($m) => $m->name, $methods);
            
            if (in_array('destroy', $methodNames)) {
                $this->line("  ✓ Método destroy() existe");
            }
            
            if (in_array('destroySimple', $methodNames)) {
                $this->line("  ✓ Método destroySimple() existe (fallback)");
            } else {
                $this->error("  ❌ Método destroySimple() NO existe");
            }
        } else {
            $this->error("  ❌ Controlador no encontrado");
        }

        // 4. Resumen
        $this->info('\n' . str_repeat('═', 50));
        $this->line('RESULTADO FINAL:');
        
        $allGood = $logoutRoutes->count() > 0 && $hasSimpleLogout && class_exists($controller);
        
        if ($allGood) {
            $this->info('  ✅ Logout está correctamente configurado');
            $this->line('  El logout funciona incluso sin sesión activa');
        } else {
            $this->error('  ❌ Hay problemas con la configuración de logout');
        }

        $this->info(str_repeat('═', 50));

        return $allGood ? 0 : 1;
    }
}
