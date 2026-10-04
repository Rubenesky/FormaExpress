# Manual de funcionamiento de FormaExpress

FormaExpress es una aplicación para formar y certificar al personal: el
administrador prepara los cursos, los empleados los completan y se evalúan, y
los encargados consultan quién está formado en qué.

Este manual explica qué ve y qué puede hacer cada perfil. Para ponerla en marcha,
consulta antes la [guía de instalación](instalacion.md).

---

## Usuarios de prueba

> ⚠️ **AVISO IMPORTANTE: estas contraseñas son públicas y SOLO sirven para
> evaluar el proyecto en local.** No deben usarse nunca si el proyecto se
> despliega de verdad. En un despliegue real hay que crear las cuentas con
> contraseñas propias.

| Perfil | Email | Contraseña | Cómo se crea |
|---|---|---|---|
| Administrador | `admin@formaexpress.test` | `admin1234` | `php artisan db:seed` (obligatorio) |
| Empleado | `empleado@formaexpress.test` | `empleado1234` | `php artisan db:seed --class=UsuariosDemostracionSeeder` (opcional) |
| Encargado | `encargado@formaexpress.test` | `encargado1234` | `php artisan db:seed --class=UsuariosDemostracionSeeder` (opcional) |

Estos usuarios no tienen ningún curso asignado ni ningún dato de formación:
hay que crearlos desde la aplicación (ver el perfil de administrador).

---

## 1. Entrar y salir

- **Iniciar sesión:** en la pantalla de inicio, con el correo electrónico y la
  contraseña. La casilla *Recordarme* mantiene la sesión abierta.
- **Registrarse:** el enlace *Registrarse* del menú crea una cuenta nueva. **Toda
  cuenta creada así es de empleado**: no se puede elegir otro perfil.
- **Cerrar sesión:** en el menú, pulsa tu nombre (arriba a la derecha) y luego
  *Cerrar sesión*.
- **¿Has olvidado tu contraseña?:** el enlace existe, pero en la instalación local
  el correo **no se envía de verdad** (queda escrito en el registro de la
  aplicación, `storage/logs`). Ver *Limitaciones*.

Al entrar, cada perfil ve un menú distinto:

| Perfil | Menú |
|---|---|
| Administrador | **Consulta**, **Cursos**, **Registro de consultas** |
| Empleado | **Mis cursos**, **Mis logros** |
| Encargado | **Consulta** |

Si alguien intenta abrir una página que no corresponde a su perfil (por
ejemplo, un empleado escribiendo la dirección de la lista de cursos), la
aplicación responde con un error 403 (*acceso denegado*).

---

## 2. Perfil administrador

El administrador prepara toda la formación: cursos, lecciones, evaluaciones y
asignaciones. También puede consultar a la plantilla y es el único que ve el
registro de consultas.

### 2.1. Cursos

Menú **Cursos**. Muestra los cursos de 10 en 10 con su título, descripción y
número de lecciones. Cada fila tiene estos botones: *Lecciones*, *Asignaciones*,
*Evaluación*, *Editar* y *Eliminar*.

- **Crear:** botón *Nuevo curso*. Título obligatorio; descripción opcional.
- **Editar:** botón *Editar* de la fila.
- **Eliminar:** botón *Eliminar*. Primero se muestra una página de confirmación:
  - Si el curso **no tiene datos de empleados**, avisa de lo que se borrará con
    él (sus lecciones y, si la tiene, su evaluación con sus preguntas) y pide
    confirmar con *Sí, eliminar el curso*. No se puede deshacer.
  - Si el curso **tiene datos de empleados** (empleados asignados, lecciones
    completadas, intentos de evaluación o certificados emitidos), avisa de que
    **no se puede eliminar**, indica cuáles hay y no ofrece el botón de borrar.
    Así nunca se pierde el historial de formación.

### 2.2. Lecciones

Botón *Lecciones* de un curso. Lista las lecciones por su número de orden, con
su fecha de última modificación.

- **Crear:** *Nueva lección*. Título, número de orden (se propone el siguiente al
  más alto que ya exista) y contenido. Los saltos de línea del contenido se respetan.
- **Editar** y **Eliminar**, con confirmación. Una lección que algún empleado ya
  ha completado **no se puede eliminar**.

### 2.3. Asignar cursos a empleados

Botón *Asignaciones* de un curso.

- **Asignar:** elige un empleado en el desplegable y pulsa *Asignar*. Solo
  aparecen los empleados que aún no tienen el curso. Queda guardada la fecha de
  asignación.
- **Lista de asignados:** nombre, correo, fecha de asignación y columna
  **Certificado**, con la fecha de emisión y un enlace para descargar el PDF si
  el empleado ya lo ha conseguido.
- **Desasignar:** botón *Desasignar*, con confirmación. Si el empleado **ya ha
  completado alguna lección** del curso, no se puede desasignar.

### 2.4. Evaluación del curso

Botón *Evaluación* de un curso. Cada curso tiene **como mucho una evaluación**,
de tipo test.

- **Crear la evaluación:** se propone el título "Evaluación: *nombre del curso*"
  y una **nota mínima de 8,00** sobre 10, que se puede cambiar (de 0 a 10, con
  hasta 2 decimales).
- **Preguntas:** *Nueva pregunta*. Cada pregunta tiene un enunciado y hasta 6
  opciones; hay que rellenar **al menos 2** y marcar **exactamente una como
  correcta** (el botón redondo de su izquierda). Las opciones que sobren se dejan
  vacías. La aplicación no deja guardar una pregunta que no cumpla esto.
- La pantalla de la evaluación muestra todas las preguntas con su respuesta
  correcta marcada (solo el administrador la ve) y cuántos intentos han hecho ya
  los empleados.
- **Editar o eliminar preguntas** es posible en cualquier momento. Si ya hay
  intentos de empleados, la aplicación lo avisa: cambiar las preguntas hace que
  las notas anteriores ya no correspondan al test actual.
- **Eliminar la evaluación** borra también sus preguntas. Si algún empleado ya
  ha hecho la evaluación, **no se puede eliminar**.

Una evaluación sin preguntas no se ofrece a los empleados.

### 2.5. Certificados

Los certificados **no se crean a mano**: se emiten solos cuando un empleado
aprueba por primera vez la evaluación de un curso (ver 3.4). El administrador
puede descargar el PDF de cualquier certificado desde las asignaciones del
curso.

### 2.6. Consulta de la plantilla

Menú **Consulta**. Es la misma pantalla que usa el encargado (ver la sección 4).

### 2.7. Registro de consultas (solo administrador)

Menú **Registro de consultas**. Muestra, de 25 en 25 y empezando por la más
reciente, cada vez que alguien abrió la ficha de un empleado desde *Consulta*:

| Fecha y hora | Consultado por | Rol | Empleado consultado |
|---|---|---|---|

Es un registro exigido por la protección de datos (RGPD): **no se puede editar
ni borrar** desde la aplicación, ni siquiera el administrador. El encargado no
puede verlo.

---

## 3. Perfil empleado

El empleado hace los cursos que le han asignado, se evalúa y consigue
certificados e insignias.

### 3.1. Mis cursos

Menú **Mis cursos**. Una tarjeta por cada curso asignado con:

- cuántas lecciones lleva completadas ("2 de 3 lecciones") y el porcentaje, con
  una barra de progreso (verde al llegar al 100 %);
- la etiqueta **Certificado** si ya lo ha conseguido;
- el botón *Ver curso*.

Solo aparecen los cursos asignados; los demás no se pueden abrir.

### 3.2. Un curso y sus lecciones

Al abrir un curso se ven su progreso, la lista de lecciones en orden (cada una
marcada como *Completada* o *Pendiente*) y, si existe, el bloque de la
evaluación.

Al abrir una lección se ve su contenido y:

- **Marcar como completada:** registra la lección como terminada con la fecha y
  hora. Una vez completada, aparece "Completada el *fecha*". Volver a pulsarlo
  no cambia nada.
- *Siguiente lección* y *Volver al curso* para moverse por el curso.

### 3.3. Hacer la evaluación

La evaluación se **desbloquea** cuando el curso tiene al menos una lección y el
empleado las ha completado **todas**. Mientras tanto, el curso muestra
"Completa todas las lecciones para desbloquear la evaluación".

- Se responden todas las preguntas (una opción en cada una) y se pulsa
  *Enviar respuestas*. Si falta alguna, la aplicación lo indica y ese envío no
  cuenta como intento.
- **Nota:** aciertos ÷ número de preguntas × 10, con 2 decimales. Los fallos no
  restan. Se aprueba con una nota igual o superior a la nota mínima del curso.
- **Resultado:** nota, *Aprobado* o *Suspendido*, cuántas acertó y **qué
  preguntas falló**, sin decir cuál era la respuesta correcta.
- **Intentos ilimitados:** se puede repetir siempre que se quiera. Se guardan
  todos los intentos y la pantalla de resultado muestra el historial.

### 3.4. Certificados

Al **aprobar por primera vez** la evaluación de un curso, se emite
automáticamente el certificado, con la fecha de ese día. La pantalla de
resultado lo anuncia con un botón para descargarlo. Aprobar otra vez no emite
otro certificado ni cambia la fecha.

El certificado es un PDF con el nombre del empleado, el curso, la fecha de
emisión y un código (por ejemplo, `FX-000001`). Solo pueden descargarlo el
propio empleado y el administrador.

**Un curso sin evaluación no da certificado**, aunque se completen todas sus
lecciones.

### 3.5. Mis logros: certificados e insignias

Menú **Mis logros**:

- **Certificados:** curso, fecha de emisión, código y botón *Descargar*.
- **Insignias:** las cuatro que existen, cada una marcada como *Conseguida* (con
  la fecha) o *Pendiente*. Se consiguen solas:

| Insignia | Cómo se consigue |
|---|---|
| Primer curso certificado | Obtener el primer certificado |
| 5 cursos certificados | Obtener 5 certificados |
| Nota máxima | Sacar un 10 en una evaluación |
| A la primera | Aprobar una evaluación en el primer intento |

Cuando un intento consigue una insignia nueva, la pantalla de resultado lo
anuncia. Cada insignia se consigue una sola vez.

---

## 4. Perfil encargado (solo consulta)

El encargado comprueba la formación de los empleados **sin poder cambiar nada**.
Solo tiene el menú **Consulta**; cualquier otra página le devuelve un error 403.

### 4.1. Buscar un empleado

Escribe **al menos 2 letras** del nombre y pulsa *Buscar*. Aparecen los
empleados cuyo nombre las contiene, con su correo y el botón *Ver ficha*. Solo
se buscan empleados (no administradores ni otros encargados).

### 4.2. Ficha de un empleado

Muestra, solo para consultar:

- sus **cursos asignados**, con las lecciones completadas, el porcentaje y una
  barra de progreso;
- si está **certificado** en cada curso y en qué fecha (el encargado **no**
  puede descargar el PDF);
- sus **insignias**, con la fecha en que las consiguió.

No hay ningún botón para crear, editar ni borrar.

> **Protección de datos:** cada vez que se abre la ficha de un empleado, la
> aplicación anota quién la abrió, de quién y cuándo. Ese registro solo lo ve el
> administrador. La búsqueda no se anota, porque solo muestra nombre y correo.

---

## 5. Limitaciones conocidas

- **No hay gestión de usuarios en la aplicación.** El registro público solo crea
  empleados; los administradores y encargados se crean con los seeders o
  directamente en la base de datos. Tampoco hay pantalla para cambiar la
  contraseña ni para dar de baja a un usuario.
- **Recuperar la contraseña** no envía correos reales en local: el mensaje queda
  en `storage/logs`.
- **Editar las preguntas de una evaluación que ya tiene intentos** está
  permitido y cambia el significado de las notas anteriores (la aplicación lo
  avisa, pero no lo impide).
- La interfaz necesita **conexión a internet** para cargar los estilos
  (Bootstrap desde un CDN).
- **Despliegue online pendiente:** todavía no existe ninguna versión publicada en
  internet.
