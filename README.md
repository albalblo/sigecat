# Gestión Económica de Alquileres Turísticos
## Descripción
Este proyecto es el trabajo final del Ciclo de Formación Profesional en Desarrollo de Aplicaciones Web (CFGS DAW) realizado por **Alberto Alsina Ambrós**. La aplicación está diseñada para gestionar económicamente los alquileres turísticos, permitiendo controlar tanto ingresos como gastos de los apartamentos administrados. Facilita la gestión económica detallada de cada uno de ellos.

## Funcionalidades
- Validación de Usuario: Acceso seguro mediante autenticación de usuario.
- Gestión de Apartamentos: Alta, baja y modificación de los detalles del apartamento.
- Registro de Ingresos y Gastos: Incluye funcionalidades para registrar, listar y totalizar los ingresos y gastos.
- Resultados Económicos: Calcula los resultados económicos generales y por apartamento.
- Liquidación Trimestral de IVA: Calcula la liquidación del IVA en base trimestral especificando el trimestre a calcular.
- Estructura de Datos
- Ingresos: Registros de facturas de alquiler emitidas por intermediarios o propias, incluyendo detalles como fechas de entrada y salida, número de noches, datos del cliente, tarifas aplicadas, descuentos, y más.
- Gastos: Registro de gastos relacionados con la gestión de apartamentos, tanto con factura como internos, incluyendo detalles como concepto, fecha, IVA, y total del gasto.
- Usuarios: Gestión de datos de usuarios incluyendo DNI, nombre completo y contraseña.

## Tecnologías Utilizadas
- Frontend: HTML, CSS, JavaScript
- Backend: PHP
- Base de Datos: MySQL

## Instalación
Para instalar y ejecutar este proyecto en su entorno local, siga los siguientes pasos:

1. Clonar el repositorio en su máquina local.
2. Configurar una base de datos MySQL utilizando el script SQL proporcionado en database.sql.
3. Configurar su servidor web para apuntar al directorio del proyecto.
4. Asegurarse de que todos los caminos a archivos y configuraciones de base de datos en php/con_db.php estén correctos según su entorno.

## Uso
Para usar la aplicación, navegue a la URL de su instalación local o del servidor donde esté alojado el proyecto. Inicie sesión con sus credenciales de usuario para acceder a las funcionalidades de gestión.

## Licencia
La **Licencia Creative Commons Atribución-NoComercial-CompartirIgual 4.0 Internacional (CC BY-NC-SA 4.0)** es una licencia que permite a los creadores de obras protegidas por derechos de autor compartir su trabajo con otros de manera gratuita, siempre y cuando se respeten ciertas condiciones:
- Atribución (BY): El licenciante permite a otros distribuir, remezclar, retocar y crear a partir de su obra, siempre y cuando se reconozca la autoría de la obra original de manera adecuada.
- No Comercial (NC): El licenciante permite a otros copiar, distribuir, mostrar y ejecutar la obra, así como hacer obras derivadas basadas en ella, pero no con fines comerciales. Si desean utilizar la obra con fines comerciales, necesitarán obtener permiso explícito del licenciante.
- Compartir Igual (SA): Si se remezcla, transforma o se crea a partir de la obra original, la nueva obra generada debe ser distribuida bajo una licencia idéntica a ésta.

## Autor
Alberto Alsina Ambrós