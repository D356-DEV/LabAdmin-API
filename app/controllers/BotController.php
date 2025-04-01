<?php
require __DIR__ . "/../models/Bot.php";
require __DIR__ . "/../../config/db.php";

// Configuration constants
define('WIT_TOKEN', '6V4NUA63YM4WRZU6AQPVIOOTJHUTWPVF');
define('DB_ENDPOINT', 'https://api.d356.dev/bot/get_dataset');
define('WIT_API_VERSION', '20240215');

class BotController
{
    private $botModel;

    public function __construct($pdo)
    {
        $this->botModel = new Bot($pdo);
    }

    // Get bot dataset
    public function getBotDataset(): void
    {
        $bot = $this->botModel->getBotDataset();
        if ($bot) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "data" => $bot,
            ]);
        } else {
            http_response_code(404);
            echo json_encode([
                "status" => "error",
                "message" => "No bot dataset found.",
            ]);
        }
    }

    // Handle the user question and provide a response
    public function handleQuestion(): void
    {
        try {

            // Get and validate the question
            $input = json_decode(file_get_contents('php://input'), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON format', 400);
            }

            $pregunta = trim($input['message'] ?? '');
            if (empty($pregunta)) {
                throw new Exception('Message cannot be empty', 400);
            }

            $witData = $this->analizarPregunta($pregunta);
            $laboratorios = $this->obtenerLaboratorios();
            $respuesta = $this->generarRespuesta($witData, $laboratorios);

            echo json_encode([
                'status' => 'success',
                'data' => ['respuesta' => $respuesta]
            ]);
        } catch (Exception $e) {
            $this->manejarError($e);
        }
    }

    // Analyze the question using Wit.ai
    private function analizarPregunta($mensaje)
    {
        $url = "https://api.wit.ai/message?v=" . WIT_API_VERSION . "&q=" . urlencode($mensaje);
        $contexto = stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer " . WIT_TOKEN,
                'timeout' => 3
            ]
        ]);

        $respuesta = @file_get_contents($url, false, $contexto);
        if ($respuesta === false) {
            throw new Exception('Error connecting to Wit.ai', 500);
        }

        $datos = json_decode($respuesta, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid response from Wit.ai', 500);
        }

        return $datos;
    }

    // Get lab data from the external endpoint
    private function obtenerLaboratorios()
    {
        $respuesta = @file_get_contents(DB_ENDPOINT);
        if ($respuesta === false) {
            throw new Exception('Error loading data', 503);
        }

        $datos = json_decode($respuesta, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid data format', 500);
        }

        if (!isset($datos['status']) || $datos['status'] !== 'success' || !isset($datos['data'])) {
            throw new Exception('Data unavailable', 404);
        }

        return $datos['data'];
    }

    // Generate the response based on the Wit.ai data and lab data
    private function generarRespuesta($witData, $laboratorios)
    {
        $intencion = $witData['intents'][0]['name'] ?? 'desconocida';
        $lab = isset($witData['entities']['laboratorio'][0]['value']) 
               ? strtolower($witData['entities']['laboratorio'][0]['value']) 
               : '';
        $supervisor = isset($witData['entities']['supervisor'][0]['value']) 
                     ? strtolower($witData['entities']['supervisor'][0]['value']) 
                     : '';

        switch ($intencion) {
            case 'consultar_supervisor':
                return $this->buscarPorLaboratorio($lab, $laboratorios, 'supervisor');

            case 'consultar_horario':
                return $this->buscarPorLaboratorio($lab, $laboratorios, 'horario');

            case 'buscar_laboratorio':
                return $this->buscarPorSupervisor($supervisor, $laboratorios);

            default:
                return "Valid question examples:\n"
                     . "- Who supervises Molecular Biology?\n"
                     . "- What time does Organic Chemistry open?\n"
                     . "- Where does Dr. Laura Fernandez work?";
        }
    }

    // Search for lab information based on lab name
    private function buscarPorLaboratorio($lab, $datos, $tipo)
    {
        foreach ($datos as $registro) {
            if (strtolower($registro['lab_name']) === $lab) {
                switch ($tipo) {
                    case 'supervisor':
                        return "Lab: {$registro['lab_name']}\n"
                             . "Supervisor: {$registro['admin_name']}";
                    case 'horario':
                        return "Lab: {$registro['lab_name']}\n"
                             . "Hours: {$registro['shift_start']} to {$registro['shift_end']}";
                    default:
                        return "Lab: {$registro['lab_name']}\n"
                             . "Supervisor: {$registro['admin_name']}\n"
                             . "Hours: {$registro['shift_start']} - {$registro['shift_end']}";
                }
            }
        }
        return "Lab not found";
    }

    // Search for lab information based on supervisor's name
    private function buscarPorSupervisor($nombre, $datos)
    {
        $nombreBusqueda = preg_replace('/^(dr|dra|ing|lic)\.?\s*/i', '', $nombre);
        $nombreBusqueda = trim(strtolower($nombreBusqueda));

        foreach ($datos as $registro) {
            $nombreLab = preg_replace('/^(dr|dra|ing|lic)\.?\s*/i', '', $registro['admin_name']);
            $nombreLab = trim(strtolower($nombreLab));

            if ($nombreLab === $nombreBusqueda) {
                return "Supervisor: {$registro['admin_name']}\n"
                     . "Lab: {$registro['lab_name']}\n"
                     . "Hours: {$registro['shift_start']} - {$registro['shift_end']}";
            }
        }
        return "Supervisor not registered";
    }

    // Handle errors and send an error response
    private function manejarError($excepcion)
    {
        $codigo = $excepcion->getCode() ?: 500;
        http_response_code($codigo);

        echo json_encode([
            'status' => 'error',
            'code' => $codigo,
            'message' => $excepcion->getMessage()
        ]);
    }
}
?>
