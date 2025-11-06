<?php
// Simple flashdata implementation using session
// Stores temporary values in $_SESSION['_flashdata'] and removes them on read

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Set a flash value for the next request
 */
function flash_set($key, $value) {
    if (!isset($_SESSION['_flashdata']) || !is_array($_SESSION['_flashdata'])) {
        $_SESSION['_flashdata'] = [];
    }
    $_SESSION['_flashdata'][$key] = $value;
}

/**
 * Get a flash value (removes it so it's available only once)
 */
function flash_get($key, $default = null) {
    if (!isset($_SESSION['_flashdata']) || !is_array($_SESSION['_flashdata'])) {
        return $default;
    }
    if (!array_key_exists($key, $_SESSION['_flashdata'])) {
        return $default;
    }
    $value = $_SESSION['_flashdata'][$key];
    unset($_SESSION['_flashdata'][$key]);
    if (empty($_SESSION['_flashdata'])) {
        unset($_SESSION['_flashdata']);
    }
    return $value;
}

/**
 * Get all flashdata and clear it
 */
function flash_pop_all() {
    if (!isset($_SESSION['_flashdata']) || !is_array($_SESSION['_flashdata'])) {
        return [];
    }
    $data = $_SESSION['_flashdata'];
    unset($_SESSION['_flashdata']);
    return $data;
}

?>
