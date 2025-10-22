// Un único DOMContentLoaded que orquesta todo (sin CSS adicional)
document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // ===== Configuración de tarifas =====
  const TARIFA_ENVIO = 10; // € fijo
  const BLOQUES_PAGINAS = [
    { max: 4,        precio: 2.0 },  // 1..4 páginas: 2 €/pág.
    { max: 10,       precio: 1.8 },  // 5..10 páginas: 1.8 €/pág.
    { max: Infinity, precio: 1.6 }   // >10 páginas: 1.6 €/pág.
  ];
  const RECARGO_COLOR_FOTO = 0.5;
  const RECARGO_RES_ALTA_FOTO = 0.2;

  const VALORES_PAGINAS = Array.from({ length: 15 }, (_, i) => i + 1);

  const COLUMNAS = [
    { id: 'bn_150_300',    label: 'B/N 150-300 dpi',    color: false, alta: false },
    { id: 'bn_450_900',    label: 'B/N 450-900 dpi',    color: false, alta: true  },
    { id: 'color_150_300', label: 'Color 150-300 dpi',  color: true,  alta: false },
    { id: 'color_450_900', label: 'Color 450-900 dpi',  color: true,  alta: true  }
  ];

  // ===== Utilidades =====
  function formatEuro(n) {
    return n.toFixed(2).replace('.', ',') + ' €';
  }

  function costePaginas(n) {
    let restante = n;
    let acumulado = 0;
    let total = 0;
    for (const bloque of BLOQUES_PAGINAS) {
      const limiteBloque = bloque.max === Infinity ? Infinity : bloque.max - acumulado;
      const enEsteBloque = Math.max(0, Math.min(restante, limiteBloque));
      total += enEsteBloque * bloque.precio;
      restante -= enEsteBloque;
      acumulado = bloque.max === Infinity ? acumulado + enEsteBloque : bloque.max;
      if (restante <= 0) break;
    }
    return total;
  }

  function costeTotal(paginas, fotos, esColor, resAlta) {
    const base = TARIFA_ENVIO;
    const paginasCoste = costePaginas(paginas);
    const colorCoste = (esColor ? RECARGO_COLOR_FOTO : 0) * fotos;
    const resCoste   = (resAlta ? RECARGO_RES_ALTA_FOTO : 0) * fotos;
    return base + paginasCoste + colorCoste + resCoste;
  }

  function crearCelda(txt, tag) {
    const el = document.createElement(tag || 'td');
    el.appendChild(document.createTextNode(txt));
    return el;
  }

  // ===== Construcción de la tabla =====
  function construirTabla() {
    const cont = document.getElementById('contenedorTablaCostes');
    if (!cont) return;

    // Limpiar si ya existe contenido
    while (cont.firstChild) cont.removeChild(cont.firstChild);

    // sección envolvente con class="tabla"
    const seccion = document.createElement('section');
    seccion.className = 'tabla';

    const tabla = document.createElement('table');

    // THEAD
    const thead = document.createElement('thead');
    const trh = document.createElement('tr');
    trh.appendChild(crearCelda('Páginas', 'th'));
    trh.appendChild(crearCelda('Fotos', 'th'));
    for (const col of COLUMNAS) trh.appendChild(crearCelda(col.label, 'th'));
    thead.appendChild(trh);
    tabla.appendChild(thead);

    // TBODY
    const tbody = document.createElement('tbody');
    for (const p of VALORES_PAGINAS) {
      const f = p * 3; // 3 fotos por página
      const tr = document.createElement('tr');
      tr.appendChild(crearCelda(String(p)));
      tr.appendChild(crearCelda(String(f)));
      for (const col of COLUMNAS) {
        const total = costeTotal(p, f, col.color, col.alta);
        tr.appendChild(crearCelda(formatEuro(total)));
      }
      tbody.appendChild(tr);
    }
    tabla.appendChild(tbody);

    seccion.appendChild(tabla);
    cont.appendChild(seccion);
  }

  function toggleTabla() {
    const cont = document.getElementById('contenedorTablaCostes');
    const btn  = document.getElementById('toggleCostes');
    if (!cont || !btn) return;

    const visible = cont.style.display === 'block';
    if (!visible && !cont.firstChild) {
      construirTabla(); // construir solo la primera vez que se muestra
    }
    cont.style.display = visible ? 'none' : 'block';
    btn.setAttribute('aria-expanded', String(!visible));
    btn.textContent = visible ? 'Mostrar tabla de costes' : 'Ocultar tabla de costes';
  }

  // ===== Inicialización =====
  const btn = document.getElementById('toggleCostes');
  if (btn) {
    btn.setAttribute('aria-expanded', 'false');
    btn.textContent = 'Mostrar tabla de costes';
    btn.addEventListener('click', toggleTabla);
  }
});