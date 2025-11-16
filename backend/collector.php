<?php
header('Content-Type: application/json; charset=utf-8');
require_once './dbConnect.php';
class Collector
{
    private $handlers = [];
    public function __construct()
    {
        $this->handlers = [
            /* --Mapeo de todos los handlers a disposición--
            TRAE LISTA COMPLETA DE CLIENTES
            ADMITE BUSQUEDA DE TODOS LOS SERVICIOS ASOCIADOS
            ADMITE CREACION DE NUEVO SERVICIO ASOCIADO A CLIENTE
            ADMITE EDICION DE SERVICIO ASOCIADO A CLIENTE */
            'clientes' => 'handlerClientes.php',
            /* TRAE LISTA COMPLETA DE VEHICULOS ASOCIADOS A CLIENTE
                ADMITE ASOCIACION DE VEHICULO SELECCIONADO A NUEVO SERVICIO
                ADMITE NUEVOS TRABAJO ASOCIADOS A SERVICIO Y VEHICULO */
            'vehiculos' => 'handlerVehiculos.php',
            /* TRAE LISTA COMPLETA DE SERVICIOS Y SUS VINCULACIONES
                ADMITE BUSQUEDA DE TODOS LOS TRABAJOS ASOCIADOS
                ADMITE CREACION DE NUEVO TRABAJO ASOCIADO A SERVICIO DE CLIENTE
                ADMITE EDICIÖN DE SERVICIO ASOCIADO A CLIENTE */
            'servicios' => 'handlerServicios.php',

        ];
    }

    public function ProcessRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {//SI ES POR GET
            $this->showError('Método no permitido', 405);
            return;
        }
        $action = $_POST['action'] ?? '';//EXAMPLE: clientes_listar,clientes_añadir,clientes_eliminar,vehículos_modificar,servicios_agregar
        $explodedAction = explode('_', $action); //Define a cual grupo/handler apuntar: clientes,servicios,vehiculos,modelos
        if (empty($action)) {//SI ESTA VACIO PARAMETRO action
            $this->showError('Acción no válida.', 400);
            return;
        }
        if (count($explodedAction) !== 2) {//Si el array obtenido no es igual a [2] entonces el parametro no es el correcto
            $this->showError('Formato de acción inválido. Usar: grupo_accion', 400);
            return;
        }
        $group = $explodedAction[0];
        if (!isset($this->handlers[$group])) {// SI NO EXISTE HANLDER CON ESE NOMBRE
            $this->showError('Acción no válida:' . $action, 400);
            return;
        }
        $this->dispatchToHandler($group, $action);
    }
    private function dispatchToHandler($group, $action)
    {
        $handlerFile = $this->handlers[$group];
        $handlerPath = __DIR__ . '/handlers/' . $handlerFile;
        if (!file_exists($handlerPath)) {
            $this->showError('Handler no encontrado', 404);
            return;
        }
        try {
            require_once $handlerPath;
            $handlerClass = str_replace('.php', '', $handlerFile);//Obtiene el nombre de clase de forma dinámica
            $handler = new $handlerClass();
            $handler->handle($action, $_POST); //todos los archivos deben de contener la misma clase con nombre "handle" su contenido puede variar, ya que el archivo que lo incluye cambia dependiento el contenido de $_POST['action']
        } catch (Exception $e) {
            $this->showError('Error:' . $e, 500);
        }

    }
    private function showError($message, $httpCode = 400)//Simple función para evitar repetir response_code y json_encode para mostrar errores.
    {
        http_response_code($httpCode);
        echo json_encode(['status' => 'error', 'message' => $message]);
    }

}
//Sección funcional del archivo (ejecuta clase y función principal de collector cada vez que se recibe una solicitud por POST)
$collector = new Collector();
$collector->ProcessRequest();
