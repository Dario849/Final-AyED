<?php
class HandlerServicios
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
            case 'list'://Trae tabla completa de servicios, sin parametros
                return $this->listServices($data);
            case 'trabajos'://Trae relación completa entre un servicio, y sus trabajos, trae información completa del servicio, cliente, y matriz completa de trabajos realizados (se espera id de servicio como parametro).
                return $this->listJobsOfService($data);
            case 'modificar'://Realizar modificación del estado del servicio, como estado terminado no entregado y entregado, modifica fecha estado a su vez.
                return $this->modifyServiceState($data);
            case 'agregar'://Realizar insersión a servicio existente, un nuevo trabajo, su monto, fecha, y insertar en consecuencia a tabla servicio el monto total actualizado, fecha estado + estado = en proceso
                return $this->addJobToService($data);
            // case 'modificar'://Realizar cambios al estado del servicio, como estado terminado no entregado y entregado, modifica fecha estado a su vez.
            case 'substraerTrabajo'://Eliminar un trabajo de un servicio, actualizar monto total del servicio en consecuencia.
                return $this->substractJobFromService($data);
            default:
                throw new Exception("Acción no soportada", 1);
        }

    }
    private function listServices($data)
    {
        try {
            $sql = "SELECT s.*, c.apellido_nombre,c.DNI,v.patente,m.nombre FROM servicios AS s LEFT JOIN vehiculos AS v ON v.id = s.id_vehiculo LEFT JOIN clientes AS c ON c.id = v.id_cliente LEFT JOIN modelos AS m ON m.id = v.id_modelo ORDER BY s.importe DESC";
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
    private function listJobsOfService($data)
    {
        try {
            $sql = "SELECT 
            s.id AS s_id,
            s.fecha AS s_fecha,
            s.importe AS s_importe,
            s.estado AS s_estado,
            s.fecha_estado AS s_fecha_estado,
            v.id AS v_id,
            v.patente AS v_patente,
            c.id AS c_id,
            c.apellido_nombre,
            c.DNI,
            c.te_linea,
            c.email,
            c.observaciones,
            c.movil,
            m.nombre AS m_nombre,
            t.id AS t_id,
            t.nombre AS t_nombre,
            sd.cantidad AS sd_cantidad,
            sd.importe AS sd_importe,
            sd.id AS sd_id
            FROM servicios AS s 
            INNER JOIN vehiculos AS v ON s.id_vehiculo = v.id
            INNER JOIN modelos AS m ON v.id_modelo = m.id
            INNER JOIN clientes AS c ON v.id_cliente = c.id
            LEFT JOIN servicios_det AS sd ON s.id = sd.id_servicio
            LEFT JOIN trabajos AS t ON sd.id_trabajo = t.id
            WHERE s.id= ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['serviceId']]);
            $product = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $sql = "SELECT * FROM trabajos ORDER BY importe ASC";
            $stmt2 = $this->pdo->prepare($sql);
            $stmt2->execute();
            $jobs = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            if ($product) {
                echo json_encode(['status' => 'success', 'data' => $product, 'jobsList' => $jobs]);
            } else {
                // http_response_code(404);
                echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado.']);
            }

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar el producto: ' . $e->getMessage()]);
        }
    }
    private function addJobToService($data)
    {
        try {
            $total = 0;
            $this->pdo->beginTransaction();
            $sql = "SELECT importe FROM trabajos WHERE id = ?";//trae el importe del trabajo
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['jobId']]);
            $jobImporte = $stmt->fetchColumn();
            $total = $data['jobCount'] * $jobImporte;

            $sql = "INSERT INTO servicios_det (id_servicio, id_trabajo, id_vehiculo, cantidad, importe) VALUES (?, ?, ?, ?, ?)"; // inserta el nuevo trabajo, sin importar si es el primero del servicio, o ya existen anteriores.
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['serviceId'], $data['jobId'], $data['vehicleId'], $data['jobCount'], $jobImporte]);

            $sql = "SELECT fecha_estado FROM servicios WHERE id = ?"; // busca si hay importe presente en el servicio (un servicio nuevo debería de tener 0 en mayoria de los casos)
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['serviceId']]);
            $dateState = $stmt->fetchColumn();

            $sql = "SELECT COUNT(*) FROM servicios_det WHERE id_servicio = ?"; //busca servicios_det presentes asociados al servicio
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['serviceId']]);
            $hasServiceDetails = $stmt->fetchColumn() > 1; // si el resultado es mayor a 1, almacena el dato
            if(!$dateState && (!$hasServiceDetails)){
                $sql = "UPDATE servicios SET importe = importe + ?, estado = '1', fecha_estado = NOW() WHERE id = ?"; // si no hay importe existen, y tampoco tiene otros detalles de servicio, modifica el estado, y el importe, ya que se asume es el primer trabajo del servicio.
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$total, $data['serviceId']]);
            }else{
                $sql = "UPDATE servicios SET importe = importe + ? WHERE id = ?"; // si es falso, entonces hay otros trabajos anteriores a este, solo actualiza importe.
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$total, $data['serviceId']]);
            }


            $this->pdo->commit();

            echo json_encode(['status' => 'success', 'message' => 'Trabajo agregado al servicio correctamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al buscar el producto: ' . $e->getMessage()]);
        }
    }
    private function substractJobFromService($data)
    {
        try {
            $this->pdo->beginTransaction();

            if ($data['jobCount'] > 1) {
                $sql = "UPDATE servicios_det SET cantidad = cantidad - 1 WHERE id = ? AND id_trabajo = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$data['serviceDetailId'], $data['jobId']]);

                $sql = "UPDATE servicios SET importe = importe - (SELECT importe FROM trabajos WHERE id = ?) WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$data['jobId'], $data['serviceId']]);
            } else {
                $sql = "DELETE FROM servicios_det WHERE id = ? AND id_trabajo = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$data['serviceDetailId'], $data['jobId']]);
                $sql = "UPDATE servicios SET importe = importe - (SELECT importe FROM trabajos WHERE id = ?) WHERE id = ?";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$data['jobId'], $data['serviceId']]);
            }

            $this->pdo->commit();

            echo json_encode(['status' => 'success', 'message' => 'Trabajo eliminado del servicio correctamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el trabajo del servicio: ' . $e->getMessage()]);
        }
    }
    private function modifyServiceState($data)
    {
        try {
            $sql = "UPDATE servicios SET estado = ?, fecha_estado = NOW() WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$data['newState'], $data['serviceId']]);

            echo json_encode(['status' => 'success', 'message' => 'Estado del servicio modificado correctamente.']);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al modificar el estado del servicio: ' . $e->getMessage()]);
        }
    }
}
