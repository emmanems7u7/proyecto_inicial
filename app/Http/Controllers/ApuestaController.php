<?php

namespace App\Http\Controllers;

use App\Models\Apuesta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class ApuestaController extends Controller
{
    protected $cuentaValida;
    protected $nombreValido;
    protected $bancoValido;
    public function __construct()
    {
        $this->cuentaValida = '20151900026350';
        $this->nombreValido = 'Diego Emanuel Chavez Ramos';
        $this->bancoValido = 'Banco De Crédito De Bolivia';

    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'partido_id' => 'required|exists:partidos,id',
            'matricula' => 'required|max:20',
            'nombre_completo' => 'required|max:255',
            'monto' => 'required|numeric|min:1',
            'prediccion_local' => 'required|integer|min:0',
            'prediccion_visitante' => 'required|integer|min:0',
            'comprobante' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $existe = Apuesta::where('partido_id', $request->partido_id)
            ->where('matricula', $request->matricula)
            ->exists();

        if ($existe) {
            return response()->json([
                'status' => false,
                'errors' => [
                    'matricula' => [
                        'Ya existe una apuesta para esta matrícula en este partido.'
                    ]
                ]
            ], 422);
        }

        $archivo = $request->file('comprobante');

        $nombre = time() . '_' . Str::random(10) . '.' . $archivo->getClientOriginalExtension();

        $archivo->move(public_path('comprobantes'), $nombre);

        $comprobante = 'comprobantes/' . $nombre;

        do {
            $codigoApuesta = 'AP-' . strtoupper(Str::random(6));
        } while (
            Apuesta::where('codigo_apuesta', $codigoApuesta)->exists()
        );



        $result = $this->ValidarComprobante($comprobante);

        $texto = strtolower($result['text']);

        // validar si es Yape

        $resultado = null;

        if (str_contains($texto, 'yape')) {
            $resultado = $this->procesarYape($result['text'], $request);
        }

        if (
            str_contains($texto, 'banco de crédito') ||
            str_contains($texto, 'bcp') ||
            str_contains($texto, 'banca móvil')
        ) {
            $resultado = $this->procesarBancaMovilBCP($result['text'], $request);
        }

        $estadoPago = 'pendiente';

        if (isset($resultado) && $resultado['ok']) {
            $estadoPago = 'aprobado';
        } else {

        }

        $apuesta = Apuesta::create([
            'partido_id' => $request->partido_id,
            'matricula' => strtoupper($request->matricula),
            'nombre_completo' => $request->nombre_completo,
            'monto' => $request->monto,
            'prediccion_local' => $request->prediccion_local,
            'prediccion_visitante' => $request->prediccion_visitante,
            'codigo_apuesta' => $codigoApuesta,
            'comprobante' => $comprobante,
            'estado_pago' => $estadoPago ?? 'pendiente',
            'mensaje_validador' => $resultado['mensaje'] ?? 'No se pudo validar el comprobante',
            'estado_apuesta' => 'pendiente',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Apuesta registrada correctamente.',
            'codigo_apuesta' => $apuesta->codigo_apuesta
        ]);
    }

    public function ValidarComprobante($comprobante)
    {
        try {

            $response = Http::attach(
                'file',
                file_get_contents(public_path($comprobante)),
                'comprobante.jpg'
            )->post('https://api.ocr.space/parse/image', [
                        'apikey' => 'K85542626888957',
                        'language' => 'spa',
                        'OCREngine' => 2,

                    ]);

            // 1. Verificar conexión HTTP
            if (!$response->successful()) {
                return [
                    'ok' => false,
                    'error' => 'No se pudo conectar con OCR API',
                    'data' => null
                ];
            }

            $data = $response->json();

            // 2. Verificar si OCR devolvió resultados
            if (
                !isset($data['ParsedResults'][0]['ParsedText'])
            ) {
                return [
                    'ok' => false,
                    'error' => 'No se detectó texto en la imagen',
                    'data' => $data
                ];
            }

            return [
                'ok' => true,
                'text' => $data['ParsedResults'][0]['ParsedText']
            ];

        } catch (\Exception $e) {

            return [
                'ok' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function procesarYape($texto, $request)
    {
        $textoLimpio = preg_replace('/\s+/', ' ', $texto);

        // 1. Detectar monto
        preg_match('/bs\s*([\d.,]+)/i', $textoLimpio, $matches);

        $montoDetectado = isset($matches[1])
            ? floatval(str_replace(',', '.', $matches[1]))
            : null;

        if (!$montoDetectado) {
            return [
                'ok' => false,
                'mensaje' => 'No se pudo detectar el monto en Yape'
            ];
        }

        // 2. Validar monto
        if (floatval($request->monto) != $montoDetectado) {
            return [
                'ok' => false,
                'mensaje' => 'El monto no coincide con la transacción'
            ];
        }

        // 3. Validar cuenta

        if (strpos($textoLimpio, $this->cuentaValida) === false) {
            return [
                'ok' => false,
                'mensaje' => 'La cuenta de destino no es válida'
            ];
        }

        // 4. Validar nombre del titular

        if (strpos($textoLimpio, $this->nombreValido) === false) {
            return [
                'ok' => false,
                'mensaje' => 'El titular de la transacción no coincide'
            ];
        }

        // 5. Validar banco destino

        if (stripos($textoLimpio, $this->bancoValido) === false) {
            return [
                'ok' => false,
                'mensaje' => 'El banco destino no es válido'
            ];
        }

        return [
            'ok' => true,
            'monto' => $montoDetectado,
            'mensaje' => 'Comprobante Yape válido'
        ];
    }

    public function procesarBancaMovilBCP($texto, $request)
    {
        // normalizar texto
        $textoLimpio = preg_replace('/\s+/', ' ', strtolower($texto));

        // =========================
        // 1. VALIDAR MONTO
        // =========================
        preg_match('/monto:\s*bs\s*([\d.,]+)/i', $textoLimpio, $montoMatch);

        $montoDetectado = isset($montoMatch[1])
            ? floatval(str_replace(',', '.', $montoMatch[1]))
            : null;

        if (!$montoDetectado) {
            return [
                'ok' => false,
                'mensaje' => 'No se pudo detectar el monto'
            ];
        }

        if (floatval($request->monto) != $montoDetectado) {
            return [
                'ok' => false,
                'mensaje' => 'El monto no coincide, el monto de su comprobante es: ' . $montoDetectado
            ];
        }

        // 2. VALIDAR CUENTA (a la cuenta)
        preg_match('/a la cuenta\s*([\d\-]+)/i', $textoLimpio, $cuentaMatch);

        $cuentaDetectada = isset($cuentaMatch[1])
            ? str_replace('-', '', $cuentaMatch[1])
            : null;



        if ($cuentaDetectada !== $this->cuentaValida) {
            return [
                'ok' => false,
                'mensaje' => 'Cuenta no válida'
            ];
        }

        // 3. VALIDAR TITULAR (a nombre de)
        preg_match('/a nombre de\s*([a-z\s]+)/i', $textoLimpio, $nombreMatch);

        $nombreDetectado = isset($nombreMatch[1])
            ? trim($nombreMatch[1])
            : null;

        preg_match('/a nombre de\s*(.+?)(del banco|a la cuenta|$)/i', $textoLimpio, $nombreMatch);

        $nombreDetectado = isset($nombreMatch[1])
            ? trim($nombreMatch[1])
            : null;

        // normalizar espacios
        $nombreDetectado = preg_replace('/\s+/', ' ', strtolower($nombreDetectado));

        if (!$nombreDetectado || $nombreDetectado !== strtolower($this->nombreValido)) {
            return [
                'ok' => false,
                'mensaje' => 'Titular no válido'
            ];
        }
        return [
            'ok' => true,
            'monto' => $montoDetectado,
            'mensaje' => 'Comprobante BCP válido'
        ];
    }
}
