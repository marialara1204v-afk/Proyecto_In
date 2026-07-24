<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Table;
use App\Services\QRCodeService;
use Illuminate\View\View;
use Livewire\Component;

class QRCodeManager extends Component
{
    public string $qrType = 'menu'; // menu, table, category, product
    public ?int $selectedCategoryId = null;
    public ?int $selectedProductId = null;
    public ?int $selectedTableId = null;
    public bool $showPreview = false;
    public ?string $generatedQRUrl = null;
    public string $qrFileName = '';

    public function mount(): void
    {
        $this->generateMenuQR();
    }

    public function generateMenuQR(): void
    {
        $this->qrType = 'menu';
        $this->selectedTableId = null;
        $this->generatedQRUrl = QRCodeService::generateMenuQR();
        $this->qrFileName = 'menu-digital';
        $this->showPreview = true;
    }

    public function generateTableQR(int $tableId): void
    {
        $table = Table::findOrFail($tableId);
        $this->qrType = 'table';
        $this->selectedTableId = $tableId;
        $this->generatedQRUrl = QRCodeService::generateTableQR($table->number);
        $this->qrFileName = "qr-mesa-{$table->number}";
        $this->showPreview = true;
    }

    public function generateCategoryQR(int $categoryId): void
    {
        $category = Category::findOrFail($categoryId);
        $this->qrType = 'category';
        $this->selectedCategoryId = $categoryId;
        $this->generatedQRUrl = QRCodeService::generateCategoryQR($categoryId);
        $this->qrFileName = "menu-categoria-{$category->name}";
        $this->showPreview = true;
    }

    public function generateProductQR(int $productId): void
    {
        $product = Product::findOrFail($productId);
        $this->qrType = 'product';
        $this->selectedProductId = $productId;
        $this->generatedQRUrl = QRCodeService::generateProductQR($productId);
        $this->qrFileName = "menu-producto-{$product->name}";
        $this->showPreview = true;
    }

    public function downloadQR(): void
    {
        if (!$this->generatedQRUrl) {
            return;
        }

        $filepath = storage_path('app/public/' . str_replace('/storage/', '', $this->generatedQRUrl));
        $filename = $this->qrFileName . '_' . now()->format('Y-m-d_H-i-s') . '.png';
        
        session()->flash('success', "QR listo para descargar: {$filename}");
    }

    public function closePreview(): void
    {
        $this->showPreview = false;
    }

    public function render(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_available', true)->orderBy('name')->limit(20)->get();
        $tables = Table::orderBy('number')->get();

        return view('livewire.admin.qr-code-manager', [
            'categories' => $categories,
            'products' => $products,
            'tables' => $tables,
        ]);
    }
}
