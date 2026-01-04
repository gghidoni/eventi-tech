import flatpickr from "flatpickr";
import { Italian } from "flatpickr/dist/l10n/it.js";

// Rendiamo flatpickr disponibile globalmente per Alpine.js
window.flatpickr = flatpickr;
flatpickr.localize(Italian);