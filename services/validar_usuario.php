<?php
/**
 * services/validar_usuario.php
 * Lógica de validación estricta según el enunciado de la Práctica.
 * * @param array $data Datos del formulario ($_POST).
 * @param bool $isRegistration True si es registro (campos obligatorios), False si es edición.
 * @return array Array asociativo con los mensajes de error.
 */
function validar_datos_usuario($data, $isRegistration = true) {
    $errors = [];

    // --- Recoger y limpiar datos ---
    $user = trim($data['user'] ?? '');
    // En registro usamos 'pwd'/'pwd2', en edición 'new_pwd'/'new_pwd2'
    $pwd1 = $isRegistration ? ($data['pwd'] ?? '') : ($data['new_pwd'] ?? '');
    $pwd2 = $isRegistration ? ($data['pwd2'] ?? '') : ($data['new_pwd2'] ?? '');
    $email = trim($data['email'] ?? '');
    $sexo = $data['sexo'] ?? '';
    $fecha_nacimiento = $data['fecha_nacimiento'] ?? '';

    // Detectar si hay cambios en edición
    $userModified = !$isRegistration && ($user !== ($data['original_user'] ?? $user));
    $passwordIsBeingModified = !empty($pwd1); // Si escribe algo en la contraseña

    // =======================================================================
    // 1. NOMBRE DE USUARIO
    // =======================================================================
    // Reglas: Alfabeto inglés (a-z, A-Z) y números. NO comenzar con número. Longitud 3-15.
    if ($isRegistration || $userModified) {
        if ($user === '') {
            $errors['err_user'] = "El nombre de usuario no puede estar vacío.";
        } else {
            if (strlen($user) < 3 || strlen($user) > 15) {
                $errors['err_user'] = "El nombre debe tener entre 3 y 15 caracteres.";
            }
            // Solo letras inglesas y números
            elseif (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
                $errors['err_user'] = "Solo se permiten letras del alfabeto inglés y números.";
            }
            // No puede comenzar con número
            elseif (preg_match('/^[0-9]/', $user)) {
                $errors['err_user'] = "El nombre de usuario no puede comenzar con un número.";
            }
        }
    }

    // =======================================================================
    // 2. CONTRASEÑA
    // =======================================================================
    // Reglas: a-z, A-Z, 0-9, guion (-), guion bajo (_).
    // Mínimo: 1 mayúscula, 1 minúscula, 1 número.
    // Longitud: 6-15.
    if ($isRegistration || $passwordIsBeingModified) {
        if ($pwd1 === '') {
            if ($isRegistration) $errors['err_pwd1'] = "La contraseña no puede estar vacía.";
        } else {
            if (strlen($pwd1) < 6 || strlen($pwd1) > 15) {
                $errors['err_pwd1'] = "La contraseña debe tener entre 6 y 15 caracteres.";
            }
            // Caracteres permitidos
            elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $pwd1)) {
                $errors['err_pwd1'] = "Solo se permiten letras, números, guiones y guiones bajos.";
            }
            // Requisitos de complejidad
            elseif (!preg_match('/[a-z]/', $pwd1) || !preg_match('/[A-Z]/', $pwd1) || !preg_match('/[0-9]/', $pwd1)) {
                $errors['err_pwd1'] = "Debe contener al menos una mayúscula, una minúscula y un número.";
            }
        }

        // Repetir contraseña
        if ($pwd1 !== $pwd2) {
            // Usamos claves distintas según el formulario para que aparezca en el campo correcto
            $key = $isRegistration ? 'err_pwd2' : 'err_pwd_match';
            $errors[$key] = "Las contraseñas no coinciden.";
        }
    }

    // =======================================================================
    // 3. EMAIL (Validación Manual Estricta)
    // =======================================================================
    // Formato: parte-local@dominio
    if ($email === '') {
        $errors['err_email'] = "La dirección de email no puede estar vacía.";
    } else {
        // Longitud total máxima 254
        if (strlen($email) > 254) {
            $errors['err_email'] = "El email no puede superar los 254 caracteres.";
        } else {
            // Separar parte local y dominio por el último '@'
            $posArroba = strrpos($email, '@');
            if ($posArroba === false) {
                $errors['err_email'] = "Formato inválido: falta el símbolo '@'.";
            } else {
                $local = substr($email, 0, $posArroba);
                $dominio = substr($email, $posArroba + 1);

                // VALIDAR PARTE LOCAL
                // - Longitud 1 a 64
                // - Caracteres: a-z A-Z 0-9 ! # $ % & ' * + - / = ? ^ _ ` { | } ~ .
                // - Punto no al principio ni al final
                // - No dos puntos seguidos
                if (strlen($local) < 1 || strlen($local) > 64) {
                    $errors['err_email'] = "La parte local debe tener entre 1 y 64 caracteres.";
                } elseif ($local[0] === '.' || substr($local, -1) === '.') {
                    $errors['err_email'] = "La parte local no puede empezar ni terminar con un punto.";
                } elseif (strpos($local, '..') !== false) {
                    $errors['err_email'] = "La parte local no puede contener dos puntos seguidos.";
                } elseif (!preg_match('/^[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]+$/', $local)) {
                    $errors['err_email'] = "La parte local contiene caracteres no permitidos.";
                }

                // VALIDAR DOMINIO (si la parte local es correcta)
                if (!isset($errors['err_email'])) {
                    // Longitud máxima 255 (aunque el total limita a 254)
                    if (strlen($dominio) < 1 || strlen($dominio) > 255) {
                        $errors['err_email'] = "La longitud del dominio es incorrecta.";
                    } else {
                        // Secuencia de subdominios separados por punto
                        $subdominios = explode('.', $dominio);
                        if (count($subdominios) < 1) { // Al menos un dominio (ej: localhost) o dom.com
                             // El enunciado dice "secuencia de uno o más subdominio separados por un punto"
                             // Interpretación: "google.com" -> subdominios "google" y "com".
                        }
                        
                        foreach ($subdominios as $sub) {
                            if ($sub === '') {
                                $errors['err_email'] = "El dominio contiene puntos consecutivos o vacíos.";
                                break;
                            }
                            // Longitud subdominio max 63
                            if (strlen($sub) > 63) {
                                $errors['err_email'] = "Cada subdominio debe tener máximo 63 caracteres.";
                                break;
                            }
                            // Caracteres: a-z A-Z 0-9 -
                            if (!preg_match('/^[a-zA-Z0-9-]+$/', $sub)) {
                                $errors['err_email'] = "El dominio contiene caracteres inválidos (solo letras, números y guiones).";
                                break;
                            }
                            // Guion no al principio ni al final del subdominio
                            if ($sub[0] === '-' || substr($sub, -1) === '-') {
                                $errors['err_email'] = "Los subdominios no pueden empezar ni terminar con guion.";
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    // =======================================================================
    // 4. SEXO
    // =======================================================================
    // Se debe elegir un valor.
    if ($isRegistration || ($sexo !== ($data['original_sexo'] ?? $sexo))) {
        if ($sexo === '' || ($sexo != '0' && $sexo != '1' && $sexo != '2')) {
            $errors['err_sexo'] = "Debes elegir un valor para el sexo.";
        }
    }

    // =======================================================================
    // 5. FECHA DE NACIMIENTO
    // =======================================================================
    // Fecha válida (acepta dd/mm/aaaa o yyyy-mm-dd) y 18 años cumplidos HOY.
    if ($isRegistration || ($fecha_nacimiento !== ($data['original_fecha'] ?? $fecha_nacimiento))) {
        if ($fecha_nacimiento === '') {
            if ($isRegistration) $errors['err_fecha'] = "La fecha de nacimiento es obligatoria.";
        } else {
            $fechaObj = null;
            // Intentar primero Y-m-d (formato normalizado desde respuesta_registro.php)
            $tmp = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
            if ($tmp !== false) {
                // Resetear warnings de DateTime
                $errors_before = DateTime::getLastErrors();
                if ($errors_before['warning_count'] == 0 && $errors_before['error_count'] == 0) {
                    $fechaObj = $tmp;
                }
            }
            
            // Si no funcionó Y-m-d, intentar dd/mm/Y (formato usuario)
            if (!$fechaObj) {
                $tmp = DateTime::createFromFormat('d/m/Y', $fecha_nacimiento);
                if ($tmp !== false) {
                    $errors_before = DateTime::getLastErrors();
                    if ($errors_before['warning_count'] == 0 && $errors_before['error_count'] == 0) {
                        $fechaObj = $tmp;
                    }
                }
            }

            if (!$fechaObj) {
                $errors['err_fecha'] = "La fecha introducida no es válida. Usa dd/mm/aaaa.";
            } else {
                // Reglas adicionales: no futura, al menos 18 años y año >= 1909
                $hoy = new DateTime();
                $hoy->setTime(0, 0, 0);
                $fechaObj->setTime(0, 0, 0);

                if ($fechaObj > $hoy) {
                    $errors['err_fecha'] = "La fecha de nacimiento no puede ser futura.";
                } else {
                    $anio = (int)$fechaObj->format('Y');
                    if ($anio < 1909) {
                        $errors['err_fecha'] = "Año no válido: la persona viva actual más longeva nació en 1909.";
                    } else {
                        $fechaMas18 = clone $fechaObj;
                        $fechaMas18->modify('+18 years');
                        if ($fechaMas18 > $hoy) {
                            $errors['err_fecha'] = "Debes tener al menos 18 años recién cumplidos.";
                        }
                    }
                }
            }
        }
    }

    return $errors;
}
?>