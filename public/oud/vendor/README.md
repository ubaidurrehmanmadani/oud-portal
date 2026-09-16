# Locally served select dependencies

- jQuery 3.7.1: `https://code.jquery.com/jquery-3.7.1.min.js` (MIT; license alongside asset).
- Select2 4.0.13: `https://github.com/select2/select2/tree/4.0.13/dist` (MIT; license alongside assets). Includes Arabic messages.

Pinned distribution assets are served locally, without runtime CDN requests. Shared initialization is in `../select-controls.js`; theme overrides are in `../application.css`. Select2 documentation: https://select2.org/configuration/options-api/ and https://select2.org/programmatic-control/methods/.
