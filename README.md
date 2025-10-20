Sistema para Final de Algoritmos y Estructura de datos.
    # Frontend #: Interfaz realizada con un desarrollo mínimo de bootstrap, tablas, y construcción de los datos con jQuery en script de ejecución síncrona.
        - Notificaciones realizadas con SweetAlert2.
        - Solicitudes al back realizadas en su totalidad con $.ajax (jQuery)
    # Backend #: Sistema basado en php, con un constructor y un listener
        - Completa devolución de datos, y ejecución de instrucciones en json con método [POST].
        - Fragmentación de los handlers según sector, y propósito de la instrucción, proponiendo una estructura de bajo acoplamiento y alta cohesión en lo máximo posible.
    # Declaración de variables #: 
        -Collector.php: Se propone declaración de variables en inglés con estilo CamelCase asegurando un entendimiento mayor del estandard listener-collector-handler
        -/handlers/**: Variables contenidas dentro de tales clases varían entre ingles y español dependiendo del propósito, y medio del cual provienen
            - Variables y funciones designadas para acarrear una solicitud proveniente del usuario o fuertemente relacionada a un nombre de tabla o campo de la base de datos se presentan en español
            - Variables desginadas para las operaciones lógicas se presentan en inglés para mejor representación del propósito.
        -HTML/SCRIPTS: Variables, y atributos id se presentan en ingles para mayor co-relación con funciones jQuery para facilitar flujo de proceso y entendimiento.
            -Campos value permanecen en español para mejor relación con tablas y campos de la base de datos.
    # Contenidos en carpetas #:
        - index.html: Vista única con ventanas modales donde se grafíca el sistema de forma completamente dinámica.
        - /js/**:
            - jQuery-3.7.1.js
            - scripts.js: Contiene todas las funciones, y eventos para la graficación y manejo de las solicitudes del usuario.
            - ./bootstrap-5.3.8/
            - ./sweetalert2-11.26.2/
        - /backend/**:
            - dbConnect.php: Contiene clase para conexión a la base de datos, utiliza PHP Data Objects (PDO)
            - collector.php: Contiene clase listener/collector de todas las solicitudes ajax.
            - ./handlers/
                - Contiene todos los handlers necesarios para cada módulo del sistema.
        