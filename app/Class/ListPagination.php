<?php

namespace App\Class;

class ListPagination
{
    public  $rows = [];   
    public  $totalRecords;
    public  $registrosPorPagina;
    public  $paginaActual;
    public  $totalPaginas;

    public function __construct($rows, $totalRecords, $registrosPorPagina, $paginaActual)
    {
        $this->rows = $rows;
        $this->totalRecords = $totalRecords;
        $this->registrosPorPagina = $registrosPorPagina;
        $this->paginaActual = $paginaActual + 1;
        $this->totalPaginas = ceil($totalRecords / $registrosPorPagina);
    }
}
