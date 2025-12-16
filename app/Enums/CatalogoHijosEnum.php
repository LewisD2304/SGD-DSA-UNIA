<?php

namespace App\Enums;

enum CatalogoHijosEnum: int
{
    // Estados de recuperación de contraseña
    case PENDIENTE              = 159;
    case USADO                  = 160;
    case EXPIRADO               = 161;
    case REVOCADO               = 162;

    // Valores tipo documento
    case CARNET_EXTRANJERIA     = 13;
    case CARNET_PERMISO_TEMPORAL = 14;
    case DOCUMENTO_IDENTIFICACION_PERSONAL = 15;
    case DNI                    = 16;
    case PASAPORTE              = 17;
    case PARTIDA_NACIMIENTO     = 18;
    case RUC                    = 343;


}
