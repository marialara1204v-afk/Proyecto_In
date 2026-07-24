<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    private function hourExpression(): string
    {
        return match (DB::connection()->getDriverName()) {
            'sqlite' => "CAST(strftime('%H', created_at) AS INTEGER)",
            'pgsql'  => 'EXTRACT(HOUR FROM created_at)',
            default  => 'HOUR(created_at)',
        };
    }

    private function resolveDate(Request $request): Carbon
    {
        return Carbon::parse($request->input('date', today()->toDateString()))->startOfDay();
    }

    private function buildBaseData(Carbon $date): array
    {
        $endDate = $date->copy()->endOfDay();
        $hourExpr = $this->hourExpression();

        $orders = Order::with(['items.product', 'table', 'user'])
            ->whereBetween('created_at', [$date, $endDate])
            ->get();

        $paidOrders = $orders->where('status', 'pagado');

        $salesByHour = Order::select(DB::raw("{$hourExpr} as hour"), DB::raw('SUM(total) as total'))
            ->whereBetween('created_at', [$date, $endDate])
            ->where('status', 'pagado')
            ->groupBy(DB::raw($hourExpr))
            ->orderBy(DB::raw($hourExpr))
            ->get()
            ->keyBy('hour');

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as revenue'))
            ->with('product.category')
            ->whereHas('order', fn ($query) => $query->whereBetween('created_at', [$date, $endDate])->where('status', 'pagado'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        $inventoryItems = InventoryItem::orderBy('category')->orderBy('name')->get();

        // Desglose por método de pago
        $payments = OrderPayment::whereBetween('created_at', [$date, $endDate])->get();
        $paymentBreakdown = [
            'cash'     => $payments->where('payment_method', 'efectivo')->sum('amount'),
            'card'     => $payments->where('payment_method', 'tarjeta')->sum('amount'),
            'transfer' => $payments->where('payment_method', 'transferencia')->sum('amount'),
            'other'    => $payments->where('payment_method', 'otro')->sum('amount'),
        ];

        return [
            'date' => $date,
            'totalSales' => $paidOrders->sum('total'),
            'totalOrders' => $paidOrders->count(),
            'avgTicket' => $paidOrders->count() > 0 ? round($paidOrders->sum('total') / $paidOrders->count(), 2) : 0,
            'totalTips' => $payments->sum('tip'),
            'activeTables' => Table::whereIn('status', ['ocupada', 'pedido_listo'])->count(),
            'criticalInventory' => InventoryItem::critical()->active()->orderBy('name')->get(),
            'inventoryItems' => $inventoryItems,
            'salesByHour' => $salesByHour,
            'topProducts' => $topProducts,
            'paymentBreakdown' => $paymentBreakdown,
        ];
    }

    public function __invoke(Request $request)
    {
        $date = $this->resolveDate($request);
        return view('admin.report', $this->buildBaseData($date));
    }

    public function export(Request $request): StreamedResponse
    {
        $date = $this->resolveDate($request);
        $data = $this->buildBaseData($date);

        $filename = 'cierre-diario-' . $date->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        return response()->stream(function () use ($data): void {
            $out = fopen('php://output', 'w');
            if (! $out) {
                return;
            }

            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Reporte de Cierre Diario']);
            fputcsv($out, ['Fecha', $data['date']->format('d/m/Y')]);
            fputcsv($out, []);
            fputcsv($out, ['Resumen']);
            fputcsv($out, ['Ventas totales', number_format($data['totalSales'], 2, '.', '')]);
            fputcsv($out, ['Ordenes pagadas', $data['totalOrders']]);
            fputcsv($out, ['Ticket promedio', number_format($data['avgTicket'], 2, '.', '')]);
            fputcsv($out, ['Propinas', number_format($data['totalTips'], 2, '.', '')]);
            fputcsv($out, ['Mesas activas', $data['activeTables']]);
            fputcsv($out, []);
            fputcsv($out, ['Inventario']);
            fputcsv($out, ['Insumo', 'Categoria', 'Stock actual', 'Stock minimo', 'Unidad', 'Estado']);

            /** @var Collection $inventory */
            $inventory = $data['inventoryItems'];
            foreach ($inventory as $item) {
                fputcsv($out, [
                    $item->name,
                    $item->category,
                    number_format((float) $item->stock, 2, '.', ''),
                    number_format((float) $item->min_stock, 2, '.', ''),
                    $item->unit,
                    $item->statusLabel(),
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $date = $this->resolveDate($request);
        $data = $this->buildBaseData($date);

        $pdf = Pdf::loadView('admin.report-pdf', $data);
        $pdf->setPaper('letter');

        return $pdf->download('cierre-diario-' . $date->format('Y-m-d') . '.pdf');
    }
}