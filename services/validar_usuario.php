<?php
/**
 * services/validar_usuario.php
 * Lógica de validación estricta según el enunciado de la Práctica.
 * @param array $data Datos del formulario ($_POST).
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
    if ($isRegistration || $userModified) {
        if ($user === '') {
            $errors['err_user'] = "El nombre de usuario no puede estar vacío.";
        } else {
            if (strlen($user) < 3 || strlen($user) > 15) {
                $errors['err_user'] = "El nombre debe tener entre 3 y 15 caracteres.";
            }
            elseif (!preg_match('/^[a-zA-Z0-9]+$/', $user)) {
                $errors['err_user'] = "Solo se permiten letras del alfabeto inglés y números.";
            }
            elseif (preg_match('/^[0-9]/', $user)) {
                $errors['err_user'] = "El nombre de usuario no puede comenzar con un número.";
            }
        }
    }

    // =======================================================================
    // 2. CONTRASEÑA
    // =======================================================================
    if ($isRegistration || $passwordIsBeingModified) {
        if ($pwd1 === '') {
            if ($isRegistration) $errors['err_pwd1'] = "La contraseña no puede estar vacía.";
        } else {
            if (strlen($pwd1) < 6 || strlen($pwd1) > 15) {
                $errors['err_pwd1'] = "La contraseña debe tener entre 6 y 15 caracteres.";
            }
            elseif (!preg_match('/^[a-zA-Z0-9_-]+$/', $pwd1)) {
                $errors['err_pwd1'] = "Solo se permiten letras, números, guiones y guiones bajos.";
            }
            elseif (!preg_match('/[a-z]/', $pwd1) || !preg_match('/[A-Z]/', $pwd1) || !preg_match('/[0-9]/', $pwd1)) {
                $errors['err_pwd1'] = "Debe contener al menos una mayúscula, una minúscula y un número.";
            }
        }

        if ($pwd1 !== $pwd2) {
            $key = $isRegistration ? 'err_pwd2' : 'err_pwd_match';
            $errors[$key] = "Las contraseñas no coinciden.";
        }
    }

    // =======================================================================
    // 3. EMAIL
    // =======================================================================
    if ($email === '') {
        $errors['err_email'] = "La dirección de email no puede estar vacía.";
    } else {
        if (strlen($email) > 254) {
            $errors['err_email'] = "El email no puede superar los 254 caracteres.";
        } else {
            $posArroba = strrpos($email, '@');
            if ($posArroba === false) {
                $errors['err_email'] = "Formato inválido: falta el símbolo '@'.";
            } else {
                $local = substr($email, 0, $posArroba);
                $dominio = substr($email, $posArroba + 1);

                if (strlen($local) < 1 || strlen($local) > 64) {
                    $errors['err_email'] = "La parte local debe tener entre 1 y 64 caracteres.";
                } elseif ($local[0] === '.' || substr($local, -1) === '.') {
                    $errors['err_email'] = "La parte local no puede empezar ni terminar con un punto.";
                } elseif (strpos($local, '..') !== false) {
                    $errors['err_email'] = "La parte local no puede contener dos puntos seguidos.";
                } elseif (!preg_match('/^[a-zA-Z0-9!#$%&\'*+\-\/=?^_`{|}~.]+$/', $local)) {
                    $errors['err_email'] = "La parte local contiene caracteres no permitidos.";
                }

                if (!isset($errors['err_email'])) {
                    if (strlen($dominio) < 1 || strlen($dominio) > 255) {
                        $errors['err_email'] = "La longitud del dominio es incorrecta.";
                    } else {
                        $subdominios = explode('.', $dominio);
                        if (count($subdominios) < 1) { 
                             // Validar mínimo
                        }
                        
                        foreach ($subdominios as $sub) {
                            if ($sub === '') {
                                $errors['err_email'] = "El dominio contiene puntos consecutivos o vacíos.";
                                break;
                            }
                            if (strlen($sub) > 63) {
                                $errors['err_email'] = "Cada subdominio debe tener máximo 63 caracteres.";
                                break;
                            }
                            if (!preg_match('/^[a-zA-Z0-9-]+$/', $sub)) {
                                $errors['err_email'] = "El dominio contiene caracteres inválidos.";
                                break;
                            }
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
    if ($isRegistration || ($sexo !== ($data['original_sexo'] ?? $sexo))) {
        if ($sexo === '' || ($sexo != '0' && $sexo != '1' && $sexo != '2')) {
            $errors['err_sexo'] = "Debes elegir un valor para el sexo.";
        }
    }

    // =======================================================================
    // 5. FECHA DE NACIMIENTO (CORREGIDO WARNING)
    // =======================================================================
    if ($isRegistration || ($fecha_nacimiento !== ($data['original_fecha'] ?? $fecha_nacimiento))) {
        if ($fecha_nacimiento === '') {
            if ($isRegistration) $errors['err_fecha'] = "La fecha de nacimiento es obligatoria.";
        } else {
            $fechaObj = null;
            // Intentar primero Y-m-d
            $tmp = DateTime::createFromFormat('Y-m-d', $fecha_nacimiento);
            if ($tmp !== false) {
                $errors_before = DateTime::getLastErrors();
                // CORRECCIÓN: Comprobar is_array antes de acceder a índices
                if (is_array($errors_before) && $errors_before['warning_count'] == 0 && $errors_before['error_count'] == 0) {
                    $fechaObj = $tmp;
                } elseif ($errors_before === false) {
                    // Si devuelve false significa que no hay errores
                    $fechaObj = $tmp;
                }
            }
            
            // Si no funcionó Y-m-d, intentar dd/mm/Y
            if (!$fechaObj) {
                $tmp = DateTime::createFromFormat('d/m/Y', $fecha_nacimiento);
                if ($tmp !== false) {
                    $errors_before = DateTime::getLastErrors();
                    // CORRECCIÓN
                    if (is_array($errors_before) && $errors_before['warning_count'] == 0 && $errors_before['error_count'] == 0) {
                        $fechaObj = $tmp;
                    } elseif ($errors_before === false) {
                        $fechaObj = $tmp;
                    }
                }
            }

            if (!$fechaObj) {
                $errors['err_fecha'] = "La fecha introducida no es válida. Usa dd/mm/aaaa.";
            } else {
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