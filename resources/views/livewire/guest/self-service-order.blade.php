{{-- Self-Service QR Ordering: Mesa {number} --}}
<div class="min-h-screen bg-patrona-950 text-white antialiased pb-32">

    {{-- Header de mesa --}}
    <div class="sticky top-0 z-40 bg-patrona-900/95 backdrop-blur-md border-b border-white/5">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-copper-500/20 border border-copper-500/40 flex items-center justify-center text-copper-400 font-black text-sm">
                    {{ $tableNumber }}
                </div>
                <div>
                    <p class="text-xs text-white/40 uppercase tracking-wider">Mesa</p>
                    <p class="font-display text-sm font-bold text-white">{{ $tableName }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="toggleCart"
                        class="relative flex items-center gap-2 rounded-full px-4 py-2 text-sm font-bold transition-all
                               {{ $this->cartCount > 0 ? 'bg-copper-500 text-patrona-950 shadow-lg shadow-copper-500/25' : 'bg-white/10 text-white/70 hover:bg-white/15' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    @if($this->cartCount > 0)
                        <span class="bg-patrona-950 text-copper-400 rounded-full w-5 h-5 flex items-center justify-center text-xs font-black">
                            {{ $this->cartCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </div>

    {{-- Estado: Pedido enviado --}}
    @if($orderPlaced)
        <div class="max-w-3xl mx-auto px-4 py-16 text-center">
            <div class="w-20 h-20 rounded-full bg-olive-500/20 border border-olive-400/30 flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-olive-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="font-display text-3xl font-bold text-white mb-3">Pedido Enviado!</h2>
            <p class="text-white/60 text-lg mb-2">Tu orden esta siendo preparada</p>
            <p class="text-white/40 text-sm">Mesa {{ $tableNumber }} — Un mesero se encargara de entregarte tu pedido</p>
        </div>
    @else
        {{-- Busqueda --}}
        <div class="max-w-3xl mx-auto px-4 pt-4">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-white/30" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar platillo o bebida..."
                       class="w-full bg-white/5 border border-white/10 rounded-xl pl-11 pr-4 py-3 text-sm text-white placeholder-white/30 focus:outline-none focus:border-copper-500/50 focus:bg-white/8 transition">
            </div>
        </div>

        {{-- Categorias --}}
        <div class="max-w-3xl mx-auto px-4 pt-4">
            <div class="flex gap-2 overflow-x-auto pb-2" style="-ms-overflow-style:none; scrollbar-width:none;">
                <style>.flex::-webkit-scrollbar { display: none; }</style>
                <button wire:click="$set('activeCategoryId', null)"
                        class="shrink-0 rounded-full px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all
                               {{ is_null($activeCategoryId) ? 'bg-copper-500 text-patrona-950' : 'bg-white/5 text-white/50 hover:bg-white/10' }}">
                    Todo
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="$set('activeCategoryId', {{ $category->id }})"
                            class="shrink-0 rounded-full px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all
                                   {{ $activeCategoryId === $category->id ? 'bg-copper-500 text-patrona-950' : 'bg-white/5 text-white/50 hover:bg-white/10' }}">
                        {{ $category->name }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Productos --}}
        <div class="max-w-3xl mx-auto px-4 pt-4 pb-6">
            <div class="space-y-3">
                @forelse($this->products as $product)
                    <div class="rounded-2xl border border-white/5 bg-patrona-800/60 p-4 hover:border-copper-500/20 transition-all">
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-16 h-16 rounded-xl overflow-hidden bg-patrona-700 flex items-center justify-center">
                                @if($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-2xl">&#127860;</span>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-bold text-white text-sm leading-tight">{{ $product->name }}</h3>
                                    <span class="text-copper-400 font-black text-sm whitespace-nowrap">${{ number_format($product->price, 2) }}</span>
                                </div>
                                @if($product->description)
                                    <p class="text-white/40 text-xs mt-1 line-clamp-2">{{ $product->description }}</p>
                                @endif
                                @if(isset($this->cart[$product->id]['notes']) && $this->cart[$product->id]['notes'])
                                    <p class="text-copper-300/60 text-xs mt-1 italic">{{ $this->cart[$product->id]['notes'] }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                            <div class="flex-1 mr-3">
                                @if(isset($this->cart[$product->id]))
                                    <input type="text"
                                           wire:change="updateNote({{ $product->id }}, $event.target.value)"
                                           value="{{ $this->cart[$product->id]['notes'] ?? '' }}"
                                           placeholder="Nota: sin cebolla, extra salsa..."
                                           class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-1.5 text-xs text-white/80 placeholder-white/20 focus:outline-none focus:border-copper-500/40">
                                @endif
                            </div>

                            @if(isset($this->cart[$product->id]))
                                <div class="flex items-center gap-2">
                                    <button wire:click="decrementItem({{ $product->id }})"
                                            class="w-8 h-8 rounded-full bg-white/10 hover:bg-wine-500/40 flex items-center justify-center text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-6 text-center font-black text-sm text-copper-400">
                                        {{ $this->cart[$product->id]['quantity'] }}
                                    </span>
                                    <button wire:click="incrementItem({{ $product->id }})"
                                            class="w-8 h-8 rounded-full bg-white/10 hover:bg-olive-500/40 flex items-center justify-center text-white transition">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <button wire:click="addToCart({{ $product->id }})"
                                        class="flex items-center gap-1.5 rounded-full bg-copper-500 hover:bg-copper-400 text-patrona-950 px-4 py-2 text-xs font-bold transition-all shadow-lg shadow-copper-500/10">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Agregar
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <p class="text-white/30 text-lg">No se encontraron productos</p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- Modal Carrito --}}
    @if($showCart)
        <div class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" wire:click="toggleCart"></div>

        <div class="fixed inset-x-0 bottom-0 z-50 bg-patrona-900 border-t border-white/10 rounded-t-3xl max-h-[80vh] overflow-hidden flex flex-col">
            <div class="px-6 py-4 border-b border-white/5 flex items-center justify-between shrink-0">
                <h2 class="font-display text-xl font-bold text-white">Tu Pedido</h2>
                <button wire:click="toggleCart" class="text-white/40 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-6 py-4">
                @if(empty($this->cartItems))
                    <div class="text-center py-12">
                        <p class="text-white/30">Tu carrito esta vacio</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($this->cartItems as $item)
                            <div class="flex items-center gap-3 bg-patrona-800/60 rounded-xl p-3 border border-white/5">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-white truncate">{{ $item['name'] }}</p>
                                    <p class="text-xs text-white/40">
                                        ${{ number_format($item['price'], 2) }} x {{ $item['quantity'] }}
                                        = <span class="text-copper-400 font-bold">${{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                    </p>
                                    @if($item['notes'])
                                        <p class="text-xs text-copper-300/50 mt-1 italic">{{ $item['notes'] }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button wire:click="decrementItem({{ $item['product_id'] }})"
                                            class="w-7 h-7 rounded-full bg-white/10 hover:bg-wine-500/40 flex items-center justify-center text-white/70">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                    <span class="w-5 text-center text-xs font-black text-white">{{ $item['quantity'] }}</span>
                                    <button wire:click="incrementItem({{ $item['product_id'] }})"
                                            class="w-7 h-7 rounded-full bg-white/10 hover:bg-olive-500/40 flex items-center justify-center text-white/70">
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                            <path stroke-linecap="round" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(!empty($this->cartItems))
                <div class="shrink-0 px-6 py-4 border-t border-white/5 bg-patrona-900">
                    @if($orderError)
                        <div class="mb-3 rounded-lg bg-wine-500/20 border border-wine-400/30 px-4 py-2 text-xs text-wine-400">
                            {{ $orderError }}
                        </div>
                    @endif

                    <div class="flex items-center justify-between mb-4">
                        <span class="text-white/60 text-sm">Mesa {{ $tableNumber }}</span>
                        <div class="text-right">
                            <p class="text-xs text-white/40">Total</p>
                            <p class="font-display text-2xl font-black text-copper-400">${{ number_format($this->cartTotal, 2) }}</p>
                        </div>
                    </div>
                    <button wire:click="submitOrder"
                            wire:loading.attr="disabled"
                            class="w-full rounded-xl bg-copper-500 hover:bg-copper-400 text-patrona-950 py-3.5 text-sm font-black uppercase tracking-wider transition-all shadow-lg shadow-copper-500/20 disabled:opacity-50">
                        <span wire:loading.remove wire:target="submitOrder">Enviar Pedido</span>
                        <span wire:loading wire:target="submitOrder" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                            Enviando...
                        </span>
                    </button>
                </div>
            @endif
        </div>
    @endif

    {{-- FAB carrito --}}
    @if(!empty($this->cartItems) && !$showCart && !$orderPlaced)
        <div class="fixed bottom-6 right-6 z-30">
            <button wire:click="toggleCart"
                    class="flex items-center gap-3 rounded-full bg-copper-500 hover:bg-copper-400 text-patrona-950 px-5 py-3.5 font-black text-sm shadow-2xl shadow-copper-500/30 transition-all hover:scale-105">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>{{ $this->cartCount }} items</span>
                <span class="border-l border-patrona-950/30 pl-3">${{ number_format($this->cartTotal, 2) }}</span>
            </button>
        </div>
    @endif
</div>
