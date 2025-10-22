document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // ---------- Utils de selección ----------
  const $ = (id) => document.getElementById(id);
  const byName = (name) => document.getElementsByName(name)[0] || null;

  // ---------- Helpers de UI ----------
  function ensureErrorSlot(input) {
    let slot = input.nextElementSibling;
    if (!slot || !slot.classList || !slot.classList.contains('error-msg')) {
      slot = document.createElement('span');
      slot.className = 'error-msg';
      slot.style.display = 'block';
      slot.style.color = '#c0392b';
      slot.style.fontFamily = 'inherit';
      slot.style.fontSize = '1.8rem';
      slot.style.lineHeight = '1.2';
      slot.style.marginTop = '0rem';
      slot.style.marginBottom = '2rem';
      input.after(slot);
    }
    return slot;
  }

  function setError(input, msg) {
    const slot = ensureErrorSlot(input);
    slot.textContent = msg;
    input.classList.add('error');
    input.style.borderColor = '#c0392b';
  }

  function clearError(input) {
    const slot = ensureErrorSlot(input);
    slot.textContent = '';
    input.classList.remove('error');
    input.style.borderColor = '';
  }

  function trimSpaces(str) {
    let start = 0, end = str.length - 1;
    while (start <= end && (str[start] === ' ' || str[start] === '\t' || str[start] === '\n' || str[start] === '\r')) start++;
    while (end >= start && (str[end] === ' ' || str[end] === '\t' || str[end] === '\n' || str[end] === '\r')) end--;
    return str.slice(start, end + 1);
  }

  // ---------- Conjuntos de caracteres (sin regex) ----------
  function isUpper(ch) { const c = ch.charCodeAt(0); return c >= 65 && c <= 90; }
  function isLower(ch) { const c = ch.charCodeAt(0); return c >= 97 && c <= 122; }
  function isDigit(ch) { const c = ch.charCodeAt(0); return c >= 48 && c <= 57; }
  function isLetterEN(ch) { return isUpper(ch) || isLower(ch); }
  function isAlnumEN(ch) { return isLetterEN(ch) || isDigit(ch); }

  // Para contraseña: letras, dígitos, '-' y '_'
  function isPwdAllowed(ch) {
    return isLetterEN(ch) || isDigit(ch) || ch === '-' || ch === '_';
  }

  // Email local-part permitido
  function isLocalAllowedChar(ch) {
    if (isLetterEN(ch) || isDigit(ch)) return true;
    const specials = "!#$%&'*+-/=?^_`{|}~.";
    for (let i = 0; i < specials.length; i++) {
      if (ch === specials[i]) return true;
    }
    return false;
  }

  // Dominio: letras, dígitos y '-'
  function isDomainLabelChar(ch) {
    return isLetterEN(ch) || isDigit(ch) || ch === '-';
  }

  // ---------- Validaciones ----------
  function validateUsername(value) {
    const v = trimSpaces(value);
    if (v.length < 3 || v.length > 15) return 'El nombre de usuario debe tener entre 3 y 15 caracteres.';
    if (v.length === 0) return 'El nombre de usuario no puede estar vacío.';
    if (isDigit(v[0])) return 'El nombre de usuario no puede comenzar por un número.';
    for (let i = 0; i < v.length; i++) {
      if (!isAlnumEN(v[i])) {
        return 'El nombre de usuario solo puede contener letras inglesas y números.';
      }
    }
    return '';
  }

  function validatePassword(value) {
    const v = value; // intencionadamente sin trim
    if (v.length < 6 || v.length > 15) return 'La contraseña debe tener entre 6 y 15 caracteres.';
    let hasU = false, hasL = false, hasD = false;
    for (let i = 0; i < v.length; i++) {
      const ch = v[i];
      if (!isPwdAllowed(ch)) return 'La contraseña solo puede contener letras, números, guion y guion bajo.';
      if (isUpper(ch)) hasU = true;
      else if (isLower(ch)) hasL = true;
      else if (isDigit(ch)) hasD = true;
    }
    if (!hasU) return 'La contraseña debe tener al menos una letra mayúscula.';
    if (!hasL) return 'La contraseña debe tener al menos una letra minúscula.';
    if (!hasD) return 'La contraseña debe tener al menos un número.';
    return '';
  }

  function validatePasswordRepeat(pwd, pwd2) {
    if (pwd2 !== pwd) return 'Las contraseñas no coinciden.';
    return '';
  }

  function validateEmail(value) {
    const email = trimSpaces(value);
    if (email.length === 0) return 'La dirección de email no puede estar vacía.';
    if (email.length > 254) return 'La dirección de email no puede superar 254 caracteres.';

    // contar @
    let atCount = 0, atPos = -1;
    for (let i = 0; i < email.length; i++) {
      if (email[i] === '@') { atCount++; atPos = i; }
    }
    if (atCount !== 1) return 'El email debe tener exactamente un @ (formato parte-local@dominio).';
    const local = email.slice(0, atPos);
    const domain = email.slice(atPos + 1);

    if (local.length < 1 || local.length > 64) return 'La parte local debe tener entre 1 y 64 caracteres.';
    if (domain.length < 1 || domain.length > 255) return 'El dominio debe tener entre 1 y 255 caracteres.';

    if (local[0] === '.' || local[local.length - 1] === '.') return 'La parte local no puede empezar ni terminar con punto.';
    for (let i = 0; i < local.length; i++) {
      const ch = local[i];
      if (!isLocalAllowedChar(ch)) return 'La parte local contiene caracteres no permitidos.';
      if (ch === '.' && i + 1 < local.length && local[i + 1] === '.') return 'La parte local no puede contener dos puntos seguidos.';
    }

    const labels = domain.split('.');
    if (labels.length === 0) return 'El dominio debe tener al menos un subdominio.';
    for (let li = 0; li < labels.length; li++) {
      const label = labels[li];
      if (label.length < 1 || label.length > 63) return 'Cada subdominio debe tener entre 1 y 63 caracteres.';
      if (label[0] === '-' || label[label.length - 1] === '-') return 'Los subdominios no pueden empezar ni terminar con guion.';
      for (let j = 0; j < label.length; j++) {
        if (!isDomainLabelChar(label[j])) return 'El dominio solo puede contener letras, números y guiones.';
      }
    }
    return '';
  }

  function parseDateDDMMYYYY(text) {
    const s = trimSpaces(text);
    if (s.length === 0) return { ok: false, msg: 'La fecha no puede estar vacía.' };

    let parts = [];
    let buffer = '';
    for (let i = 0; i < s.length; i++) {
      const ch = s[i];
      const isSep = ch === '/' || ch === '-' || ch === '.' || ch === ' ';
      if (isSep) {
        if (buffer.length > 0) { parts.push(buffer); buffer = ''; }
      } else {
        if (!isDigit(ch)) return { ok: false, msg: 'La fecha solo puede contener números y separadores / - . espacio.' };
        buffer += ch;
      }
    }
    if (buffer.length > 0) parts.push(buffer);

    if (parts.length === 1 && parts[0].length === 8) {
      parts = [parts[0].slice(0, 2), parts[0].slice(2, 4), parts[0].slice(4)];
    }

    if (parts.length !== 3) return { ok: false, msg: 'Formato de fecha esperado: dd/mm/aaaa.' };

    const dd = parseInt(parts[0], 10);
    const mm = parseInt(parts[1], 10);
    const yyyy = parseInt(parts[2], 10);

    if (!(yyyy >= 1900 && yyyy <= 9999)) return { ok: false, msg: 'Año no válido.' };
    if (!(mm >= 1 && mm <= 12)) return { ok: false, msg: 'Mes no válido.' };
    if (!(dd >= 1 && dd <= 31)) return { ok: false, msg: 'Día no válido.' };

    const isLeap = (yyyy % 4 === 0 && yyyy % 100 !== 0) || (yyyy % 400 === 0);
    const daysInMonth = [0,31,(isLeap?29:28),31,30,31,30,31,31,30,31,30,31];
    if (dd > daysInMonth[mm]) return { ok: false, msg: 'La fecha no existe.' };

    const d = new Date(yyyy, mm - 1, dd);
    if (d.getFullYear() !== yyyy || d.getMonth() !== mm - 1 || d.getDate() !== dd) {
      return { ok: false, msg: 'La fecha no es válida.' };
    }
    return { ok: true, date: d };
  }

  function validateAgeAtLeast18(date) {
    const today = new Date();
    const eighteen = new Date(date.getTime());
    eighteen.setFullYear(eighteen.getFullYear() + 18);
    return eighteen <= new Date(today.getFullYear(), today.getMonth(), today.getDate());
  }

  function validateSexo(value) {
    if (!value) return 'Debes seleccionar una opción.';
    return '';
  }

  // ---------- Handler de submit (misma idea que login.js) ----------
  function onSubmit(event) {
    const form = event.target;

    const user = $('reg-user');
    const pwd1 = $('reg-pwd1');
    const pwd2 = $('reg-pwd2');
    const emailInput = form.querySelector('input[name="email"]');
    const sexo = $('reg-sexo');
    const fechaInput = $('fecha_nacimiento') || byName('fecha_nacimiento') || $('reg-fecha');
    const ciudad = $('reg-ciudad');
    const pais = $('reg-pais');
    const foto = $('reg-foto');

    [user, pwd1, pwd2, emailInput, sexo, fechaInput, ciudad, pais, foto].forEach(el => {
      if (el) clearError(el);
    });

    let ok = true;

    const uErr = validateUsername(user.value);
    if (uErr) { setError(user, uErr); ok = false; }

    const pErr = validatePassword(pwd1.value);
    if (pErr) { setError(pwd1, pErr); ok = false; }

    const prErr = validatePasswordRepeat(pwd1.value, pwd2.value);
    if (prErr) { setError(pwd2, prErr); ok = false; }

    const eErr = validateEmail(emailInput.value);
    if (eErr) { setError(emailInput, eErr); ok = false; }

    const sErr = validateSexo(sexo.value);
    if (sErr) { setError(sexo, sErr); ok = false; }

    const parsed = parseDateDDMMYYYY(fechaInput.value);
    if (!parsed.ok) {
      setError(fechaInput, parsed.msg);
      ok = false;
    } else {
      const year = parsed.date.getFullYear();
      const currentYear = new Date().getFullYear();
      if (year < 1909) {
        setError(fechaInput, 'Año no válido: la persona viva actual más longeva nació en 1909, es imposible que seas tan mayor.');
        ok = false;
      } else if (year > currentYear) {
        setError(fechaInput, 'Año no válido: aún no hemos llegado a ese año.');
        ok = false;
      } else if (!validateAgeAtLeast18(parsed.date)) {
        setError(fechaInput, 'Debes tener al menos 18 años cumplidos hoy.');
        ok = false;
      }
    }

    if (!ciudad || trimSpaces(ciudad.value).length === 0) {
      if (ciudad) setError(ciudad, 'Debes indicar la ciudad de residencia.');
      ok = false;
    }
    if (!pais || trimSpaces(pais.value).length === 0) {
      if (pais) setError(pais, 'Debes indicar el país de residencia.');
      ok = false;
    }

    if (foto) {
      if (!foto.value) {
        setError(foto, 'Debes seleccionar un archivo de foto.');
        ok = false;
      }
    }

    if (!ok) {
      event.preventDefault();
      const firstError = form.querySelector('.error');
      if (firstError && typeof firstError.focus === 'function') firstError.focus();
    }
    // Si ok es true, envío normal al action="./index_logged.html"
  }

  // ---------- Inicialización (paralela al login) ----------
  const form = document.querySelector('form.auth');
  if (!form) return;

  // Estilos mínimos de accesibilidad para errores (igual que la idea de login con feedback visual)
  const style = document.createElement('style');
  style.textContent = `
    .error { outline: 2px solid #c0392b22; }
    .error:focus { outline-color: #c0392bcc; }
  `;
  document.head.appendChild(style);

  form.addEventListener('submit', onSubmit);
});
