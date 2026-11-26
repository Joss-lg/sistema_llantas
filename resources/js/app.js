import './bootstrap';

import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import { Spanish } from 'flatpickr/dist/l10n/es.js';
import 'flatpickr/dist/flatpickr.min.css';

// Alpine: lo usan las pantallas con x-data (ventas, inventario, etc.)
window.Alpine = Alpine;
Alpine.start();

// Calendario (flatpickr) en español, para los filtros de fechas
flatpickr.l10ns.es = Spanish;
flatpickr.localize(Spanish);
window.flatpickr = flatpickr;
