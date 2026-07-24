@props(['class' => ''])

<svg {{ $attributes->merge(['class' => $class]) }} viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
    {{-- Cuerpo principal --}}
    <path d="
        M 145 55
        C 155 45, 170 40, 175 42
        C 180 44, 182 50, 180 55
        L 175 60
        C 178 62, 180 65, 180 68
        C 180 72, 178 75, 175 76
        L 168 74
        C 165 78, 160 80, 155 80
        L 140 78
        C 135 82, 128 84, 120 84
        L 80 88
        C 70 90, 60 95, 55 102
        L 48 112
        C 45 116, 40 118, 35 116
        C 30 114, 28 110, 30 106
        L 38 96
        C 40 92, 44 88, 48 86
        L 55 82
        C 50 78, 46 72, 44 65
        C 42 58, 44 50, 48 44
        C 52 38, 58 34, 65 32
        L 90 28
        C 95 27, 100 27, 105 28
        L 130 35
        C 138 38, 143 45, 145 55
        Z
    " fill="currentColor" opacity="0.95"/>

    {{-- Cabeza / Mandíbula superior --}}
    <path d="
        M 145 55
        C 150 48, 158 43, 168 42
        C 175 41, 182 43, 186 48
        C 190 53, 190 60, 186 65
        L 180 70
        C 178 72, 175 73, 172 72
        L 155 68
        C 150 72, 145 75, 138 76
        L 120 78
        C 130 82, 140 78, 145 72
        L 145 55
        Z
    " fill="currentColor"/>

    {{-- Mandíbula inferior --}}
    <path d="
        M 138 76
        C 142 80, 145 85, 144 90
        C 143 94, 140 96, 136 95
        L 125 90
        C 120 88, 115 86, 110 86
        L 120 84
        C 128 84, 135 80, 138 76
        Z
    " fill="currentColor" opacity="0.9"/>

    {{-- Ojo --}}
    <circle cx="165" cy="52" r="4" fill="#0a0a0a"/>
    <circle cx="166" cy="51" r="1.5" fill="currentColor" opacity="0.6"/>

    {{-- Brazos pequeños (iconicos del T-Rex) --}}
    <path d="M 95 85 L 88 95 L 92 96 L 100 87 Z" fill="currentColor" opacity="0.85"/>
    <path d="M 92 96 L 86 100 L 89 102 L 96 98 Z" fill="currentColor" opacity="0.85"/>

    {{-- Pierna trasera --}}
    <path d="
        M 70 88 L 60 120 L 55 145 L 50 155 L 45 158 L 55 160 L 65 158 L 70 150 L 75 135 L 80 115 L 85 92 Z
    " fill="currentColor" opacity="0.92"/>

    {{-- Cola --}}
    <path d="
        M 55 82 C 45 78, 35 72, 25 68 C 18 65, 12 64, 8 66 C 5 68, 5 72, 8 75 C 15 80, 28 84, 40 86 L 55 82 Z
    " fill="currentColor" opacity="0.88"/>

    {{-- Dientes superiores --}}
    <path d="M 170 62 L 168 68 L 172 66 Z" fill="currentColor"/>
    <path d="M 162 64 L 160 70 L 164 68 Z" fill="currentColor"/>
    <path d="M 154 66 L 152 72 L 156 70 Z" fill="currentColor"/>

    {{-- Lineas de textura en el cuerpo --}}
    <path d="M 100 40 C 110 38, 120 40, 125 45" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3"/>
    <path d="M 90 50 C 100 48, 115 50, 130 55" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3"/>
    <path d="M 80 65 C 95 62, 110 64, 130 68" stroke="currentColor" stroke-width="0.8" fill="none" opacity="0.3"/>
</svg>
