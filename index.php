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
                        <input type="text" id="nombre" class="form-control" placeholder="Ej. Melvin Heredia" required>
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
                        <input type="text" id="ocupacion" class="form-control" placeholder="Ej. Coordinador Administrativo" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="empresa" class="form-label fw-semibold">Nombre de la Empresa / Institución</label>
                        <input type="text" id="empresa" class="form-control" placeholder="Ej. Rodríguez Peña & Asociados" required>
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
                        <input type="text" id="otros_ingresos" class="form-control" placeholder="Ej. Desarrollador de software freelance">
                    </div>
                </div>

                <!-- SECCIÓN 4: Detalles del Viaje -->
                <h5 class="text-secondary mt-4 mb-3">4. Detalles del Viaje a EE.UU.</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="motivo_viaje" class="form-label fw-semibold">Propósito del Viaje</label>
                        <input type="text" id="motivo_viaje" class="form-control" placeholder="Ej. Turismo, ver juego de los Astros en Houston" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="destino" class="form-label fw-semibold">Ciudad y Estado de Destino</label>
                        <input type="text" id="destino" class="form-control" placeholder="Ej. Houston, Texas" required>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="tiempo_estadia" class="form-label fw-semibold">Tiempo de Estadía Estimado</label>
                        <input type="text" id="tiempo_estadia" class="form-control" placeholder="Ej. 13 días" required>
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
                        <input type="text" id="acompanantes" class="form-control" placeholder="Ej. Viajo solo, me encuentro con amigos allá">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="paises_visitados" class="form-label fw-semibold">Países visitados en los últimos 5 años</label>
                        <select id="paises_visitados" class="form-select" multiple size="3">
                            <option value="Colombia">Colombia</option>
                            <option value="Ecuador">Ecuador</option>
                            <option value="México">México</option>
                            <option value="España">España</option>
                            <option value="Estados Unidos (Visados previos)">Estados Unidos (Visados previos)</option>
                            <option value="Ninguno">Ninguno</option>
                        </select>
                        <small class="text-muted">Mantén presionado Ctrl para seleccionar varios países.</small>
                    </div>
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