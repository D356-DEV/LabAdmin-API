<?php

// Configuración
define('WIT_TOKEN', '6V4NUA63YM4WRZU6AQPVIOOTJHUTWPVF');
define('DB_ENDPOINT', 'https://api.d356.dev/bot/get_dataset');
define('WIT_API_VERSION', '20240215');
define('LOG_FILE', __DIR__ . '/bot_logs.log');

class ChatBot {
    
    public function __construct() {
    }

    // Inicializar logs
    public function logMessage($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] $message\n";
        file_put_contents(LOG_FILE, $logEntry, FILE_APPEND);
    }

    public function handleQuestion() {
        try {
            $this->logMessage("Iniciando solicitud");

            $input = json_decode(file_get_contents('php://input'), true);

            $pregunta = trim($input['message'] ?? '');
            $this->logMessage("Pregunta recibida: $pregunta");

            if (empty($pregunta)) {
                throw new Exception('Mensaje no puede estar vacío', 400);
            }

            $witData = $this->analizarPregunta($pregunta);
            $this->logMessage("Datos de Wit.ai: " . json_encode($witData));

            $laboratorios = $this->obtenerLaboratorios();
            $this->logMessage("Laboratorios cargados: " . count($laboratorios['data']) . " registros");

            $respuesta = $this->generarRespuesta($witData, $laboratorios['data']);

            $response = [
                'status' => 'success',
                'data' => [
                    'respuesta' => $respuesta,
                    'debug' => [
                        'intencion_detectada' => $witData['intents'][0]['name'] ?? 'desconocida',
                        'laboratorio_detectado' => $witData['entities']['laboratorio'][0]['value'] ?? null,
                        'supervisor_detectado' => $witData['entities']['supervisor'][0]['value'] ?? null,
                        'hora_detectada' => $witData['entities']['datetime'][0]['value'] ?? null,
                        'laboratorios_disponibles' => array_column($laboratorios['data'], 'lab_name')
                    ]
                ]
            ];

            $this->logMessage("Respuesta generada: " . json_encode($response));

            echo json_encode($response);

        } catch (Exception $e) {
            $this->logMessage("Error: " . $e->getMessage());
            $this->manejarError($e);
        }
    }

    // Funciones principales
    private function analizarPregunta($mensaje) {
        $url = "https://api.wit.ai/message?v=" . WIT_API_VERSION . "&q=" . urlencode($mensaje);
        $contexto = stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer " . WIT_TOKEN,
                'timeout' => 3
            ]
        ]);

        $respuesta = @file_get_contents($url, false, $contexto);
        if ($respuesta === false) {
            throw new Exception('Error al conectar con Wit.ai', 500);
        }

        $datos = json_decode($respuesta, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Respuesta inválida de Wit.ai', 500);
        }

        return $datos;
    }

    private function obtenerLaboratorios() {
        $this->logMessage("Intentando obtener datos de laboratorios desde: " . DB_ENDPOINT);
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'header' => "Content-Type: application/json\r\n"
            ]
        ]);
        
        $response = @file_get_contents(DB_ENDPOINT, false, $context);
        
        if ($response === false) {
            $this->logMessage("Error al conectar con la API de laboratorios");
            throw new Exception('Error al obtener datos de laboratorios', 500);
        }
        
        $datos = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logMessage("Respuesta inválida de la API de laboratorios");
            throw new Exception('Respuesta inválida de la API de laboratorios', 500);
        }
        
        if (empty($datos) || !isset($datos['data'])) {
            $this->logMessage("No se encontraron laboratorios en la base de datos");
            throw new Exception('No se encontraron laboratorios en la base de datos', 404);
        }
        
        return $datos;
    }

    private function normalizeText($text) {
        $text = preg_replace('/^(dr|dra|ing|lic|laboratorio de|lab|el|la|los)\s*/i', '', $text);
        $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = strtolower($text);
        return preg_replace('/[^a-z0-9]/', '', $text);
    }

    private function convertirHora($hora) {
        return date("H:i", strtotime($hora));
    }

    private function buscarPorLaboratorio($lab, $datos, $tipo, $horaConsulta = null) {
        if (empty($lab)) return "Laboratorio no especificado";

        $labNormalized = $this->normalizeText($lab);
        $this->logMessage("Buscando laboratorio: '$lab' (normalizado: '$labNormalized')");

        foreach ($datos as $registro) {
            $registroLab = $this->normalizeText($registro['lab_name']);
            $this->logMessage("Comparando con: '{$registro['lab_name']}' (normalizado: '$registroLab')");

            if ($registroLab === $labNormalized) {
                if ($tipo === 'horario' && $horaConsulta) {
                    $horaInicio = strtotime($registro['shift_start']);
                    $horaFin = strtotime($registro['shift_end']);
                    $horaUser = strtotime($this->convertirHora($horaConsulta));

                    if ($horaUser >= $horaInicio && $horaUser <= $horaFin) {
                        return "Laboratorio: {$registro['lab_name']}\nHorario: {$registro['shift_start']} a {$registro['shift_end']}\nEstado: ABIERTO";
                    } else {
                        return "Laboratorio: {$registro['lab_name']}\nHorario: {$registro['shift_start']} a {$registro['shift_end']}\nEstado: CERRADO";
                    }
                }

                switch ($tipo) {
                    case 'supervisor':
                        return "Laboratorio: {$registro['lab_name']}\nSupervisor: {$registro['admin_name']}";
                    case 'horario':
                        return "Laboratorio: {$registro['lab_name']}\nHorario: {$registro['shift_start']} a {$registro['shift_end']}";
                    default:
                        return "Laboratorio: {$registro['lab_name']}\nSupervisor: {$registro['admin_name']}\nHorario: {$registro['shift_start']} - {$registro['shift_end']}";
                }
            }
        }
        return "Laboratorio no encontrado";
    }

    private function buscarPorSupervisor($nombre, $datos) {
        if (empty($nombre)) return "Supervisor no especificado";

        $nombreBusqueda = $this->normalizeText($nombre);
        $this->logMessage("Buscando supervisor: '$nombre' (normalizado: '$nombreBusqueda')");

        foreach ($datos as $registro) {
            $nombreLab = $this->normalizeText($registro['admin_name']);
            $this->logMessage("Comparando con: '{$registro['admin_name']}' (normalizado: '$nombreLab')");

            if (strpos($nombreLab, $nombreBusqueda) !== false) {
                return "Supervisor: {$registro['admin_name']}\nLaboratorio: {$registro['lab_name']}\nHorario: {$registro['shift_start']} - {$registro['shift_end']}";
            }
        }
        return "Supervisor no registrado";
    }

    private function generarRespuesta($witData, $laboratorios) {
        $intencion = $witData['intents'][0]['name'] ?? 'desconocida';
        $lab = $witData['entities']['laboratorio:laboratorio'][0]['body'] ?? '';
        $supervisor = $witData['entities']['supervisor:supervisor'][0]['body'] ?? ''; //aqui cambio
        $hora = $witData['entities']['datetime:datetime'][0]['value'] ?? null;

        $this->logMessage("Generando respuesta para: Intención=$intencion, Laboratorio=$lab, Supervisor=$supervisor, Hora=$hora");

        switch ($intencion) {
            case 'consultar_supervisor':
                return $this->buscarPorLaboratorio($lab, $laboratorios, 'supervisor');
            case 'consultar_horario':
                return $this->buscarPorLaboratorio($lab, $laboratorios, 'horario', $hora);
            case 'buscar_laboratorio':
                return $this->buscarPorSupervisor($supervisor, $laboratorios);
            default:
                $ejemplos = [
                    "Ejemplos de preguntas válidas:",
                    "- ¿Quién supervisa Biología Molecular?",
                    "- ¿A qué hora abre Química Orgánica?",
                    "- ¿Está abierto el laboratorio de Nanotecnología a las 10:00?",
                    "- ¿En qué laboratorio trabaja Carlos Perez?"
                ];
                return implode("\n", $ejemplos);
        }
    }

    private function manejarError($excepcion) {
        $codigo = $excepcion->getCode() ?: 500;
        http_response_code($codigo);
        echo json_encode([
            'status' => 'error',
            'code' => $codigo,
            'message' => $excepcion->getMessage(),
            'trace' => $excepcion->getTraceAsString()
        ]);
    }
}
