<?php
/**
 * services/ultimos_anuncios.php
 * Cookie: 'ultimos_anuncios' (JSON con array de elementos)
 * Cada elemento: ['id', 'foto', 'nombre', 'ciudad', 'pais', 'precio']
 * Persistencia: 7 días, HttpOnly, SameSite=Lax
 */

const UA_COOKIE_NAME = 'ultimos_anuncios';
const UA_MAX_ITEMS   = 4;
const UA_MAX_AGE     = 7 * 24 * 60 * 60; // 1 semana

function ua_obtener(): array {
    if (!isset($_COOKIE[UA_COOKIE_NAME])) return [];
    $raw = $_COOKIE[UA_COOKIE_NAME];
    $arr = json_decode($raw, true);
    return is_array($arr) ? $arr : [];
}

function ua_guardar(array $items): void {
    // Guardar manteniendo el orden recibido
    $opts = [
        'expires'  => time() + UA_MAX_AGE,
        'path'     => '/',
        'secure'   => false,
        'httponly' => true,    // no accesible desde JS (lo pide la práctica)
        'samesite' => 'Lax',
    ];
    setcookie(UA_COOKIE_NAME, json_encode($items, JSON_UNESCAPED_UNICODE), $opts);
}

function ua_actualizar(int $id, array $anuncio): void {
    // Construir el payload que queremos guardar
    $nuevo = [
        'id'     => $id,
        'foto'   => $anuncio['foto']     ?? '',
        'nombre' => $anuncio['nombre']   ?? '',
        'ciudad' => $anuncio['ciudad']   ?? '',
        'pais'   => $anuncio['pais']     ?? '',
        'precio' => $anuncio['precio']   ?? 0,
    ];

    // Cargar cookie actual
    $items = ua_obtener();

    // Si ya existe, eliminarlo para reinsertarlo y respetar el orden de visita
    $items = array_values(array_filter($items, fn($it) => (int)$it['id'] !== $id));

    // Añadir al final (el enunciado pide "en el orden en el que se han visitado")
    $items[] = $nuevo;

    // Mantener solo los 4 últimos: si hay más, eliminar el primero (el más antiguo)
    if (count($items) > UA_MAX_ITEMS) {
        $items = array_slice($items, -UA_MAX_ITEMS);
    }

    ua_guardar($items);
}