# Documentación de las pruebas de FormaExpress

Este documento describe la batería de pruebas automáticas que **ya existe** en
el proyecto: qué tipo de pruebas son, qué casos cubren, cómo se ejecutan y qué
resultado dan hoy.

---

## 1. Resumen

Resultado de ejecutar la batería completa el 30 de octubre de 2026:

| Tests | Aserciones | Fallos | Errores | Omitidos |
|---|---|---|---|---|
| **165** | **727** | 0 | 0 | 0 |

| Módulo | Archivos | Tests | Aserciones |
|---|---|---|---|
| Cursos y lecciones | `CursoTest`, `LeccionTest` | 29 | 132 |
| Asignación y progreso | `AsignacionTest`, `ProgresoTest` | 22 | 88 |
| Evaluaciones | `EvaluacionAdminTest`, `HacerEvaluacionTest` | 34 | 182 |
| Certificados e insignias | `CertificadoTest`, `InsigniaTest` | 19 | 56 |
| Consulta del encargado y registro RGPD | `ConsultaTest` | 15 | 90 |
| Sistema de ayuda en línea | `AyudaTest` | 4 | 29 |
| Transversales (acceso, registro, idioma, formularios manipulados) | `AutenticacionTest`, `EntradaManipuladaTest`, `Feature/ExampleTest`, `Unit/ExampleTest` | 22 | 127 |
| Pruebas de unidad (nota, aprobado, progreso e insignias) | `CalificadorTest`, `LogrosTest` | 20 | 23 |
| **Total** | 16 archivos | **165** | **727** |

Una *aserción* es cada comprobación individual dentro de un test (por ejemplo,
"la respuesta es 200" o "la tabla tiene 1 fila"). Un test pasa solo si pasan
todas sus aserciones.

---

## 2. Tipo de pruebas

**La batería tiene dos tipos de prueba reales: 20 de unidad (`Calificador` y
`Logros::hitos()`) y 144 de integración (*feature tests* de Laravel).**

- **Pruebas de unidad** (`tests/Unit/CalificadorTest.php` y
  `tests/Unit/LogrosTest.php`, 20 tests): prueban lógica de cálculo extraída
  de los modelos Eloquent a `app/Support/`, sin Eloquent ni base de datos.
  `App\Support\Calificador::nota()`, `::aprueba()` y `::progreso()` calculan
  la nota de una evaluación, si aprueba y el % de progreso de un curso a
  partir de números sueltos. `App\Support\Logros::hitos()` decide qué
  insignias corresponden a un resultado (aprobado, nota, si es el primer
  intento, cuántos certificados tiene ya) sin guardar nada. Son de unidad de
  verdad porque: extienden `PHPUnit\Framework\TestCase` directamente (no
  `Tests\TestCase`), no usan `RefreshDatabase` ni tocan la base de datos, no
  hacen ninguna petición HTTP, y cada test pasa una entrada concreta y
  comprueba la salida exacta de un método estático. La carpeta `tests/Unit`
  también sigue teniendo `ExampleTest::test_that_true_is_true`, el ejemplo
  que trae Laravel al instalarlo: no prueba nada del proyecto, se cuenta en
  el total pero no aporta cobertura.
- Los 144 tests de integración están en `tests/Feature`. Cada uno arranca la
  aplicación Laravel completa, crea en una base de datos los datos que
  necesita (salvo los 3 de `Feature/ExampleTest`, que no crean ninguno), simula
  peticiones HTTP (como si fuera el navegador: `GET /cursos`, `POST /cursos`…)
  y comprueba lo que se indica abajo. Casi todas las peticiones se hacen con la
  sesión iniciada como un usuario de un rol concreto; se hacen sin sesión las de
  los 3 de `Feature/ExampleTest`, las de login y registro, y las que comprueban
  que un invitado es enviado al login (por ejemplo,
  `test_un_invitado_es_enviado_al_login`). Lo que se comprueba:
  - la respuesta (código HTTP, redirección, texto que aparece o no en la página,
    errores de validación y su mensaje en español);
  - el estado de la base de datos después (filas creadas, borradas o sin
    cambios).
- Algunos tests de integración comprueban lógica concreta sin pasar por HTTP
  (por ejemplo, que la base de datos impide duplicar una insignia, o que un
  seeder es idempotente), pero a diferencia de los de unidad de arriba sí
  usan Eloquent y la base de datos real, así que siguen siendo de
  integración.

**Base de datos de las pruebas:** SQLite en memoria (configurado en
`phpunit.xml`), no MySQL, así que los tests no tocan los datos de la
instalación. Los 11 archivos que crean datos usan `RefreshDatabase`: cada uno de
sus tests empieza con la base de datos vacía y recién migrada, y no depende de
los demás. Los dos `ExampleTest` no lo usan y no crean ningún dato (solo
piden `/`, `/home` y `/login`, o comprueban que `true` es `true`).

**Qué no hay** (para no presentarlo como cubierto):

- Pruebas de unidad de otra lógica aparte de `Calificador` y `Logros::hitos()`.
  Por ejemplo, `App\Support\EntradaAntigua::limpiar()` también es un método
  estático puro (sin Eloquent ni base de datos), pero solo tiene cobertura
  indirecta, vía el test de integración `EntradaManipuladaTest`.
- Pruebas en navegador o de extremo a extremo (*E2E*): no se ejecuta JavaScript
  ni se comprueba el aspecto visual.
- Pruebas contra MySQL: las restricciones de clave foránea se prueban en
  SQLite.
- Pruebas de carga o rendimiento.

---

## 3. Cómo se ejecuta

Desde la carpeta del proyecto, con PHP 8.3 o superior y la extensión
`pdo_sqlite` activa (ver la [guía de instalación](instalacion.md), paso 10):

```bash
php artisan test
```

Equivalente, llamando a PHPUnit directamente:

```bash
vendor/bin/phpunit
```

Variantes útiles:

```bash
php artisan test --testsuite=Feature            # solo los de integración
php artisan test --filter=HacerEvaluacionTest   # un archivo
php artisan test --filter=test_un_envio_incompleto_no_cuenta_como_intento   # un test
```

No hace falta MySQL, ni el servidor arrancado, ni cargar seeders: cada test se
prepara sus propios datos.

### Qué significa el resultado

- Al final aparece una línea como **`Tests:    165 passed (727 assertions)`** y el
  comando termina con código de salida `0`: todo está bien.
- Si algún test falla, aparece marcado como `FAIL` con el nombre del test, la
  aserción que no se cumplió (valor esperado frente a valor obtenido) y el
  archivo y la línea. La línea final indica cuántos fallaron y el código de
  salida es distinto de `0`.
- Un *error* (en lugar de un *fallo*) significa que el test no llegó a
  comprobar nada porque se produjo una excepción (por ejemplo, falta
  `pdo_sqlite`).

---

## 4. Casos que exige el enunciado

El enunciado pide expresamente pruebas de validación de **tipos de dato,
longitud, caracteres especiales y máscaras**. Esto es lo que hay, tal cual:

### Tipos de dato

| Campo | Entrada | Salida esperada | Test |
|---|---|---|---|
| Orden de lección | `abc` | Error "El campo orden debe ser un número entero." y no se crea | `LeccionTest::test_la_validacion_de_lecciones_esta_en_espanol` |
| Orden de lección | `0` | Error "El campo orden debe ser al menos 1." | ídem |
| Nota mínima | `abc` | Error "El campo nota mínima debe ser un número." | `EvaluacionAdminTest::test_la_nota_minima_se_valida` |
| Nota mínima | `11` | Error "El campo nota mínima debe estar entre 0 y 10." | ídem |
| Nota mínima | `8.555` | Error "La nota mínima admite como máximo 2 decimales." | ídem |
| Nota mínima | vacío | Error "El campo nota mínima es obligatorio." | ídem |
| Empleado a asignar | id de un encargado, de un administrador o inexistente (`99999`) | Error "El empleado seleccionado no es válido." y ninguna asignación | `AsignacionTest::test_solo_se_asigna_a_empleados` |
| Empleado a asignar | vacío | Error "El campo empleado es obligatorio." | ídem |
| Opción correcta de una pregunta | ninguna marcada / una vacía marcada / dos marcadas (petición manipulada que envía un array) | "Marca cuál es la opción correcta." / "La opción marcada como correcta está vacía." / error en `correcta`; en los tres casos no se guarda nada | `EvaluacionAdminTest::test_una_pregunta_necesita_exactamente_una_correcta` |
| Cualquier campo de texto de todos los formularios | un **array** en lugar de texto (petición manipulada, p. ej. `titulo[]=x`) | Se rechaza, vuelve al formulario con el error y la página carga (nunca un error 500); no se guarda nada | `EntradaManipuladaTest` (6 tests, 67 aserciones) |
| Respuesta de la evaluación | la opción correcta de **otra** pregunta | Cuenta como fallo (nota 0) | `HacerEvaluacionTest::test_una_opcion_de_otra_pregunta_cuenta_como_fallo` |
| Correo del registro | `esto-no-es-un-correo` | "El campo correo electrónico debe ser un correo electrónico válido.", sin errores en los demás campos; no se crea la cuenta ni se inicia sesión | `AutenticacionTest::test_el_registro_rechaza_un_email_con_formato_invalido` |

### Obligatoriedad

| Campo | Entrada | Salida esperada | Test |
|---|---|---|---|
| Título del curso | vacío | "El campo título es obligatorio." y no se crea | `CursoTest::test_el_titulo_es_obligatorio_con_mensaje_en_espanol` |
| Descripción del curso | vacía | Se acepta (es opcional) | `CursoTest::test_la_descripcion_es_opcional` |
| Título y contenido de lección | vacíos | "El campo título/contenido es obligatorio." | `LeccionTest::test_la_validacion_de_lecciones_esta_en_espanol` |
| Opciones de una pregunta | menos de 2 rellenas | Error, no se guarda | `EvaluacionAdminTest::test_una_pregunta_necesita_al_menos_dos_opciones` |
| Respuestas de la evaluación | falta alguna pregunta | Vuelve con error y **no** se guarda intento | `HacerEvaluacionTest::test_un_envio_incompleto_no_cuenta_como_intento` |
| Login | correo y contraseña vacíos | "El campo correo electrónico es obligatorio." / "El campo contraseña es obligatorio." | `AutenticacionTest::test_los_errores_de_validacion_estan_en_espanol` |
| Búsqueda del encargado | 1 carácter | "Escribe al menos 2 caracteres." y no lista a nadie | `ConsultaTest::test_la_busqueda_solo_lista_empleados_y_exige_dos_caracteres` |

### Longitud

| Campo | Entrada | Salida esperada | Test |
|---|---|---|---|
| Título del curso | 256 caracteres | "El campo título no puede tener más de 255 caracteres." | `CursoTest::test_el_titulo_no_puede_superar_255_caracteres` |
| Contraseña del registro | 7 caracteres (`1234567`) | "El campo contraseña debe tener al menos 8 caracteres.", sin errores en los demás campos; no se crea la cuenta | `AutenticacionTest::test_el_registro_exige_una_contrasena_de_al_menos_8_caracteres` |

**Hay un test de longitud máxima (título del curso) y uno de longitud mínima
(contraseña del registro).** Los formularios tienen otros límites en las reglas
de validación que **no** están cubiertos por ningún test: descripción del curso
(5000), título de lección y de evaluación (255), enunciado de pregunta (2000),
cada opción (255), orden de lección (65535) y nombre y correo del registro
(255).

### Valores duplicados

| Campo | Entrada | Salida esperada | Test |
|---|---|---|---|
| Correo del registro | un correo que ya tiene otra cuenta | "Ya existe un registro con ese correo electrónico.", sin errores en los demás campos; sigue habiendo una sola cuenta con ese correo y no se inicia sesión | `AutenticacionTest::test_el_registro_rechaza_un_email_ya_registrado` |
| Empleado a asignar | un empleado que ya tiene el curso | "Este empleado ya tiene el curso asignado." y sigue habiendo una sola asignación | `AsignacionTest::test_no_se_asigna_dos_veces_el_mismo_curso` |

### Caracteres especiales

| Caso | Entrada | Salida esperada | Test |
|---|---|---|---|
| HTML/JavaScript en el contenido de una lección | `<script>alert(1)</script>` | Se muestra escapado (`&lt;script&gt;…`) y nunca como etiqueta real | `ProgresoTest::test_el_contenido_de_la_leccion_se_muestra_escapado` |
| Comodines de SQL en la búsqueda del encargado | `%%` | No lista a nadie (no se interpreta como "cualquier texto") | `ConsultaTest::test_la_busqueda_solo_lista_empleados_y_exige_dos_caracteres` |
| Tildes en los datos | opciones como `Otra más` | Se guardan y se recuperan iguales | `EvaluacionAdminTest::test_editar_una_pregunta_reemplaza_sus_opciones` |
| Tildes y signos en la interfaz | `¿Has olvidado tu contraseña?`, `Contraseña` | Aparecen correctamente en la página | `AutenticacionTest::test_el_login_esta_en_espanol_y_usa_bootstrap_desde_cdn` |

No hay tests de otros caracteres (comillas, emojis, etc.); el escapado de toda
la salida lo hace Blade con `{{ }}`, que es lo que comprueba el test del
`<script>`.

### Máscaras

**El proyecto no tiene ningún campo con máscara** (no hay DNI, teléfono,
código postal ni similar), así que **no hay pruebas de máscaras**. El único
campo con formato es el correo electrónico del registro, que valida Laravel
con su regla `email`; su caso inválido está en la tabla de tipos de dato.

### Valores límite (cálculo de la nota)

| Entrada | Salida esperada | Test |
|---|---|---|
| Aciertos / preguntas | Nota = aciertos ÷ preguntas × 10 con 2 decimales | `HacerEvaluacionTest::test_la_nota_es_aciertos_entre_preguntas_por_10_con_2_decimales` |
| Nota exactamente igual a la mínima | Aprobado | `HacerEvaluacionTest::test_el_aprobado_en_el_limite_exacto_de_la_nota_minima` |
| 2 de 3 = 6,666… → 6,67, con mínima 6,67 y luego 6,68 | Aprobado y luego suspendido | `HacerEvaluacionTest::test_el_limite_con_decimales_periodicos` |
| Un 10 frente a un 9 | Solo el 10 da la insignia "Nota máxima" | `InsigniaTest::test_un_10_da_nota_maxima_y_un_9_no` |
| 4 cursos certificados y luego el 5.º | La insignia "5 cursos certificados" no aparece con 4 y sí con 5 | `InsigniaTest::test_cinco_certificados_en_el_quinto_curso` |

---

## 5. Pruebas por módulo

En cada tabla, la columna *Comprueba* resume la entrada y la salida esperada.
La sección 5.0 es de unidad; las secciones 5.1 a 5.6 son de integración (ver
el apartado 2).

### 5.0. Pruebas de unidad (20 tests, 23 aserciones)

Prueban directamente los métodos estáticos de `app/Support/`, sin Eloquent,
sin base de datos y sin HTTP (ver el apartado 2).

**`CalificadorTest`** (11 tests)

| Test | Comprueba |
|---|---|
| `test_nota_calcula_el_porcentaje_de_aciertos_sobre_diez` | `nota(4, 5)` → `8.0` |
| `test_nota_con_decimales_periodicos_redondea_a_dos_decimales` | `nota(1, 3)` → `3.33` (1/3 × 10 = 3,333…, redondeado) |
| `test_nota_sin_preguntas_es_cero` | `nota(0, 0)` → `0.0` |
| `test_nota_con_todo_fallado_es_cero` | `nota(0, 5)` → `0.0` |
| `test_nota_con_todo_acertado_es_diez` | `nota(5, 5)` → `10.0` |
| `test_aprueba_con_nota_igual_a_la_minima` | `aprueba(8.0, 8.0)` → `true` |
| `test_aprueba_falla_por_una_centesima` | `aprueba(7.99, 8.0)` → `false` |
| `test_aprueba_evita_el_error_de_coma_flotante_en_el_limite` | `aprueba(8.1 − 0.1, 8.0)` → `true` (8,1 − 0,1 da 7,999999999999999 en coma flotante; en céntimos enteros sigue siendo 8,00) |
| `test_progreso_sin_lecciones_es_cero` | `progreso(0, 0)` → `0` |
| `test_progreso_redondea_decimales_periodicos` | `progreso(1, 3)` → `33` (1/3 × 100 = 33,333…, redondeado) |
| `test_progreso_completo_es_cien` | `progreso(5, 5)` → `100` |

**`LogrosTest`** (9 tests) — todos llaman a `Logros::hitos(aprobado, nota, esPrimerIntento, certificadosTotales)`

| Test | Comprueba |
|---|---|
| `test_primer_intento_aprobado_concede_la_insignia_a_la_primera` | `(true, 7.0, true, 1)` → incluye `PRIMER_INTENTO` |
| `test_no_concede_a_la_primera_si_hubo_intentos_previos` | `(true, 7.0, false, 1)` → no incluye `PRIMER_INTENTO` |
| `test_nota_maxima_solo_se_concede_con_un_diez_exacto` | `(true, 9.99, false, 0)` → no incluye `NOTA_MAXIMA` |
| `test_nota_maxima_se_concede_con_un_diez` | `(true, 10.0, false, 1)` → incluye `NOTA_MAXIMA` |
| `test_primer_certificado_se_concede_con_un_certificado` | `(true, 7.0, false, 1)` → incluye `PRIMER_CERTIFICADO`, no incluye `CINCO_CERTIFICADOS` |
| `test_cinco_certificados_se_concede_a_partir_del_quinto` | `(true, 7.0, false, 5)` → incluye `PRIMER_CERTIFICADO` y `CINCO_CERTIFICADOS` |
| `test_cuatro_certificados_no_concede_la_insignia_de_cinco` | `(true, 7.0, false, 4)` → no incluye `CINCO_CERTIFICADOS` |
| `test_sin_certificados_no_concede_ninguna_insignia_de_certificados` | `(false, 5.0, false, 0)` → no incluye `PRIMER_CERTIFICADO` ni `CINCO_CERTIFICADOS` |
| `test_suspender_sin_certificados_previos_no_concede_ninguna_insignia` | `(false, 5.0, false, 0)` → `[]` (lista vacía) |

### 5.1. Cursos y lecciones (29 tests, 132 aserciones)

**`CursoTest`** (20 tests)

| Test | Comprueba |
|---|---|
| `test_el_administrador_ve_el_listado_de_cursos` | El administrador abre `/cursos` y ve los cursos |
| `test_el_listado_esta_paginado_de_10_en_10` | Con 12 cursos, la página 1 muestra 10 y la 2 los otros 2 |
| `test_el_listado_muestra_cuantas_lecciones_tiene_cada_curso` | Columna de número de lecciones |
| `test_el_administrador_crea_un_curso` | Alta correcta: se guarda y redirige |
| `test_la_descripcion_es_opcional` | Sin descripción también se crea |
| `test_el_titulo_es_obligatorio_con_mensaje_en_espanol` | Título vacío → error en español, no se crea |
| `test_el_titulo_no_puede_superar_255_caracteres` | 256 caracteres → error de longitud |
| `test_el_administrador_edita_un_curso` | Edición correcta |
| `test_editar_valida_el_titulo` | La edición también valida el título |
| `test_la_confirmacion_avisa_de_las_lecciones_que_se_borraran` | Aviso "Se borrarán también sus 2 lecciones." |
| `test_la_confirmacion_usa_singular_con_una_leccion` | Aviso en singular con 1 lección |
| `test_la_confirmacion_con_evaluacion_sin_preguntas` | Aviso "(sin preguntas)" |
| `test_la_confirmacion_ofrece_el_borrado_si_no_hay_datos_de_empleados` | Sin datos de empleados aparece el botón de borrar |
| `test_la_confirmacion_avisa_antes_si_hay_datos_de_empleados` | Con asignaciones, progreso, intentos o certificados: aviso de bloqueo y sin botón |
| `test_el_administrador_elimina_un_curso_y_sus_lecciones_en_cascada` | Borrado en cascada de las lecciones |
| `test_no_se_elimina_un_curso_con_datos_de_empleados` | Curso con un empleado asignado: el borrado devuelve un error y el curso sigue existiendo. El borrado con intentos y con certificados se prueba en `EvaluacionAdminTest` y `CertificadoTest`; el de un curso con solo progreso de lecciones no tiene test de borrado (sí de aviso, en `test_la_confirmacion_avisa_antes_si_hay_datos_de_empleados`) |
| `test_un_empleado_no_puede_acceder_a_ninguna_ruta_de_cursos` | Empleado → 403 en todas las rutas |
| `test_un_encargado_tampoco_puede_acceder` | Encargado → 403 |
| `test_un_invitado_es_enviado_al_login` | Sin sesión → redirección al login |
| `test_solo_el_administrador_ve_el_enlace_cursos_en_el_menu` | Menú según el rol |

**`LeccionTest`** (9 tests)

| Test | Comprueba |
|---|---|
| `test_el_listado_muestra_las_lecciones_del_curso_por_orden` | Orden por número de orden |
| `test_el_formulario_propone_el_siguiente_orden` | Sin lecciones propone 1; con una lección de orden 5 propone 6 (el más alto + 1) |
| `test_el_administrador_crea_una_leccion_dentro_del_curso` | Alta correcta en su curso |
| `test_la_validacion_de_lecciones_esta_en_espanol` | Vacíos, orden 0 y orden `abc` → errores en español |
| `test_el_administrador_edita_una_leccion` | Edición correcta |
| `test_el_administrador_elimina_una_leccion` | Borrado correcto |
| `test_no_se_elimina_una_leccion_con_progreso_de_empleados` | Con progreso no se borra |
| `test_una_leccion_no_se_puede_manejar_desde_la_url_de_otro_curso` | Editar o borrar una lección con la URL de otro curso → 404 y la lección queda intacta |
| `test_un_empleado_no_puede_acceder_a_las_lecciones` | Empleado → 403 en la administración de lecciones |

### 5.2. Asignación y progreso (22 tests, 88 aserciones)

**`AsignacionTest`** (9 tests)

| Test | Comprueba |
|---|---|
| `test_el_selector_solo_ofrece_empleados_sin_el_curso` | El desplegable excluye a quien ya lo tiene |
| `test_el_administrador_asigna_un_curso` | Asignación con fecha |
| `test_no_se_asigna_dos_veces_el_mismo_curso` | Duplicado → error, una sola fila |
| `test_solo_se_asigna_a_empleados` | Encargado, administrador, id inexistente o vacío → error |
| `test_el_administrador_desasigna_un_curso_sin_progreso` | Desasignar sin progreso funciona |
| `test_no_se_desasigna_si_el_empleado_tiene_progreso` | Con progreso se bloquea |
| `test_el_progreso_en_otro_curso_no_bloquea` | Solo cuenta el progreso de ese curso |
| `test_desasignar_a_quien_no_lo_tiene_da_404` | Desasignación inexistente → 404 |
| `test_empleado_y_encargado_no_acceden_a_las_asignaciones` | 403 para otros roles |

**`ProgresoTest`** (13 tests)

| Test | Comprueba |
|---|---|
| `test_mis_cursos_solo_muestra_los_asignados` | El empleado solo ve sus cursos |
| `test_el_detalle_muestra_las_lecciones_ordenadas` | Lecciones en orden |
| `test_completar_una_leccion_crea_el_progreso_con_fecha` | Marcar como completada guarda la fecha |
| `test_completar_dos_veces_no_duplica_ni_cambia_la_fecha` | Idempotente |
| `test_el_porcentaje_es_completadas_entre_totales` | Cálculo del porcentaje |
| `test_el_progreso_de_otros_empleados_y_cursos_no_cuenta` | El porcentaje no mezcla datos ajenos |
| `test_un_curso_sin_lecciones_esta_al_0_por_ciento` | Sin lecciones → 0 % |
| `test_un_empleado_sin_asignacion_no_ve_el_curso_ni_sus_lecciones` | Sin asignación → 403 |
| `test_un_empleado_no_puede_completar_una_leccion_ajena` | Lección de un curso no asignado → 403 |
| `test_al_desasignar_se_pierde_el_acceso` | Tras desasignar → 403 |
| `test_administrador_encargado_e_invitado_no_usan_mis_cursos` | Otros roles e invitado no entran |
| `test_solo_el_empleado_ve_mis_cursos_en_el_menu` | Menú según el rol |
| `test_el_contenido_de_la_leccion_se_muestra_escapado` | `<script>` se muestra escapado |

### 5.3. Evaluaciones (34 tests, 182 aserciones)

**`EvaluacionAdminTest`** (19 tests): parte del administrador

| Test | Comprueba |
|---|---|
| `test_el_formulario_propone_titulo_y_nota_minima_8` | Título "Evaluación: …" y nota mínima 8 propuestos |
| `test_el_administrador_crea_la_evaluacion` | Alta correcta |
| `test_la_nota_minima_se_valida` | `11`, `8.555`, `abc` y vacío → errores en español |
| `test_solo_hay_una_evaluacion_por_curso` | No se crea una segunda |
| `test_el_administrador_edita_la_nota_minima` | Edición correcta |
| `test_editar_o_borrar_sin_evaluacion_da_404` | Sin evaluación → 404 |
| `test_eliminar_la_evaluacion_borra_sus_preguntas_y_opciones` | Cascada |
| `test_no_se_elimina_una_evaluacion_con_resultados` | Con intentos no se borra |
| `test_el_administrador_crea_una_pregunta_con_sus_opciones` | Alta de pregunta con opciones |
| `test_una_pregunta_necesita_al_menos_dos_opciones` | Menos de 2 → error |
| `test_una_pregunta_necesita_exactamente_una_correcta` | Ninguna correcta, correcta vacía o dos marcadas → error |
| `test_el_formulario_no_falla_al_volver_de_una_peticion_manipulada` | Sin 500 tras una petición manipulada |
| `test_editar_una_pregunta_reemplaza_sus_opciones` | Las opciones vacías se descartan y se respetan las tildes |
| `test_el_administrador_elimina_una_pregunta` | Borrado de pregunta |
| `test_una_pregunta_no_se_maneja_desde_otro_curso` | URL cruzada → 404 |
| `test_empleado_y_encargado_reciben_403_en_la_administracion` | 403 en todas las rutas |
| `test_el_aviso_de_borrar_curso_menciona_la_evaluacion_y_sus_preguntas` | Aviso de cascada |
| `test_borrar_un_curso_sin_resultados_borra_su_evaluacion` | Cascada curso → evaluación |
| `test_borrar_un_curso_con_resultados_falla_limpiamente` | Mensaje en lugar de error 500 |

**`HacerEvaluacionTest`** (15 tests): parte del empleado

| Test | Comprueba |
|---|---|
| `test_con_lecciones_pendientes_la_evaluacion_esta_bloqueada` | Aviso de bloqueo; forzar la URL o enviar respuestas → 403 y 0 intentos |
| `test_al_completar_las_lecciones_se_desbloquea` | Disponible al 100 % |
| `test_sin_evaluacion_o_sin_preguntas_no_se_ofrece_y_da_403` | No se ofrece |
| `test_un_curso_sin_lecciones_no_ofrece_la_evaluacion` | Mensaje "…cuando el curso tenga lecciones." |
| `test_sin_asignacion_o_con_otro_rol_da_403` | Control de acceso |
| `test_la_nota_es_aciertos_entre_preguntas_por_10_con_2_decimales` | Cálculo de la nota |
| `test_el_aprobado_en_el_limite_exacto_de_la_nota_minima` | Nota = mínima → aprobado |
| `test_el_limite_con_decimales_periodicos` | 6,67 frente a 6,68 |
| `test_cambiar_la_nota_minima_no_altera_intentos_pasados` | El aprobado se guarda por intento |
| `test_una_opcion_de_otra_pregunta_cuenta_como_fallo` | Respuesta cruzada → fallo |
| `test_un_envio_incompleto_no_cuenta_como_intento` | Error y 0 intentos guardados |
| `test_intentos_ilimitados_cada_uno_es_una_fila` | Cada intento, una fila |
| `test_el_resultado_muestra_nota_estado_y_preguntas_falladas_sin_la_correcta` | Pantalla de resultado |
| `test_sin_intentos_el_resultado_da_404` | Resultado sin intentos → 404 |
| `test_es_correcta_no_aparece_en_la_respuesta_al_empleado` | La respuesta correcta nunca llega al navegador |

### 5.4. Certificados e insignias (19 tests, 56 aserciones)

**`CertificadoTest`** (12 tests)

| Test | Comprueba |
|---|---|
| `test_aprobar_emite_el_certificado_con_la_fecha_de_hoy` | Emisión automática con la fecha |
| `test_suspender_no_emite_certificado` | Suspenso → sin certificado |
| `test_solo_se_emite_con_el_primer_aprobado` | Un segundo aprobado no duplica ni cambia la fecha |
| `test_un_curso_sin_evaluacion_no_certifica` | Sin evaluación → sin certificado |
| `test_el_certificado_se_ve_en_el_curso_y_en_mis_cursos` | Texto "Curso certificado el 29/09/2026." en el curso y etiqueta *Certificado* en Mis cursos |
| `test_el_empleado_descarga_su_certificado_en_pdf` | Respuesta PDF |
| `test_el_pdf_contiene_nombre_curso_fecha_y_codigo` | La plantilla HTML del certificado (la que DomPDF convierte en PDF) contiene nombre, curso, fecha y el código del certificado. No analiza el PDF generado ni comprueba el formato literal `FX-000001` |
| `test_quien_puede_descargar_un_certificado` | Otro empleado y encargado → 403; administrador → 200; invitado → login |
| `test_mis_logros_muestra_solo_los_certificados_propios` | No muestra certificados ajenos |
| `test_mis_logros_solo_para_empleados` | Otros roles → 403 |
| `test_el_administrador_ve_el_certificado_en_las_asignaciones` | Columna Certificado |
| `test_no_se_borra_un_curso_con_certificados` | Bloqueo de borrado |

**`InsigniaTest`** (7 tests)

| Test | Comprueba |
|---|---|
| `test_aprobar_al_primer_intento_da_primer_intento_y_primer_certificado` | Dos insignias a la vez |
| `test_aprobar_despues_de_suspender_no_da_primer_intento` | Sin "A la primera" |
| `test_un_10_da_nota_maxima_y_un_9_no` | Límite de "Nota máxima" |
| `test_cinco_certificados_en_el_quinto_curso` | No con 4 certificados; sí con el 5.º, y se anuncia |
| `test_repetir_un_hito_no_duplica_ni_vuelve_a_anunciarlo` | Idempotencia |
| `test_la_base_de_datos_impide_duplicar_una_insignia` | Índice único en la base de datos |
| `test_mis_logros_muestra_conseguidas_y_pendientes` | Pantalla de logros |

### 5.5. Consulta del encargado y registro RGPD (15 tests, 90 aserciones)

**`ConsultaTest`** (15 tests)

| Test | Comprueba |
|---|---|
| `test_el_encargado_busca_empleados_por_nombre` | Búsqueda por nombre |
| `test_la_busqueda_solo_lista_empleados_y_exige_dos_caracteres` | Mínimo 2 caracteres; no lista otros roles; `%%` no lista a nadie |
| `test_la_busqueda_no_registra_consultas` | Buscar no crea filas en el registro |
| `test_el_encargado_ve_la_ficha_completa` | Cursos, progreso, certificados e insignias |
| `test_la_ficha_no_tiene_nada_editable_ni_el_pdf` | Sin formularios ni enlace al PDF |
| `test_la_busqueda_no_tiene_nada_editable` | Sin formularios de edición |
| `test_solo_se_consultan_fichas_de_empleados` | Ficha de un no empleado → 404 |
| `test_el_encargado_no_accede_a_rutas_de_edicion_ni_al_pdf` | 403 en 11 rutas concretas: listado, alta, edición y borrado de cursos, asignaciones, evaluación, PDF del certificado y registro de consultas |
| `test_el_administrador_tambien_puede_consultar` | El administrador también accede |
| `test_cada_consulta_a_una_ficha_crea_una_fila` | Registro de quién, a quién y cuándo |
| `test_solo_el_administrador_ve_la_pantalla_del_registro` | Encargado y empleado → 403 |
| `test_el_registro_esta_paginado_y_ordenado_del_mas_reciente` | 25 por página, más reciente primero |
| `test_el_registro_no_se_puede_editar_ni_borrar_desde_la_interfaz` | No hay rutas de edición ni de borrado |
| `test_un_empleado_no_accede_a_nada_del_modulo` | 403 |
| `test_los_enlaces_del_menu_segun_el_rol` | Menú según el rol |

### 5.6. Pruebas transversales (22 tests, 127 aserciones)

No pertenecen a un módulo concreto, pero cubren el acceso y la robustez de toda
la aplicación.

**`AutenticacionTest`** (12 tests)

| Test | Comprueba |
|---|---|
| `test_el_login_esta_en_espanol_y_usa_bootstrap_desde_cdn` | Textos en español y Bootstrap desde CDN (sin Vite) |
| `test_los_errores_de_validacion_estan_en_espanol` | Campos vacíos → mensajes en español |
| `test_credenciales_incorrectas_muestran_mensaje_en_espanol` | Credenciales incorrectas → mensaje en español |
| `test_el_seeder_crea_un_unico_administrador_aunque_se_ejecute_dos_veces` | Seeder idempotente |
| `test_el_administrador_de_prueba_puede_iniciar_sesion` | `admin@formaexpress.test` entra |
| `test_el_seeder_de_demostracion_crea_un_empleado_y_un_encargado` | Seeder de demostración idempotente y con los roles correctos |
| `test_los_usuarios_de_demostracion_inician_sesion_con_su_perfil` | Cada uno ve su menú |
| `test_db_seed_por_defecto_no_crea_los_usuarios_de_demostracion` | `db:seed` solo crea el administrador |
| `test_el_registro_crea_empleados_aunque_se_envie_otro_rol` | Enviar `rol=administrador` al registrarse → se crea un empleado |
| `test_el_registro_rechaza_un_email_con_formato_invalido` | Correo sin formato válido → error en el correo, sin cuenta ni sesión |
| `test_el_registro_exige_una_contrasena_de_al_menos_8_caracteres` | Contraseña de 7 caracteres → error en la contraseña, sin cuenta ni sesión |
| `test_el_registro_rechaza_un_email_ya_registrado` | Correo ya usado → error en el correo; sigue habiendo una sola cuenta con él |

**`EntradaManipuladaTest`** (6 tests)

| Test | Comprueba |
|---|---|
| `test_formularios_del_administrador` | Arrays en los campos de texto de todos los formularios del administrador, incluidos crear evaluación y editar pregunta → error y sin 500 |
| `test_formularios_de_login_y_registro` | Lo mismo en login y registro |
| `test_formularios_de_recuperacion_de_contrasena` | Lo mismo en `/password/email`, `/password/reset` y `/password/confirm`. El caso de `/password/confirm` encontró un `TypeError` real (500): `password` manipulado como array llegaba a `password_verify()`. Ya corregido en `ConfirmPasswordController` (commit `0968142`) |
| `test_formulario_de_la_evaluacion_del_empleado` | Lo mismo en la evaluación |
| `test_que_se_guarda_para_old_al_volver` | Solo se recuerda lo válido; nunca contraseñas |
| `test_los_valores_normales_se_conservan_al_volver` | Los valores correctos se mantienen al volver con un error |

**`Feature/ExampleTest`** (3 tests)

| Test | Comprueba |
|---|---|
| `test_la_raiz_redirige_a_home` | `/` → `/home` |
| `test_home_exige_sesion` | Sin sesión → login |
| `test_la_pantalla_de_login_carga` | `/login` responde 200 |

**`Unit/ExampleTest`** (1 test): `test_that_true_is_true`. Es el ejemplo de
Laravel y no prueba nada del proyecto (ver el apartado 2).

---

## 6. Limitaciones de la batería

- La cobertura de unidad se limita a `Calificador` y `Logros::hitos()`; el
  resto de la aplicación (incluida `EntradaAntigua`, ya aislada pero sin test
  de unidad propio) solo tiene cobertura de integración.
- Las pruebas usan SQLite, no MySQL. Las restricciones de clave foránea y los
  índices únicos se comprueban en SQLite.
- De las longitudes máximas, solo se prueba la del título del curso.
- No hay campos con máscara ni, por tanto, pruebas de máscaras.
- No hay pruebas en navegador, visuales ni de rendimiento.
