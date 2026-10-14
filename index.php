<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simulador Consular DS-160 | Perfil Personalizado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="container py-5" style="max-width: 950px;">
    <!-- Encabezado -->
    <div class="text-center mb-5">
        <h1 class="hero-title">🏛️ Simulador de Entrevista Consular</h1>
        <p class="text-muted">Completa los datos de tu perfil DS-160 para generar una simulación de alto realismo con IA.</p>
    </div>

    <!-- Paso 1: Formulario Estructurado -->
    <div id="step-upload" class="card p-4 mb-4 shadow-sm">
        <div class="card-body">
            <h4 class="card-title mb-4 text-primary border-bottom pb-2">📋 Cuestionario de Perfil DS-160</h4>
            
            <form id="upload-form">
                
                <!-- SECCIÓN 1: Información Personal -->
                <h5 class="text-secondary mt-4 mb-3">1. Información Personal</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre" class="form-label fw-semibold">Nombre y Apellidos Completos</label>
                        <input type="text" id="nombre" class="form-control" placeholder="Nombre y Apellido" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="estado_civil" class="form-label fw-semibold">Estado Civil</label>
                        <select id="estado_civil" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <option value="Soltero/a">Soltero/a</option>
                            <option value="Casado/a">Casado/a</option>
                            <option value="Unión libre">Unión libre</option>
                            <option value="Divorciado/a">Divorciado/a</option>
                            <option value="Viudo/a">Viudo/a</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="hijos" class="form-label fw-semibold">¿Tiene Hijos?</label>
                        <select id="hijos" class="form-select" required>
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 2: Información de los Padres -->
                <h5 class="text-secondary mt-4 mb-3">2. Información de los Padres</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre_padre" class="form-label fw-semibold">Nombre Completo del Padre</label>
                        <input type="text" id="nombre_padre" class="form-control" placeholder="Ej. Juan Pérez">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="padre_eeuu" class="form-label fw-semibold">¿El padre se encuentra en EE.UU.?</label>
                        <select id="padre_eeuu" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nombre_madre" class="form-label fw-semibold">Nombre Completo de la Madre</label>
                        <input type="text" id="nombre_madre" class="form-control" placeholder="Ej. María Gómez">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="madre_eeuu" class="form-label fw-semibold">¿La madre se encuentra en EE.UU.?</label>
                        <select id="madre_eeuu" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                </div>

                <!-- SECCIÓN 3: Trabajo y Lazos Económicos -->
                <h5 class="text-secondary mt-4 mb-3">3. Trabajo, Estudios e Ingresos</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="ocupacion" class="form-label fw-semibold">Ocupación / Cargo Actual</label>
                        <input type="text" id="ocupacion" class="form-control" placeholder="Cargo Actual" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="empresa" class="form-label fw-semibold">Nombre de la Empresa / Institución</label>
                        <input type="text" id="empresa" class="form-control" placeholder="Empresa" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="antiguedad" class="form-label fw-semibold">Tiempo laborando (Desde)</label>
                        <input type="date" id="antiguedad" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="ingresos" class="form-label fw-semibold">Ingreso Mensual Aproximado</label>
                        <input type="text" id="ingresos" class="form-control" placeholder="Ej. $60,000 DOP / $1,000 USD" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="otros_ingresos" class="form-label fw-semibold">Otros ingresos / Independiente</label>
                        <input type="text" id="otros_ingresos" class="form-control" placeholder="Monto Extra">
                    </div>
                </div>

                <!-- SECCIÓN 4: Detalles del Viaje -->
                <h5 class="text-secondary mt-4 mb-3">4. Detalles del Viaje a EE.UU.</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="motivo_viaje" class="form-label fw-semibold">Propósito del Viaje</label>
                        <input type="text" id="motivo_viaje" class="form-control" placeholder="Ej. Turismo, Visitas familiares" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="destino" class="form-label fw-semibold">Ciudad y Estado de Destino</label>
                        <input type="text" id="destino" class="form-control" placeholder="Estados Unidos, Washington" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tiempo_estadia" class="form-label fw-semibold">Tiempo de Estadía Estimado</label>
                        <input type="text" id="tiempo_estadia" class="form-control" placeholder="Ej. X días" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="fecha_viaje" class="form-label fw-semibold">Fecha Tentativa de Viaje</label>
                        <input type="date" id="fecha_viaje" class="form-control" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="financiacion" class="form-label fw-semibold">¿Quién paga los gastos?</label>
                        <input type="text" id="financiacion" class="form-control" placeholder="Ej. Ahorros propios / Salario" required>
                    </div>
                </div>

                <!-- SECCIÓN 5: Acompañantes y Historial de Países -->
                <h5 class="text-secondary mt-4 mb-3">5. Acompañantes e Historial de Viajes</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="acompanantes" class="form-label fw-semibold">¿Viaja solo o acompañado?</label>
                        <input type="text" id="acompanantes" class="form-control" placeholder="Ej. Viajo solo, con amigos, con familia" required>
                    </div>
                   
<div class="col-md-6 mb-3">
    <label for="paises_visitados" class="form-label fw-semibold">
        Países visitados en los últimos 5 años
    </label>

    <select
        id="paises_visitados"
        name="paises_visitados[]"
        class="form-select"
        multiple
        size="6"
    >
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
        <option value="Estados Unidos (Visados previos)">Estados Unidos (Visados previos)</option>
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
        <option value="Ninguno">Ninguno</option>
    </select>

    <small class="text-muted">
        Mantén presionado Ctrl (o Cmd en Mac) para seleccionar varios países.
    </small>
</div>


                <!-- SECCIÓN 6: Antecedentes y Seguridad -->
                <h5 class="text-secondary mt-4 mb-3">6. Antecedentes y Seguridad (Security & Background)</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="negacion_visa" class="form-label fw-semibold">¿Le han negado la visa de EE.UU. antes?</label>
                        <select id="negacion_visa" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="negacion_entrada" class="form-label fw-semibold">¿Le han denegado la entrada a otro país?</label>
                        <select id="negacion_entrada" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="antecedentes_judiciales" class="form-label fw-semibold">¿Tiene antecedentes judiciales o arrestos?</label>
                        <select id="antecedentes_judiciales" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="overstay" class="form-label fw-semibold">¿Ha excedido el tiempo de estadía en algún país (Overstay)?</label>
                        <select id="overstay" class="form-select">
                            <option value="No">No</option>
                            <option value="Sí">Sí</option>
                        </select>
                    </div>
                </div>
                
                <div class="d-grid mt-5">
                    <button type="submit" class="btn btn-primary btn-lg py-3">🚀 Generar Cónsul y Empezar Entrevista</button>
                </div>
            </form>
            <div id="upload-status" class="mt-3 text-center fw-semibold"></div>
        </div>
    </div>

    <!-- Paso 2: Interfaz Consular (Chat & Voz) -->
    <div id="step-chat" class="card p-4 d-none shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <div>
                    <h5 class="card-title text-primary mb-0">Ventanilla Consular Activa</h5>
                    <small class="text-muted">Responde con naturalidad, claridad y brevedad.</small>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="voice-toggle" checked>
                    <label class="form-check-label fw-semibold text-secondary" for="voice-toggle">🔊 Voz del Cónsul</label>
                </div>
            </div>

            <div id="chat-container" class="chat-container mb-3 shadow-sm"></div>

            <div class="input-group">
                <input type="text" id="user-input" class="form-control form-control-lg" placeholder="Escribe tu respuesta o usa el micrófono..." disabled>
                <button id="btn-mic" class="btn btn-outline-danger px-3" disabled title="Hablar">🎙️</button>
                <button id="btn-send" class="btn btn-success px-4" disabled>Enviar</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/voice_engine.js"></script>
</body>
</html>