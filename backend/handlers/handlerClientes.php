<?php
class HandlerClientes
{

    private $pdo;

    public function __construct()//En tiempo de ejecución la conexión a la base de datos ya existe, siendo que está dbConnect.php en primer lugar antes que la clase, y la llamada a dicha clase.
    {
        $this->pdo = DB::connect();
    }
    public function handle($action, $data)
    {
        $action = explode('_', $action);
        switch ($action[1]) {
            case 'list'://Trae tabla completa de clientes, sin parámetro
                return $this->listar($data);
            case 'servicios'://Trae relación completa entre un cliente y sus servicios, el vehículo al que pertenece, monto total, y fechas.
                return $this->serviciosCliente($data);
            case 'servicioNuevo'://Realizar insersión de nuevo servicio a cliente + vehículo pre-seleccionados (se inicia con monto en 0, estado = Recibido, con 0 trabajos realizados)

            case 'servicioAgregar'://Realizar insersión a servicio existente, un nuevo trabajo, su monto, fecha, y insertar en consecuencia a tabla servicio el monto total actualizado, fecha estado + estado = en proceso

            case 'servicioModificar'://Realizar cambios al estado del servicio, como estado terminado no entregado y entregado, modifica fecha estado a su vez.

            default:
                throw new Exception("Acción no soportada", 1);
        }

    }
    private function listar($data)
    {
        try {
            $sql = "SELECT * FROM clientes";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($product) {
                echo json_encode(['status' => 'success', 'data' => $product]);
            } else {
                // http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            }


        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar el producto: ' . $e->getMessage()]);
        }
    }
    private function serviciosCliente($data)
    {
        try {
            $sql = "SELECT s.id, v.patente, m.nombre,c.apellido_nombre, s.fecha, s.importe,s.fecha_estado,s.estado FROM vehiculos AS v JOIN modelos AS m ON v.id_modelo = m.id JOIN clientes AS c ON c.id = v.id_cliente JOIN servicios AS s ON s.id_vehiculo = v.id WHERE c.id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['clientId']]);
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($product) {
                echo json_encode(['status' => 'success', 'data' => $product]);
            } else {
                // http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar el producto: ' . $e->getMessage()]);
        }
    }
}
