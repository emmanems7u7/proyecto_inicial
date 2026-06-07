<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class ExportExcel implements FromView
{
    protected string $view;
    protected array $data;
    protected string $fileName;

    /**
     * Constructor.
     *
     * @param string $view     Nombre de la vista Blade (ej: 'export.usuarios')
     * @param array $data      Datos a pasar a la vista
     * @param string $baseName Nombre base del archivo (ej: 'usuarios')
     */
    public function __construct(string $view, array $data, string $baseName)
    {
        $this->view = $view;
        $this->data = $data;
        $this->fileName = $this->generateFileName($baseName);
    }

    /**
     * Devuelve la vista que se usará para generar el archivo Excel.
     */
    public function view(): View
    {
        return view($this->view, $this->data);
    }

    /**
     * Devuelve el nombre generado del archivo con fecha y hora.
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * Genera el nombre final del archivo con fecha y hora.
     */
    protected function generateFileName(string $baseName): string
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i');
        return "{$baseName}_{$timestamp}.xlsx";
    }
}
