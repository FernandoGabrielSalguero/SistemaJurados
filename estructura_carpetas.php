<?php
function escanearCarpetas($ruta, $nivel = 0, $profundidadMaxima = 5) {
    $excluir = ['.git', '.DS_Store', '.env', 'node_modules', 'vendor', '.idea', '__MACOSX'];

    if ($nivel > $profundidadMaxima) return;

    $archivos = scandir($ruta);
    foreach ($archivos as $archivo) {
        if ($archivo === '.' || $archivo === '..' || in_array($archivo, $excluir)) continue;

        $rutaCompleta = $ruta . DIRECTORY_SEPARATOR . $archivo;
        echo str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;", $nivel);

        if (is_dir($rutaCompleta)) {
            echo "📁 <strong>$archivo/</strong><br>";
            escanearCarpetas($rutaCompleta, $nivel + 1, $profundidadMaxima);
        } else {
            echo "📄 $archivo<br>";
        }
    }
}

$rutaBase = __DIR__;
echo "<h1>Estructura del Proyecto</h1>";
escanearCarpetas($rutaBase);
