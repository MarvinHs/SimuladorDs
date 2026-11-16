<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-FRGMJH2ZE5"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-FRGMJH2ZE5');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Practica tu entrevista de visa B1/B2 con un cónsul simulado por IA, basado en tu perfil DS-160.">
    <meta name="theme-color" content="#1e3a8a">
    <title>Simulador Consular DS-160 | Perfil Personalizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=5">
</head>
<body>
<div class="container py-5 app-container">
    <header class="hero text-center mb-4">
        <span class="hero-badge">Visa B1/B2 · Práctica con IA</span>
        <h1 class="hero-title">🏛️ Simulador de Entrevista Consular</h1>
        <p class="hero-subtitle">Completa tu perfil DS-160, practica la entrevista por texto o por voz y recibe un informe con tus puntos fuertes, débiles y recomendaciones.</p>
        <ol class="stepper" aria-label="Progreso">
            <li id="stepper-1" class="active"><span>1</span> Perfil</li>
            <li id="stepper-2"><span>2</span> Entrevista</li>
            <li id="stepper-3"><span>3</span> Resultado</li>
        </ol>
    </header>

    <div id="config-warning" class="alert alert-warning d-none" role="alert">
        <strong>⚠️ Falta configurar la IA.</strong> El servidor no tiene ninguna API key. Crea el archivo <code>.env</code> a partir de <code>.env.example</code> y coloca al menos <code>GEMINI_API_KEY</code> o <code>GROQ_API_KEY</code>.
    </div>

    <!-- PASO 1: FORMULARIO DE PERFIL -->
    <div id="step-upload" class="card p-4 mb-4 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4 border-bottom pb-2">
                <h4 class="card-title text-primary mb-0">📋 Cuestionario de Perfil DS-160</h4>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" id="btn-example" class="btn btn-sm btn-outline-primary" title="Llena el formulario con un perfil ficticio para probar">✨ Usar perfil de ejemplo</button>
                    <button type="button" id="btn-clear" class="btn btn-sm btn-outline-secondary">🧹 Limpiar</button>
                </div>
            </div>

            <form id="upload-form">
                <h5 class="text-secondary mt-4 mb-3">1. Información personal</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label fw-semibold">Nombre y apellidos completos</label>
                        <input type="text" id="nombre" name="nombre" class="form-control" placeholder="Nombre y apellido" autocomplete="name" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estado_civil" class="form-label fw-semibold">Estado civil</label>
                        <select id="estado_civil" name="estado_civil" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Soltero/a">Soltero/a</option>
                            <option value="Casado/a">Casado/a</option>
                            <option value="Unión libre">Unión libre</option>
                            <option value="Divorciado/a">Divorciado/a</option>
                            <option value="Viudo/a">Viudo/a</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hijos" class="form-label fw-semibold">¿Tiene hijos?</label>
                        <select id="hijos" name="hijos" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                </div>

                <h5 class="text-secondary mt-4 mb-3">2. Información de los padres</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre_padre" class="form-label fw-semibold">Nombre completo del padre</label>
                        <input type="text" id="nombre_padre" name="nombre_padre" class="form-control" placeholder="Ej. Juan Pérez">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="padre_eeuu" class="form-label fw-semibold">¿El padre se encuentra en EE. UU.?</label>
                        <select id="padre_eeuu" name="padre_eeuu" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                            <option value="No sé">No sé</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="nombre_madre" class="form-label fw-semibold">Nombre completo de la madre</label>
                        <input type="text" id="nombre_madre" name="nombre_madre" class="form-control" placeholder="Ej. María Gómez">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="madre_eeuu" class="form-label fw-semibold">¿La madre se encuentra en EE. UU.?</label>
                        <select id="madre_eeuu" name="madre_eeuu" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                            <option value="No sé">No sé</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="residencia_actual" class="form-label fw-semibold">País de residencia actual</label>
                        <input type="text" id="residencia_actual" name="residencia_actual" class="form-control" placeholder="Ej. República Dominicana">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="familiares_eeuu" class="form-label fw-semibold">¿Tiene familiares directos en EE. UU.?</label>
                        <select id="familiares_eeuu" name="familiares_eeuu" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3" data-show-when="familiares_eeuu=Sí">
                        <label for="detalle_familiares_eeuu" class="form-label fw-semibold">Si respondió sí, indique parentesco y situación migratoria si la conoce</label>
                        <input type="text" id="detalle_familiares_eeuu" name="detalle_familiares_eeuu" class="form-control" placeholder="Ej. Hermana, residente permanente">
                    </div>
                </div>

                <h5 class="text-secondary mt-4 mb-3">3. Trabajo, estudios e ingresos</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ocupacion" class="form-label fw-semibold">Ocupación / cargo actual</label>
                        <input type="text" id="ocupacion" name="ocupacion" class="form-control" placeholder="Cargo actual" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="empresa" class="form-label fw-semibold">Nombre de la empresa / institución</label>
                        <input type="text" id="empresa" name="empresa" class="form-control" placeholder="Empresa" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="antiguedad" class="form-label fw-semibold">Fecha de inicio en el empleo</label>
                        <input type="date" id="antiguedad" name="antiguedad" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ingresos" class="form-label fw-semibold">Ingreso mensual aproximado</label>
                        <input type="text" id="ingresos" name="ingresos" class="form-control" placeholder="Ej. RD$60,000 o US$1,000" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="otros_ingresos" class="form-label fw-semibold">Otros ingresos / actividad independiente</label>
                        <input type="text" id="otros_ingresos" name="otros_ingresos" class="form-control" placeholder="Monto y origen, si aplica">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="estudios_actuales" class="form-label fw-semibold">¿Estudia actualmente?</label>
                        <select id="estudios_actuales" name="estudios_actuales" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" data-show-when="estudios_actuales=Sí">
                        <label for="detalle_estudios" class="form-label fw-semibold">Institución, programa y horario (si aplica)</label>
                        <input type="text" id="detalle_estudios" name="detalle_estudios" class="form-control" placeholder="Ej. Instituto, carrera, horario">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="vacaciones_autorizadas" class="form-label fw-semibold">¿Tiene vacaciones autorizadas?</label>
                        <select id="vacaciones_autorizadas" name="vacaciones_autorizadas" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="No aplica">No aplica</option>
                            <option value="Sí">Sí</option>
                            <option value="No">No</option>
                            <option value="Pendiente">Pendiente de aprobación</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" data-show-when="vacaciones_autorizadas=Sí|Pendiente">
                        <label for="detalle_vacaciones" class="form-label fw-semibold">Fechas o días de vacaciones disponibles</label>
                        <input type="text" id="detalle_vacaciones" name="detalle_vacaciones" class="form-control" placeholder="Ej. Del 1 al 10 de agosto">
                    </div>
                </div>

                <h5 class="text-secondary mt-4 mb-3">4. Detalles del viaje a EE. UU.</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="motivo_viaje" class="form-label fw-semibold">Propósito del viaje</label>
                        <input type="text" id="motivo_viaje" name="motivo_viaje" class="form-control" placeholder="Ej. Turismo, vacaciones" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="destino" class="form-label fw-semibold">Ciudad y estado de destino</label>
                        <input type="text" id="destino" name="destino" class="form-control" placeholder="Ej. Houston, Texas" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="tiempo_estadia" class="form-label fw-semibold">Duración estimada</label>
                        <input type="text" id="tiempo_estadia" name="tiempo_estadia" class="form-control" placeholder="Ej. 10 días" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_viaje" class="form-label fw-semibold">Fecha tentativa de salida</label>
                        <input type="date" id="fecha_viaje" name="fecha_viaje" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_regreso" class="form-label fw-semibold">Fecha tentativa de regreso</label>
                        <input type="date" id="fecha_regreso" name="fecha_regreso" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="financiacion" class="form-label fw-semibold">¿Quién paga los gastos?</label>
                        <input type="text" id="financiacion" name="financiacion" class="form-control" placeholder="Ej. Ahorros propios, salario" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="presupuesto" class="form-label fw-semibold">Presupuesto total aproximado</label>
                        <input type="text" id="presupuesto" name="presupuesto" class="form-control" placeholder="Ej. US$2,500">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ahorros" class="form-label fw-semibold">Ahorros disponibles para el viaje</label>
                        <input type="text" id="ahorros" name="ahorros" class="form-control" placeholder="Monto y moneda, si desea indicarlo">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="hospedaje" class="form-label fw-semibold">¿Dónde se hospedará?</label>
                        <input type="text" id="hospedaje" name="hospedaje" class="form-control" placeholder="Hotel, domicilio de familiar/amigo, etc.">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="invitante" class="form-label fw-semibold">¿Alguien lo invita o recibirá en EE. UU.?</label>
                        <input type="text" id="invitante" name="invitante" class="form-control" placeholder="No aplica o nombre/relación">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="motivo_regreso" class="form-label fw-semibold">¿Qué compromisos tiene al regresar?</label>
                        <input type="text" id="motivo_regreso" name="motivo_regreso" class="form-control" placeholder="Trabajo, familia, estudios u otros">
                    </div>
                </div>

                <h5 class="text-secondary mt-4 mb-3">5. Acompañantes e historial de viajes</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="acompanantes" class="form-label fw-semibold">¿Viaja solo o acompañado?</label>
                        <input type="text" id="acompanantes" name="acompanantes" class="form-control" placeholder="Ej. Viajo solo o con mi familia" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="viajes_eeuu" class="form-label fw-semibold">¿Ha viajado anteriormente a EE. UU.?</label>
                        <select id="viajes_eeuu" name="viajes_eeuu" class="form-select">
                            <option value="">Seleccione...</option>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3" data-show-when="viajes_eeuu=Sí">
                        <label for="detalle_viajes_eeuu" class="form-label fw-semibold">Si ha viajado, indique fechas aproximadas y duración de las estadías</label>
                        <textarea id="detalle_viajes_eeuu" name="detalle_viajes_eeuu" class="form-control" rows="2" placeholder="Incluya entradas y salidas si las recuerda"></textarea>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="paises_visitados" class="form-label fw-semibold">Otros países visitados en los últimos 5 años</label>
                        <input type="search" id="filtro_paises" class="form-control form-control-sm mb-2" placeholder="🔎 Buscar país..." aria-label="Buscar país" autocomplete="off">
                        <select id="paises_visitados" name="paises_visitados[]" class="form-select" multiple size="6" aria-describedby="ayuda-paises">
                        <option value="Afganistán">Afganistán</option>
                        <option value="Albania">Albania</option>
                        <option value="Alemania">Alemania</option>
                        <option value="Andorra">Andorra</option>
                        <option value="Angola">Angola</option>
                        <option value="Antigua y Barbuda">Antigua y Barbuda</option>
                        <option value="Arabia Saudita">Arabia Saudita</option>
                        <option value="Argelia">Argelia</option>
                        <option value="Argentina">Argentina</option>
                        <option value="Armenia">Armenia</option>
                        <option value="Australia">Australia</option>
                        <option value="Austria">Austria</option>
                        <option value="Azerbaiyán">Azerbaiyán</option>
                        <option value="Bahamas">Bahamas</option>
                        <option value="Bangladés">Bangladés</option>
                        <option value="Barbados">Barbados</option>
                        <option value="Baréin">Baréin</option>
                        <option value="Bélgica">Bélgica</option>
                        <option value="Belice">Belice</option>
                        <option value="Benín">Benín</option>
                        <option value="Bielorrusia">Bielorrusia</option>
                        <option value="Birmania (Myanmar)">Birmania (Myanmar)</option>
                        <option value="Bolivia">Bolivia</option>
                        <option value="Bosnia y Herzegovina">Bosnia y Herzegovina</option>
                        <option value="Botsuana">Botsuana</option>
                        <option value="Brasil">Brasil</option>
                        <option value="Brunéi">Brunéi</option>
                        <option value="Bulgaria">Bulgaria</option>
                        <option value="Burkina Faso">Burkina Faso</option>
                        <option value="Burundi">Burundi</option>
                        <option value="Bután">Bután</option>
                        <option value="Cabo Verde">Cabo Verde</option>
                        <option value="Camboya">Camboya</option>
                        <option value="Camerún">Camerún</option>
                        <option value="Canadá">Canadá</option>
                        <option value="Catar">Catar</option>
                        <option value="Chad">Chad</option>
                        <option value="Chile">Chile</option>
                        <option value="China">China</option>
                        <option value="Chipre">Chipre</option>
                        <option value="Ciudad del Vaticano">Ciudad del Vaticano</option>
                        <option value="Colombia">Colombia</option>
                        <option value="Comoras">Comoras</option>
                        <option value="Corea del Norte">Corea del Norte</option>
                        <option value="Corea del Sur">Corea del Sur</option>
                        <option value="Costa de Marfil">Costa de Marfil</option>
                        <option value="Costa Rica">Costa Rica</option>
                        <option value="Croacia">Croacia</option>
                        <option value="Cuba">Cuba</option>
                        <option value="Dinamarca">Dinamarca</option>
                        <option value="Dominica">Dominica</option>
                        <option value="Ecuador">Ecuador</option>
                        <option value="Egipto">Egipto</option>
                        <option value="El Salvador">El Salvador</option>
                        <option value="Emiratos Árabes Unidos">Emiratos Árabes Unidos</option>
                        <option value="Eritrea">Eritrea</option>
                        <option value="Eslovaquia">Eslovaquia</option>
                        <option value="Eslovenia">Eslovenia</option>
                        <option value="España">España</option>
                        <option value="Estados Unidos">Estados Unidos</option>
                        <option value="Estonia">Estonia</option>
                        <option value="Esuatini">Esuatini</option>
                        <option value="Etiopía">Etiopía</option>
                        <option value="Filipinas">Filipinas</option>
                        <option value="Finlandia">Finlandia</option>
                        <option value="Fiyi">Fiyi</option>
                        <option value="Francia">Francia</option>
                        <option value="Gabón">Gabón</option>
                        <option value="Gambia">Gambia</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Ghana">Ghana</option>
                        <option value="Granada">Granada</option>
                        <option value="Grecia">Grecia</option>
                        <option value="Guatemala">Guatemala</option>
                        <option value="Guinea">Guinea</option>
                        <option value="Guinea-Bisáu">Guinea-Bisáu</option>
                        <option value="Guinea Ecuatorial">Guinea Ecuatorial</option>
                        <option value="Guyana">Guyana</option>
                        <option value="Haití">Haití</option>
                        <option value="Honduras">Honduras</option>
                        <option value="Hungría">Hungría</option>
                        <option value="India">India</option>
                        <option value="Indonesia">Indonesia</option>
                        <option value="Irak">Irak</option>
                        <option value="Irán">Irán</option>
                        <option value="Irlanda">Irlanda</option>
                        <option value="Islandia">Islandia</option>
                        <option value="Islas Marshall">Islas Marshall</option>
                        <option value="Islas Salomón">Islas Salomón</option>
                        <option value="Israel">Israel</option>
                        <option value="Italia">Italia</option>
                        <option value="Jamaica">Jamaica</option>
                        <option value="Japón">Japón</option>
                        <option value="Jordania">Jordania</option>
                        <option value="Kazajistán">Kazajistán</option>
                        <option value="Kenia">Kenia</option>
                        <option value="Kirguistán">Kirguistán</option>
                        <option value="Kiribati">Kiribati</option>
                        <option value="Kuwait">Kuwait</option>
                        <option value="Laos">Laos</option>
                        <option value="Lesoto">Lesoto</option>
                        <option value="Letonia">Letonia</option>
                        <option value="Líbano">Líbano</option>
                        <option value="Liberia">Liberia</option>
                        <option value="Libia">Libia</option>
                        <option value="Liechtenstein">Liechtenstein</option>
                        <option value="Lituania">Lituania</option>
                        <option value="Luxemburgo">Luxemburgo</option>
                        <option value="Macedonia del Norte">Macedonia del Norte</option>
                        <option value="Madagascar">Madagascar</option>
                        <option value="Malasia">Malasia</option>
                        <option value="Malaui">Malaui</option>
                        <option value="Maldivas">Maldivas</option>
                        <option value="Malí">Malí</option>
                        <option value="Malta">Malta</option>
                        <option value="Marruecos">Marruecos</option>
                        <option value="Mauricio">Mauricio</option>
                        <option value="Mauritania">Mauritania</option>
                        <option value="México">México</option>
                        <option value="Micronesia">Micronesia</option>
                        <option value="Moldavia">Moldavia</option>
                        <option value="Mónaco">Mónaco</option>
                        <option value="Mongolia">Mongolia</option>
                        <option value="Montenegro">Montenegro</option>
                        <option value="Mozambique">Mozambique</option>
                        <option value="Namibia">Namibia</option>
                        <option value="Nauru">Nauru</option>
                        <option value="Nepal">Nepal</option>
                        <option value="Nicaragua">Nicaragua</option>
                        <option value="Níger">Níger</option>
                        <option value="Nigeria">Nigeria</option>
                        <option value="Noruega">Noruega</option>
                        <option value="Nueva Zelanda">Nueva Zelanda</option>
                        <option value="Omán">Omán</option>
                        <option value="Países Bajos">Países Bajos</option>
                        <option value="Pakistán">Pakistán</option>
                        <option value="Palaos">Palaos</option>
                        <option value="Palestina">Palestina</option>
                        <option value="Panamá">Panamá</option>
                        <option value="Papúa Nueva Guinea">Papúa Nueva Guinea</option>
                        <option value="Paraguay">Paraguay</option>
                        <option value="Perú">Perú</option>
                        <option value="Polonia">Polonia</option>
                        <option value="Portugal">Portugal</option>
                        <option value="Reino Unido">Reino Unido</option>
                        <option value="República Centroafricana">República Centroafricana</option>
                        <option value="República Checa">República Checa</option>
                        <option value="República del Congo">República del Congo</option>
                        <option value="República Democrática del Congo">República Democrática del Congo</option>
                        <option value="República Dominicana">República Dominicana</option>
                        <option value="Ruanda">Ruanda</option>
                        <option value="Rumanía">Rumanía</option>
                        <option value="Rusia">Rusia</option>
                        <option value="Samoa">Samoa</option>
                        <option value="San Cristóbal y Nieves">San Cristóbal y Nieves</option>
                        <option value="San Marino">San Marino</option>
                        <option value="San Vicente y las Granadinas">San Vicente y las Granadinas</option>
                        <option value="Santa Lucía">Santa Lucía</option>
                        <option value="Santo Tomé y Príncipe">Santo Tomé y Príncipe</option>
                        <option value="Senegal">Senegal</option>
                        <option value="Serbia">Serbia</option>
                        <option value="Seychelles">Seychelles</option>
                        <option value="Sierra Leona">Sierra Leona</option>
                        <option value="Singapur">Singapur</option>
                        <option value="Siria">Siria</option>
                        <option value="Somalia">Somalia</option>
                        <option value="Sri Lanka">Sri Lanka</option>
                        <option value="Sudáfrica">Sudáfrica</option>
                        <option value="Sudán">Sudán</option>
                        <option value="Sudán del Sur">Sudán del Sur</option>
                        <option value="Suecia">Suecia</option>
                        <option value="Suiza">Suiza</option>
                        <option value="Surinam">Surinam</option>
                        <option value="Tailandia">Tailandia</option>
                        <option value="Tanzania">Tanzania</option>
                        <option value="Tayikistán">Tayikistán</option>
                        <option value="Timor Oriental">Timor Oriental</option>
                        <option value="Togo">Togo</option>
                        <option value="Tonga">Tonga</option>
                        <option value="Trinidad y Tobago">Trinidad y Tobago</option>
                        <option value="Túnez">Túnez</option>
                        <option value="Turkmenistán">Turkmenistán</option>
                        <option value="Turquía">Turquía</option>
                        <option value="Tuvalu">Tuvalu</option>
                        <option value="Ucrania">Ucrania</option>
                        <option value="Uganda">Uganda</option>
                        <option value="Uruguay">Uruguay</option>
                        <option value="Uzbekistán">Uzbekistán</option>
                        <option value="Vanuatu">Vanuatu</option>
                        <option value="Venezuela">Venezuela</option>
                        <option value="Vietnam">Vietnam</option>
                        <option value="Yemen">Yemen</option>
                        <option value="Yibuti">Yibuti</option>
                        <option value="Zambia">Zambia</option>
                        <option value="Zimbabue">Zimbabue</option>
                        </select>
                        <div id="paises-seleccionados" class="mt-2 d-flex flex-wrap gap-1" aria-live="polite"></div>
                        <small id="ayuda-paises" class="text-muted d-block">En Windows, mantén presionado Ctrl; en Mac, Cmd, para seleccionar varios países. No incluyas Estados Unidos aquí si ya respondiste en el campo de viajes anteriores a EE. UU.</small>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="ningun_pais" name="ningun_pais" value="Sí">
                            <label class="form-check-label" for="ningun_pais">No he visitado otros países en los últimos 5 años</label>
                        </div>
                    </div>
                </div>

                <h5 class="text-secondary mt-4 mb-3">6. Antecedentes y seguridad</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="negacion_visa" class="form-label fw-semibold">¿Le han negado una visa de EE. UU. anteriormente?</label>
                        <select id="negacion_visa" name="negacion_visa" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" data-show-when="negacion_visa=Sí">
                        <label for="detalle_negacion_visa" class="form-label fw-semibold">Si respondió sí, explique brevemente</label>
                        <input type="text" id="detalle_negacion_visa" name="detalle_negacion_visa" class="form-control" placeholder="Año y circunstancias, si las conoce">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="negacion_entrada" class="form-label fw-semibold">¿Le han denegado la entrada a algún país?</label>
                        <select id="negacion_entrada" name="negacion_entrada" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="antecedentes_judiciales" class="form-label fw-semibold">¿Ha sido arrestado o tiene antecedentes judiciales?</label>
                        <select id="antecedentes_judiciales" name="antecedentes_judiciales" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="overstay" class="form-label fw-semibold">¿Ha excedido el tiempo autorizado de estadía en algún país?</label>
                        <select id="overstay" name="overstay" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label for="detalle_antecedentes" class="form-label fw-semibold">Detalles adicionales relevantes (opcional)</label>
                        <textarea id="detalle_antecedentes" name="detalle_antecedentes" class="form-control" rows="2" placeholder="Explique cualquier respuesta afirmativa con información veraz"></textarea>
                    </div>
                </div>

                <h5 class="text-secondary mt-4 mb-3">7. Ajustes de la simulación</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ajuste_dificultad" class="form-label fw-semibold">Tipo de cónsul</label>
                        <select id="ajuste_dificultad" class="form-select" data-setting="dificultad">
                            <option value="amable">😊 Amable: ideal para la primera práctica</option>
                            <option value="realista" selected>🧑‍💼 Realista: como en la ventanilla</option>
                            <option value="estricto">🧐 Estricto: repregunta todo y busca contradicciones</option>
                            <option value="implacable">🔥 Implacable: presiona, interrumpe y rechaza ante la duda</option>
                        </select>
                        <div id="dificultad-ayuda" class="form-text"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="ajuste_idioma" class="form-label fw-semibold">Idioma de la entrevista</label>
                        <select id="ajuste_idioma" class="form-select" data-setting="idioma">
                            <option value="es" selected>🇪🇸 Español</option>
                            <option value="en">🇺🇸 Inglés (el informe final será en español)</option>
                        </select>
                    </div>
                    <div class="col-12 mb-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="ajuste_presion" data-setting="presion" role="switch">
                            <label class="form-check-label fw-semibold" for="ajuste_presion">⏳ Modo presión: tiempo límite para responder cada pregunta</label>
                        </div>
                        <div class="form-text">Si no respondes a tiempo, el cónsul lo toma como silencio o nerviosismo. Así practicas responder rápido y sin titubear.</div>
                    </div>
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="recordar_datos">
                            <label class="form-check-label" for="recordar_datos">Recordar mis datos en este navegador <small class="text-muted">(no lo actives en una computadora compartida)</small></label>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4" role="note">
                    <strong>Importante:</strong> utiliza información veraz y coherente con tu solicitud. Este simulador es una herramienta de práctica: no está afiliado al Departamento de Estado ni garantiza la aprobación de una visa. Tus respuestas se envían a un servicio de IA solo para generar la entrevista y no se guardan en este servidor.
                </div>
                <div class="d-grid mt-4">
                    <button type="submit" id="btn-start" class="btn btn-primary btn-lg py-3">🚀 Generar cónsul y empezar entrevista</button>
                </div>
            </form>
            <div id="upload-status" class="mt-3 text-center fw-semibold" role="status" aria-live="polite"></div>
        </div>
    </div>

    <!-- PASO 2: CHAT Y VOZ -->
    <div id="step-chat" class="card p-4 d-none shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom no-print">
                <div>
                    <h5 class="card-title text-primary mb-0">🪟 Ventanilla consular</h5>
                    <small class="text-muted">Responde con naturalidad, claridad y brevedad.</small>
                    <div class="d-flex flex-wrap gap-1 mt-1">
                        <span id="question-counter" class="badge text-bg-light border"></span>
                        <span id="interview-timer" class="badge text-bg-light border" title="Las entrevistas reales suelen durar entre 2 y 5 minutos">⏱️ 0:00</span>
                        <span id="settings-badge" class="badge text-bg-light border"></span>
                        <span id="answer-countdown" class="badge d-none" aria-live="polite"></span>
                    </div>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="voice-toggle" name="voice_toggle" checked>
                    <label class="form-check-label fw-semibold text-secondary" for="voice-toggle">🔊 Voz del cónsul</label>
                </div>
            </div>
            <div class="print-only print-header">
                <h2>Informe de práctica: entrevista consular simulada</h2>
                <p id="print-meta"></p>
            </div>
            <div id="chat-container" class="chat-container mb-3 shadow-sm" aria-live="polite" aria-label="Conversación de entrevista"></div>
            <div id="chat-input-group" class="input-group">
                <input type="text" id="user-input" name="user_input" class="form-control form-control-lg" placeholder="Escribe tu respuesta o usa el micrófono..." autocomplete="off" disabled>
                <button type="button" id="btn-mic" class="btn btn-outline-danger px-3" disabled title="Hablar" aria-label="Hablar">🎙️</button>
                <button type="button" id="btn-send" class="btn btn-success px-4" disabled>Enviar</button>
            </div>
            <div class="d-flex flex-wrap gap-2 justify-content-end mt-3">
                <button type="button" id="btn-print" class="btn btn-outline-success btn-sm d-none">📄 Descargar informe</button>
                <button type="button" id="btn-edit" class="btn btn-outline-secondary btn-sm">✏️ Editar perfil</button>
                <button type="button" id="btn-restart" class="btn btn-outline-primary btn-sm">🔄 Repetir entrevista</button>
            </div>
        </div>
    </div>
</div>

<footer class="text-center text-muted small pb-4 no-print">
    Herramienta de práctica independiente. No está afiliada al Gobierno de los EE. UU.
</footer>

<script src="assets/js/voice_engine.js?v=5"></script>
</body>
</html>
