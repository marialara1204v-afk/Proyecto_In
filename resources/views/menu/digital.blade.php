<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Digital - La Patrona</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f5f0eb;
            font-family: 'DM Sans', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #2a2a2a;
        }

        .menu-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .menu-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .menu-header h1 {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 3rem;
            color: #6b4f1e;
            font-weight: bold;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .menu-header p {
            font-size: 1.1rem;
            color: #8a8a8a;
        }

        .tabs-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid rgba(200,157,84,0.15);
        }

        .tabs-header {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: #faf8f5;
            border-bottom: 2px solid #e8e4df;
        }

        .tab-button {
            padding: 20px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            border: none;
            background: transparent;
            color: #8a8a8a;
            text-transform: uppercase;
            transition: all 0.3s ease;
            text-align: center;
            letter-spacing: 0.5px;
        }

        .tab-button:hover {
            background: #f5f0eb;
            color: #2a2a2a;
        }

        .tab-button.active {
            color: #6b4f1e;
            border-bottom: 4px solid #c89d54;
            background: white;
        }

        .tab-content {
            display: none;
            padding: 0;
        }

        .tab-content.active {
            display: block;
        }

        .tab-panel {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .images-column {
            background: linear-gradient(135deg, #5c4419 0%, #7a5c2e 100%);
            padding: 30px 20px;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            min-height: auto;
            overflow-y: auto;
            max-height: 1200px;
        }

        .images-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
            width: 100%;
            padding: 20px;
            max-width: 300px;
            margin: 0 auto;
        }

        .image-item {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.3);
            transition: transform 0.3s ease;
            cursor: pointer;
            height: 180px;
            width: 100%;
        }

        .image-item:hover {
            transform: scale(1.05);
            z-index: 10;
        }

        .image-item:nth-child(3n) { height: 200px; }
        .image-item:nth-child(5n) { height: 190px; }
        .image-item:nth-child(7n) { height: 210px; }

        .image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .products-column {
            padding: 40px;
            overflow-y: auto;
            max-height: 1200px;
        }

        .section-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 1.8rem;
            color: #6b4f1e;
            margin-bottom: 30px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .products-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .product-item {
            border-bottom: 1px solid #e8e4df;
            padding-bottom: 15px;
            transition: all 0.3s ease;
        }

        .product-item:last-child {
            border-bottom: none;
        }

        .product-item:hover {
            padding-left: 10px;
            background: #faf8f5;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: bold;
            color: #2a2a2a;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .product-price {
            color: #7a5c2e;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .product-description {
            font-size: 0.9rem;
            color: #8a8a8a;
            margin-bottom: 8px;
        }

        .product-variants {
            font-size: 0.85rem;
            color: #5a5a5a;
            font-style: italic;
        }

        .btn-volver {
            background: #6b4f1e;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn-volver:hover {
            background: #5c4419;
        }

        @media (max-width: 768px) {
            .tabs-header {
                grid-template-columns: 1fr 1fr;
            }

            .tab-button {
                padding: 15px;
                font-size: 1rem;
            }

            .tab-panel {
                grid-template-columns: 1fr;
            }

            .images-column {
                max-height: 600px;
                padding: 20px 10px;
            }

            .images-grid {
                max-width: 250px;
                gap: 15px;
                padding: 15px;
            }

            .image-item {
                height: 140px;
            }

            .products-column {
                padding: 20px;
                max-height: 600px;
            }

            .menu-header h1 {
                font-size: 2rem;
            }

            .section-title {
                font-size: 1.3rem;
            }

            .product-name {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="menu-container">
        <div style="margin-bottom: 20px;">
            <button onclick="window.history.back()" class="btn-volver">
                ← Volver
            </button>
        </div>

        <div class="menu-header">
            <h1>La Patrona</h1>
            <p>Menú Digital - Bienvenido</p>
        </div>

        <div class="tabs-container">
            <div class="tabs-header">
                <button class="tab-button active" onclick="switchTab(event, 'food')">
                    🍽️ Comidas
                </button>
                <button class="tab-button" onclick="switchTab(event, 'beverages')">
                    🥤 Bebidas
                </button>
            </div>

            <div id="food" class="tab-content active">
                <div class="tab-panel">
                    <div class="images-column">
                        <div class="images-grid">
                            @php
                                $foodImages = $foodProducts
                                    ->filter(fn($p) => $p->image && strpos($p->image, 'placeholder') === false)
                                    ->pluck('image')
                                    ->unique()
                                    ->values()
                                    ->shuffle();
                            @endphp
                            @foreach($foodImages as $image)
                                <div class="image-item">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Comida">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="products-column">
                        <div class="section-title">Comidas</div>
                        <div class="products-list">
                            @foreach($foodProducts as $product)
                                <div class="product-item">
                                    <div class="product-name">
                                        <span>{{ $product->name }}</span>
                                        <span class="product-price">${{ number_format($product->price, 0) }}</span>
                                    </div>
                                    @if($product->description)
                                        <div class="product-description">
                                            {{ \Illuminate\Support\Str::limit($product->description, 80) }}
                                        </div>
                                    @endif
                                    @if($product->variants && count($product->variants) > 0)
                                        <div class="product-variants">
                                            Variantes: {{ implode(', ', array_map(fn($v) => $v['name'], $product->variants)) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div id="beverages" class="tab-content">
                <div class="tab-panel">
                    <div class="images-column">
                        <div class="images-grid">
                            @php
                                $beverageImages = $beverageProducts
                                    ->filter(fn($p) => $p->image && strpos($p->image, 'placeholder') === false)
                                    ->pluck('image')
                                    ->unique()
                                    ->values()
                                    ->shuffle();
                            @endphp
                            @foreach($beverageImages as $image)
                                <div class="image-item">
                                    <img src="{{ asset('storage/' . $image) }}" alt="Bebida">
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="products-column">
                        <div class="section-title">Bebidas</div>
                        <div class="products-list">
                            @foreach($beverageProducts as $product)
                                <div class="product-item">
                                    <div class="product-name">
                                        <span>{{ $product->name }}</span>
                                        <span class="product-price">${{ number_format($product->price, 0) }}</span>
                                    </div>
                                    @if($product->description)
                                        <div class="product-description">
                                            {{ \Illuminate\Support\Str::limit($product->description, 80) }}
                                        </div>
                                    @endif
                                    @if($product->variants && count($product->variants) > 0)
                                        <div class="product-variants">
                                            Variantes: {{ implode(', ', array_map(fn($v) => $v['name'], $product->variants)) }}
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(evt, tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab-button').forEach(b => b.classList.remove('active'));
            document.getElementById(tabName).classList.add('active');
            evt.currentTarget.classList.add('active');
        }
    </script>
</body>
</html>
