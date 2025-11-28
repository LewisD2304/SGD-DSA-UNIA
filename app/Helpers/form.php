<?php

use Vinkla\Hashids\Facades\Hashids;

if (!function_exists('limpiarCadena')) {
    // Función para limpiar cadenas de texto
    function limpiarCadena($cadena, ?bool $tilde_bool = true, ?bool $dieresis_bool = true, ?bool $mayuscula_bool = true) {
        $tilde = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'];
        $dieresis = ['ä', 'ë', 'ï', 'ö', 'ü', 'Ä', 'Ë', 'Ï', 'Ö', 'Ü'];
        $reemplazo = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'];
        $cadena=trim($cadena);
        $cadena=stripslashes($cadena);
        $cadena=str_ireplace("<script>", "", $cadena);
        $cadena=str_ireplace("</script>", "", $cadena);
        $cadena=str_ireplace("<script src", "", $cadena);
        $cadena=str_ireplace("<script type=", "", $cadena);
        $cadena=str_ireplace("SELECT * FROM", "", $cadena);
        $cadena=str_ireplace("DELETE FROM", "", $cadena);
        $cadena=str_ireplace("INSERT INTO", "", $cadena);
        $cadena=str_ireplace("DROP TABLE", "", $cadena);
        $cadena=str_ireplace("DROP DATABASE", "", $cadena);
        $cadena=str_ireplace("TRUNCATE TABLE", "", $cadena);
        $cadena=str_ireplace("SHOW TABLES", "", $cadena);
        $cadena=str_ireplace("SHOW DATABASES", "", $cadena);
        $cadena=str_ireplace("<?php", "", $cadena);
        $cadena=str_ireplace("?>", "", $cadena);
        $cadena=str_ireplace("--", "", $cadena);
        $cadena=str_ireplace(">", "", $cadena);
        $cadena=str_ireplace("<", "", $cadena);
        $cadena=str_ireplace("[", "", $cadena);
        $cadena=str_ireplace("]", "", $cadena);
        $cadena=str_ireplace("^", "", $cadena);
        $cadena=str_ireplace("==", "", $cadena);
        $cadena=str_ireplace("::", "", $cadena);
        $cadena=str_ireplace("' OR 1=1--", "", $cadena);
        $cadena=stripslashes($cadena);
        $cadena = trim($cadena);

        if ($tilde_bool === true) {
            $cadena=str_replace($tilde, $reemplazo, $cadena);
        }
        if ($dieresis_bool === true) {
            $cadena = str_replace($dieresis, $reemplazo, $cadena);
        }
        if($mayuscula_bool === true){
            $cadena = mb_strtoupper($cadena, 'UTF-8');
        }

        return $cadena;
    }
}

