<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enlace expirado</title>
    <!-- Bootstrap CSS (ajusta versión si hace falta) -->
    <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css"
            rel="stylesheet">
    <style>
        /* Asegura que el body ocupe todo el alto de la ventana */
        html, body {
            height: 100%;
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center">
<div class="card shadow-sm" style="max-width: 400px; width: 100%;">
    <div class="card-body text-center">
        <h4 class="card-title mb-3">Enlace expirado</h4>
        <p class="card-text">{{ $mensaje }}</p>
    </div>
</div>

<!-- Bootstrap JS (opcional si no necesitas componentes JS) -->
<script
        src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"
></script>
<script
        src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Piv4xVNRyMGpqkS2by6br4gNJ7DXjqk09RmUpP8jgFvJo6jIW3"
        crossorigin="anonymous"
></script>
</body>
</html>
