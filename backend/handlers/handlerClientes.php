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
            case 'nuevo'://Presentar datos del cliente, listado de vehículos asociados, para posterior creación de nuevo servicio
                return $this->servicioNuevo($data);
            case 'servicioAgregar'://Confirmar y realizar insersión de nuevo servicio con todos los datos ingresados por el usuario, vehículo, fecha, importe inicial.
                return $this->servicioAgregar($data);
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
            $sql = "SELECT c.id AS c_id, v.id AS v_id, s.id AS s_id, v.patente, m.nombre,c.apellido_nombre, s.fecha, s.importe,s.fecha_estado,s.estado FROM vehiculos AS v JOIN modelos AS m ON v.id_modelo = m.id JOIN clientes AS c ON c.id = v.id_cliente LEFT JOIN servicios AS s ON s.id_vehiculo = v.id WHERE c.id = ?";
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
    private function servicioNuevo($data)
    {
        try {
            $sql = "SELECT v.id AS v_id, v.patente, m.nombre FROM vehiculos AS v JOIN modelos AS m ON v.id_modelo = m.id WHERE v.id_cliente = ?";
            $stmtVehicles = $this->pdo->prepare($sql);
            $stmtVehicles->execute([$data['clientId']]);
            $vehicles = $stmtVehicles->fetchAll(PDO::FETCH_ASSOC);

            $sqlClient = "SELECT c.id AS c_id, c.apellido_nombre, c.DNI FROM clientes AS c WHERE c.id = ?";
            $stmtClient = $this->pdo->prepare($sqlClient);
            $stmtClient->execute([$data['clientId']]);
            $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

            if (isset($data['vehicleId'])) {
                // Si se proporcionó un vehicleId, filtrar el vehículo correspondiente
                $sql = "SELECT v.id AS v_id, v.patente, m.nombre FROM vehiculos AS v JOIN modelos AS m ON v.id_modelo = m.id WHERE v.id = ? AND v.id_cliente = ?";
                $stmtVehicle = $this->pdo->prepare($sql);
                $stmtVehicle->execute([$data['vehicleId'], $data['clientId']]);
                $vehicle = $stmtVehicle->fetch(PDO::FETCH_ASSOC);
            }
            if ($vehicles && $client) {
                echo json_encode(['status' => 'success', 'client' => $client, 'vehicles' => $vehicles, 'selectedVehicle' => $vehicle ?? null]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'No se encontraron vehículos o cliente.']);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar los datos: ' . $e->getMessage()]);
        }
    }
    private function servicioAgregar($data)
    {
        if (!$data['vehicleId'] || !$data['fecha'] || $data['importe'] < 0 || !isset($data['importe']) ) {
            echo json_encode(['status' => 'error', 'message' => 'Faltan datos obligatorios para agregar el servicio.']);
            return;
        }

        try {
            $sql = "INSERT INTO servicios (id_vehiculo, fecha, importe, estado, fecha_estado) VALUES (?, ?, ?, '0', ?)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['vehicleId'],
                $data['fecha'],
                $data['importe'],
                $data['fecha']
            ]);
            $newServiceId = $this->pdo->lastInsertId();
            echo json_encode(['status' => 'success', 'message' => 'Servicio agregado correctamente.', 'serviceId' => $newServiceId]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al agregar el servicio: ' . $e->getMessage()]);
        }
    }
}
