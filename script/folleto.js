(function () {

  // Script de generación de tabla de costes

  function costeTotal(paginas, fotos, esColor, resAlta) {
		const base = TARIFA_ENVIO;
		const paginasCoste = costePaginas(paginas);
		const colorCoste = (esColor ? RECARGO_COLOR_FOTO : 0) * fotos;
		const resCoste = (resAlta ? RECARGO_RES_ALTA_FOTO : 0) * fotos;
		return base + paginasCoste + colorCoste + resCoste;
	}


	function crearCelda(txt, tag) {
		const el = document.createElement(tag || 'td');
		el.appendChild(document.createTextNode(txt));
		return el;
	}


	function construirTabla() {
		const cont = document.getElementById('contenedorTablaCostes');
		// Limpiar si ya existe contenido
		while (cont.firstChild) {
			cont.removeChild(cont.firstChild);
		}

		const tabla = document.createElement('table');
		const caption = document.createElement('caption');
		caption.appendChild(document.createTextNode('Tabla de costes calculada automáticamente'));
		tabla.appendChild(caption);

		// THEAD
		const thead = document.createElement('thead');
		const trh = document.createElement('tr');
		trh.appendChild(crearCelda('Páginas', 'th'));
		trh.appendChild(crearCelda('Fotos', 'th'));
		for (const col of COLUMNAS) {
			trh.appendChild(crearCelda(col.label, 'th'));
		}
		thead.appendChild(trh);
		tabla.appendChild(thead);

		// TBODY
		const tbody = document.createElement('tbody');
		for (const p of VALORES_PAGINAS) {
			for (const f of VALORES_FOTOS) {
				const tr = document.createElement('tr');
				tr.appendChild(crearCelda(String(p)));
				tr.appendChild(crearCelda(String(f)));
				for (const col of COLUMNAS) {
					const total = costeTotal(p, f, col.color, col.alta);
					tr.appendChild(crearCelda(formatEuro(total)));
				}
				tbody.appendChild(tr);
			}
		}
		tabla.appendChild(tbody);

		cont.appendChild(tabla);
	}


	function toggleTabla() {
		const cont = document.getElementById('contenedorTablaCostes');
		const btn = document.getElementById('toggleCostes');
		const visible = cont.style.display === 'block';
		if (!visible && !cont.firstChild) {
			construirTabla(); // construir solo la primera vez que se muestra
		}
		cont.style.display = visible ? 'none' : 'block';
		btn.setAttribute('aria-expanded', String(!visible));
		btn.textContent = visible ? 'Mostrar tabla de costes' : 'Ocultar tabla de costes';
	}


	document.addEventListener('DOMContentLoaded', function () {
		document.getElementById('toggleCostes').addEventListener('click', toggleTabla);
	});
})();