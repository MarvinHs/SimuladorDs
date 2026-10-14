document.addEventListener('DOMContentLoaded', () => {
    const uploadForm = document.getElementById('upload-form');
    const uploadStatus = document.getElementById('upload-status');
    const stepUpload = document.getElementById('step-upload');
    const stepChat = document.getElementById('step-chat');

    const chatContainer = document.getElementById('chat-container');
    const userInput = document.getElementById('user-input');
    const btnSend = document.getElementById('btn-send');
    const btnMic = document.getElementById('btn-mic');
    const voiceToggle = document.getElementById('voice-toggle');

    if (uploadForm) {
        uploadForm.addEventListener('submit', async (e) => {
            // EVITAR RECARGA DE PÁGINA
            e.preventDefault();
            
            try {
                // Obtener países seleccionados si existe el elemento
                const paisesSelect = document.getElementById('paises_visitados');
                let paisesSeleccionados = 'Ninguno';
                if (paisesSelect) {
                    paisesSeleccionados = Array.from(paisesSelect.selectedOptions).map(opt => opt.value).join(', ');
                }

                // Capturar valores con fallback seguro (evita errores si algún ID no existe)
                const getValue = (id) => {
                    const el = document.getElementById(id);
                    return el ? el.value.trim() : 'No especificado';
                };

                const perfilData = {
                    nombre: getValue('nombre'),
                    estado_civil: getValue('estado_civil'),
                    hijos: getValue('hijos'),
                    padre: getValue('nombre_padre'),
                    padre_eeuu: getValue('padre_eeuu'),
                    madre: getValue('nombre_madre'),
                    madre_eeuu: getValue('madre_eeuu'),
                    ocupacion: getValue('ocupacion'),
                    empresa: getValue('empresa'),
                    antiguedad: getValue('antiguedad'),
                    ingresos: getValue('ingresos'),
                    otros_ingresos: getValue('otros_ingresos'),
                    motivo_viaje: getValue('motivo_viaje'),
                    destino: getValue('destino'),
                    tiempo_estadia: getValue('tiempo_estadia'),
                    fecha_viaje: getValue('fecha_viaje'),
                    financiacion: getValue('financiacion'),
                    acompanantes: getValue('acompanantes'),
                    paises_visitados: paisesSeleccionados || 'Ninguno',
                    negacion_visa: getValue('negacion_visa'),
                    negacion_entrada: getValue('negacion_entrada'),
                    antecedentes_judiciales: getValue('antecedentes_judiciales'),
                    overstay: getValue('overstay')
                };

                uploadStatus.innerHTML = '<span class="text-info">Iniciando ventanilla consular...</span>';

                const ds160Text = `
                    Nombre: ${perfilData.nombre}
                    Estado Civil: ${perfilData.estado_civil}
                    Hijos: ${perfilData.hijos}
                    Padre: ${perfilData.padre} (En EE.UU.: ${perfilData.padre_eeuu})
                    Madre: ${perfilData.madre} (En EE.UU.: ${perfilData.madre_eeuu})
                    Ocupación: ${perfilData.ocupacion} en ${perfilData.empresa} (Antigüedad: ${perfilData.antiguedad})
                    Ingresos: ${perfilData.ingresos} | Otros ingresos: ${perfilData.otros_ingresos}
                    Motivo de viaje: ${perfilData.motivo_viaje} a ${perfilData.destino}
                    Estadía: ${perfilData.tiempo_estadia} (Fecha: ${perfilData.fecha_viaje})
                    Financiamiento: ${perfilData.financiacion}
                    Acompañantes: ${perfilData.acompanantes}
                    Países visitados: ${perfilData.paises_visitados}
                    Negación de visa previa: ${perfilData.negacion_visa}
                    Negación de entrada previa: ${perfilData.negacion_entrada}
                    Antecedentes: ${perfilData.antecedentes_judiciales}
                    Overstay: ${perfilData.overstay}
                `;

                const res = await fetch('api/upload.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ds160_text: ds160Text })
                });

                const data = await res.json();

                if (data.success) {
                    stepUpload.classList.add('d-none');
                    stepChat.classList.remove('d-none');
                    triggerConsulResponse('');
                } else {
                    uploadStatus.innerHTML = `<span class="text-danger">${data.error || 'Error desconocido al procesar.'}</span>`;
                }
            } catch (err) {
                console.error("Error al procesar el formulario:", err);
                uploadStatus.innerHTML = `<span class="text-danger">Error conectando con el servidor: ${err.message}</span>`;
            }
        });
    }

    // Funciones del Chat
    async function sendMessage() {
        const text = userInput.value.trim();
        if (!text) return;

        appendMessage('user', text);
        userInput.value = '';
        setControls(false);

        await triggerConsulResponse(text);
    }

    if (btnSend) btnSend.addEventListener('click', sendMessage);
    if (userInput) userInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });

    async function triggerConsulResponse(message) {
        try {
            const res = await fetch('api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: message })
            });
            const raw = await res.text();
            let data;
            try {
                data = JSON.parse(raw);
            } catch (parseErr) {
                console.error('Respuesta no JSON de api/chat.php (HTTP ' + res.status + '):', raw);
                appendMessage('assistant', 'Error del servidor (HTTP ' + res.status + '): ' + raw.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 300));
                return;
            }

            if (data.reply) {
                appendMessage('assistant', data.reply);
                if (voiceToggle && voiceToggle.checked) speakText(data.reply);
            } else if (data.error) {
                appendMessage('assistant', 'Error: ' + data.error);
            }
        } catch (e) {
            appendMessage('assistant', 'Error de red al conectar con el cónsul.');
        } finally {
            setControls(true);
        }
    }

    function appendMessage(role, text) {
        if (!chatContainer) return;
        const div = document.createElement('div');
        div.className = role === 'user' ? 'msg-user' : 'msg-assistant';
        div.innerText = text;
        chatContainer.appendChild(div);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function setControls(enable) {
        if (userInput) userInput.disabled = !enable;
        if (btnSend) btnSend.disabled = !enable;
        if (btnMic) btnMic.disabled = !enable;
        if (enable && userInput) userInput.focus();
    }

    // Reconocimiento de Voz
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (SpeechRecognition && btnMic) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'es-DO';

        btnMic.addEventListener('click', () => {
            recognition.start();
            btnMic.classList.replace('btn-outline-danger', 'btn-danger');
        });

        recognition.onresult = (e) => {
            userInput.value = e.results[0][0].transcript;
            btnMic.classList.replace('btn-danger', 'btn-outline-danger');
            sendMessage();
        };

        recognition.onerror = () => btnMic.classList.replace('btn-danger', 'btn-outline-danger');
    } else if (btnMic) {
        btnMic.style.display = 'none';
    }

    // Voz del Cónsul
    function speakText(text) {
        if (!('speechSynthesis' in window)) return;
        window.speechSynthesis.cancel();

        const cleanText = text.replace(/[DECISIÓN FINAL]/g, '');
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = 'es-US';
        utterance.rate = 0.95;

        window.speechSynthesis.speak(utterance);
    }
});