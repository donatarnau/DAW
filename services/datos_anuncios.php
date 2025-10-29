<?php
// anuncios_datos.php
return [
    'impar' => [
        'tipo' => 'Venta',
        'vivienda' => 'Piso',
        'foto' => 'img/a1.jpeg',
        'nombre' => 'Ático luminoso en el centro',
        'descripcion' => 'Ático reformado con terraza, 3 habitaciones, 2 baños, cocina equipada y salón amplio.',
        'fecha' => '2025-09-22',
        'ciudad' => 'Alicante',
        'pais' => 'España',
        'precio' => 250000,
        'caracteristicas' => [
            'Superficie: 120 m²',
            'Número de habitaciones: 3',
            'Número de baños: 2',
            'Planta: 5ª',
            'Año de construcción: 2015'
        ],
        'fotos' => [
            ['src' => 'img/a1.jpeg', 'caption' => 'Foto principal'],
            ['src' => 'img/a2.png', 'caption' => 'Vista salón']
        ],
        'usuario' => 'juan123'
    ],
    'par' => [
        'tipo' => 'Alquiler',
        'vivienda' => 'Apartamento',
        'foto' => 'img/a2.png',
        'nombre' => 'Apartamento con vistas al mar',
        'descripcion' => 'Moderno apartamento en primera línea de playa con piscina comunitaria y terraza privada.',
        'fecha' => '2025-10-10',
        'ciudad' => 'Málaga',
        'pais' => 'España',
        'precio' => 900,
        'caracteristicas' => [
            'Superficie: 60 m²',
            'Número de habitaciones: 1',
            'Número de baños: 1',
            'Terraza: Sí',
            'Piscina: Comunitaria'
        ],
        'fotos' => [
            ['src' => 'img/a2.png', 'caption' => 'Foto principal'],
            ['src' => 'img/a1.jpeg', 'caption' => 'Vista del mar']
        ],
        'usuario' => 'maria87'
    ]
];