<?php

function getPrivilegiosPermitidos()
{
    return array(0, 1, 2, 4, 6);
}

function getPrivilegeLabel($cod)
{
    switch (intval($cod)) {
        case 0:
            return 'Bloqueado';
        case 1:
            return 'Administrador';
        case 2:
            return 'Descargas';
        case 4:
            return 'Captador';
        case 6:
            return 'Servicios';
        default:
            return 'Sin definir';
    }
}