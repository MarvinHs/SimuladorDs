// assets/js/voice_engine.js
// Lógica del simulador: formulario de perfil, chat con el cónsul y voz.
document.addEventListener('DOMContentLoaded', () => {
    const $ = (id) => document.getElementById(id);
    const DECISION_MARKER = '[DECISIÓN FINAL]';

    const form = $('upload-form');
    const uploadStatus = $('upload-status');
    const stepUpload = $('step-upload');
    const stepChat = $('step-chat');
    const btnStart = $('btn-start');

    const chatContainer = $('chat-container');
    const userInput = $('user-input');
    const btnSend = $('btn-send');
    const btnMic = $('btn-mic');
    const btnRestart = $('btn-restart');
    const btnEdit = $('btn-edit');
    const voiceToggle = $('voice-toggle');
    const questionCounter = $('question-counter');
    const inputGroup = $('chat-input-group');

    const btnPrint = $('btn-print');
    const timerBadge = $('interview-timer');
    const settingsBadge = $('settings-badge');
    const STORAGE_KEY = 'simuladords:perfil';
    const countdownBadge = $('answer-countdown');

    const DIFFICULTY_LABELS = { amable: '😊 Amable', realista: '🧑‍💼 Realista', estricto: '🧐 Estricto', implacable: '🔥 Implacable' };
    const DIFFICULTY_HELP = {
        amable: 'Paciente y comprensivo. Ideal para tu primera práctica.',
        realista: 'Seco y con prisa, como en la ventanilla. Parte de que podrías ser un posible inmigrante y rechaza ante dudas razonables.',
        estricto: 'Verifica cifras y fechas contra tu perfil, pone preguntas trampa e interrumpe si hablas de más.',
        implacable: 'Su postura por defecto es rechazar. Presiona, repite preguntas con otras palabras y no perdona contradicciones.',
    };
    // Segundos para responder en modo presión, según la dificultad
    const PRESSURE_SECONDS = { amable: 30, realista: 20, estricto: 15, implacable: 10 };

    let lastProfile = null;
    let lastSettings = null;
    let timerId = null;
    let timerStart = 0;
    let busy = false;
    let finished = false;
    let questionNumber = 0;
    let maxTurns = 10;

    /* =========================================================
     * 1. FORMULARIO
     * ======================================================= */

    // Campos de detalle que solo aparecen si la respuesta lo requiere.
    // Formato: data-show-when="campo=Valor1|Valor2"
    const conditionals = [...document.querySelectorAll('[data-show-when]')].map((el) => {
        const [field, values] = el.dataset.showWhen.split('=');
        return { el, source: $(field), values: values.split('|') };
    });

    function updateConditionals() {
        conditionals.forEach(({ el, source, values }) => {
            const visible = source && values.includes(source.value);
            el.classList.toggle('d-none', !visible);
        });
    }
    conditionals.forEach(({ source }) => source && source.addEventListener('change', updateConditionals));
    updateConditionals();

    // Países: buscador, chips de seleccionados y casilla "ninguno".
    const countriesSelect = $('paises_visitados');
    const countryFilter = $('filtro_paises');
    const countryChips = $('paises-seleccionados');
    const noneCheckbox = $('ningun_pais');

    function renderCountryChips() {
        if (!countryChips || !countriesSelect) return;
        countryChips.replaceChildren(...[...countriesSelect.selectedOptions].map((opt) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'btn btn-sm btn-primary country-chip';
            chip.textContent = `${opt.value} ✕`;
            chip.title = `Quitar ${opt.value}`;
            chip.addEventListener('click', () => {
                opt.selected = false;
                renderCountryChips();
            });
            return chip;
        }));
    }

    if (countriesSelect) {
        countriesSelect.addEventListener('change', () => {
            if (countriesSelect.selectedOptions.length > 0 && noneCheckbox) noneCheckbox.checked = false;
            renderCountryChips();
        });
    }

    if (countryFilter && countriesSelect) {
        const normalize = (s) => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();
        countryFilter.addEventListener('input', () => {
            const q = normalize(countryFilter.value.trim());
            [...countriesSelect.options].forEach((opt) => {
                opt.hidden = q !== '' && !normalize(opt.value).includes(q);
            });
        });
    }

    if (noneCheckbox && countriesSelect) {
        noneCheckbox.addEventListener('change', () => {
            if (noneCheckbox.checked) {
                [...countriesSelect.options].forEach((opt) => { opt.selected = false; });
                renderCountryChips();
            }
        });
    }

    // Fechas coherentes
    const today = new Date();
    const todayStr = new Date(today.getTime() - today.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
    const departure = $('fecha_viaje');
    const returnDate = $('fecha_regreso');
    const jobStart = $('antiguedad');
    if (departure) departure.min = todayStr;
    if (jobStart) jobStart.max = todayStr;

    function validateDates() {
        if (!departure || !returnDate) return true;
        returnDate.min = departure.value || todayStr;
        const bad = departure.value && returnDate.value && returnDate.value < departure.value;
        returnDate.setCustomValidity(bad ? 'La fecha de regreso no puede ser anterior a la fecha de salida.' : '');
        return !bad;
    }
    departure && departure.addEventListener('change', validateDates);
    returnDate && returnDate.addEventListener('change', validateDates);

    /* ---------- Perfil de ejemplo, limpiar y recordar datos ---------- */

    const EXAMPLE_PROFILE = {
        nombre: 'Laura Martínez Peña', estado_civil: 'Casado/a', hijos: 'Sí',
        nombre_padre: 'Ramón Martínez', padre_eeuu: 'No', nombre_madre: 'Carmen Peña', madre_eeuu: 'No',
        residencia_actual: 'República Dominicana', familiares_eeuu: 'Sí',
        detalle_familiares_eeuu: 'Una prima en Nueva York, ciudadana estadounidense',
        ocupacion: 'Contadora sénior', empresa: 'Distribuidora del Caribe, S.R.L.', antiguedad: '2017-03-01',
        ingresos: 'RD$95,000', otros_ingresos: 'Asesorías contables, aprox. RD$15,000 al mes',
        estudios_actuales: 'No', vacaciones_autorizadas: 'Sí', detalle_vacaciones: '10 días en julio, aprobados por RR. HH.',
        motivo_viaje: 'Turismo con mi familia (parques de Orlando)', destino: 'Orlando, Florida', tiempo_estadia: '8 días',
        financiacion: 'Ahorros propios y de mi esposo', presupuesto: 'US$4,500', ahorros: 'US$7,000',
        hospedaje: 'Hotel reservado cerca de los parques', invitante: 'No aplica',
        motivo_regreso: 'Mi empleo, mis dos hijos en la escuela y mi apartamento propio',
        acompanantes: 'Con mi esposo y mis dos hijos', viajes_eeuu: 'No',
        negacion_visa: 'No', negacion_entrada: 'No', antecedentes_judiciales: 'No', overstay: 'No',
    };

    function nextMonthDate(offsetDays) {
        const d = new Date();
        d.setDate(d.getDate() + offsetDays);
        return d.toISOString().slice(0, 10);
    }

    function fillForm(values, countries = []) {
        Object.entries(values).forEach(([key, value]) => {
            const field = form.elements[key];
            if (field && 'value' in field) field.value = value;
        });
        if (countriesSelect) {
            [...countriesSelect.options].forEach((o) => { o.selected = countries.includes(o.value); });
        }
        if (noneCheckbox) noneCheckbox.checked = values.ningun_pais === 'Sí';
        updateConditionals();
        renderCountryChips();
        validateDates();
    }

    $('btn-example') && $('btn-example').addEventListener('click', () => {
        fillForm({ ...EXAMPLE_PROFILE, fecha_viaje: nextMonthDate(60), fecha_regreso: nextMonthDate(68) }, ['Panamá', 'Colombia']);
        setStatus('Perfil de ejemplo cargado. Puedes modificarlo o empezar la entrevista.', 'success');
    });

    $('btn-clear') && $('btn-clear').addEventListener('click', () => {
        if (!confirmClear()) return;
        form.reset();
        renderCountryChips();
        updateConditionals();
        forgetDraft();
        setStatus('');
    });

    function confirmClear() {
        // Evita window.confirm (bloquea la página); se usa doble clic de seguridad.
        const btn = $('btn-clear');
        if (btn.dataset.armed === '1') { btn.dataset.armed = ''; btn.textContent = '🧹 Limpiar'; return true; }
        btn.dataset.armed = '1';
        btn.textContent = '⚠️ ¿Seguro? Pulsa otra vez';
        setTimeout(() => { btn.dataset.armed = ''; btn.textContent = '🧹 Limpiar'; }, 3000);
        return false;
    }

    const rememberBox = $('recordar_datos');

    function storage() {
        try { return window.localStorage; } catch { return null; }
    }

    function saveDraft() {
        const store = storage();
        if (!store || !rememberBox || !rememberBox.checked) return;
        const values = {};
        [...form.elements].forEach((el) => {
            if (!el.name || el.name === 'paises_visitados[]') return;
            if (el.type === 'checkbox') values[el.name] = el.checked ? 'Sí' : '';
            else values[el.name] = el.value;
        });
        const countries = countriesSelect ? [...countriesSelect.selectedOptions].map((o) => o.value) : [];
        const settings = collectSettings();
        try { store.setItem(STORAGE_KEY, JSON.stringify({ values, countries, settings })); } catch { /* cuota llena */ }
    }

    function forgetDraft() {
        const store = storage();
        try { store && store.removeItem(STORAGE_KEY); } catch { /* sin acceso */ }
    }

    function loadDraft() {
        const store = storage();
        if (!store) return;
        let draft = null;
        try { draft = JSON.parse(store.getItem(STORAGE_KEY) || 'null'); } catch { draft = null; }
        if (!draft || !draft.values) return;
        fillForm(draft.values, draft.countries || []);
        if (draft.settings) {
            document.querySelectorAll('[data-setting]').forEach((el) => {
                const v = draft.settings[el.dataset.setting];
                if (v === undefined || v === null || v === '') return;
                if (el.type === 'checkbox') el.checked = Boolean(v); else el.value = v;
            });
        }
        if (rememberBox) rememberBox.checked = true;
        setStatus('Se cargaron los datos guardados en este navegador.', 'secondary');
    }

    if (rememberBox) {
        rememberBox.addEventListener('change', () => (rememberBox.checked ? saveDraft() : forgetDraft()));
    }
    form && form.addEventListener('input', saveDraft);
    form && form.addEventListener('change', saveDraft);

    function collectSettings() {
        const settings = {};
        document.querySelectorAll('[data-setting]').forEach((el) => {
            settings[el.dataset.setting] = el.type === 'checkbox' ? el.checked : el.value;
        });
        return settings;
    }

    function collectProfile() {
        const profile = {};
        const data = new FormData(form);
        for (const [key, value] of data.entries()) {
            if (key === 'paises_visitados[]' || key === 'ningun_pais') continue;
            const field = form.elements[key];
            const wrapper = field && field.closest && field.closest('[data-show-when]');
            if (wrapper && wrapper.classList.contains('d-none')) continue; // detalle oculto
            const text = String(value).trim();
            if (text) profile[key] = text;
        }
        const countries = countriesSelect ? [...countriesSelect.selectedOptions].map((o) => o.value) : [];
        if (countries.length) profile.paises_visitados = countries.join(', ');
        else if (noneCheckbox && noneCheckbox.checked) profile.paises_visitados = 'Ninguno';
        return profile;
    }

    function setStatus(html, type = 'info') {
        uploadStatus.innerHTML = html ? `<span class="text-${type}">${html}</span>` : '';
    }

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!validateDates() || !form.reportValidity()) return;
            lastProfile = collectProfile();
            lastSettings = collectSettings();
            saveDraft();
            await startInterview(lastProfile, lastSettings);
        });
    }

    loadDraft();

    const difficultySelect = $('ajuste_dificultad');
    const difficultyHelp = $('dificultad-ayuda');
    function updateDifficultyHelp() {
        if (difficultySelect && difficultyHelp) difficultyHelp.textContent = DIFFICULTY_HELP[difficultySelect.value] || '';
    }
    difficultySelect && difficultySelect.addEventListener('change', updateDifficultyHelp);
    updateDifficultyHelp();

    // Avisa si el servidor no tiene ninguna API de IA configurada.
    fetch('api/status.php', { credentials: 'same-origin' })
        .then((r) => r.json())
        .then((st) => {
            if (!st.ready) $('config-warning') && $('config-warning').classList.remove('d-none');
            else console.info('IA disponible:', st.providers.map((p) => `${p.name} (${p.model})`).join(' → '));
        })
        .catch(() => { /* sin estado: no bloquea el uso */ });

    /* =========================================================
     * 2. ENTREVISTA
     * ======================================================= */

    async function postJson(url, body) {
        const res = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(body),
        });
        const raw = await res.text();
        try {
            return { status: res.status, data: JSON.parse(raw) };
        } catch {
            console.error(`Respuesta no JSON de ${url} (HTTP ${res.status}):`, raw);
            const snippet = raw.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 200);
            return { status: res.status, data: { error: `Error del servidor (HTTP ${res.status}). ${snippet}` } };
        }
    }

    async function startInterview(profile, settings) {
        btnStart && (btnStart.disabled = true);
        setStatus('<span class="spinner-border spinner-border-sm me-2"></span>Preparando la ventanilla consular...', 'primary');
        try {
            const { data } = await postJson('api/start.php', { perfil: profile, ajustes: settings });
            if (!data.success) {
                setStatus(escapeHtml(data.error || 'Error desconocido al procesar.'), 'danger');
                if (data.missing && data.missing[0] && $(data.missing[0])) $(data.missing[0]).focus();
                return;
            }
            maxTurns = data.maxTurns || maxTurns;
            setStatus('');
            resetChat();
            describeSettings(settings);
            setStepper(2);
            startTimer();
            stepUpload.classList.add('d-none');
            stepChat.classList.remove('d-none');
            stepChat.scrollIntoView({ behavior: 'smooth', block: 'start' });
            await askConsul('');
        } catch (err) {
            console.error('Error al iniciar la entrevista:', err);
            setStatus(`Error conectando con el servidor: ${escapeHtml(err.message)}`, 'danger');
        } finally {
            btnStart && (btnStart.disabled = false);
        }
    }

    function describeSettings(settings) {
        if (!settingsBadge) return;
        const dif = DIFFICULTY_LABELS[settings.dificultad] || '';
        const lang = settings.idioma === 'en' ? '🇺🇸 Inglés' : '🇪🇸 Español';
        settingsBadge.textContent = `${dif} · ${lang}${settings.presion ? ' · ⏳ Presión' : ''}`;
    }

    function setStepper(step) {
        [1, 2, 3].forEach((n) => {
            const li = $(`stepper-${n}`);
            if (!li) return;
            li.classList.toggle('active', n === step);
            li.classList.toggle('done', n < step);
        });
    }

    function formatElapsed() {
        const secs = Math.floor((Date.now() - timerStart) / 1000);
        return `${Math.floor(secs / 60)}:${String(secs % 60).padStart(2, '0')}`;
    }

    function startTimer() {
        stopTimer();
        timerStart = Date.now();
        if (!timerBadge) return;
        timerBadge.textContent = '⏱️ 0:00';
        timerId = setInterval(() => { timerBadge.textContent = `⏱️ ${formatElapsed()}`; }, 1000);
    }

    function stopTimer() {
        if (!timerId) return;
        clearInterval(timerId);
        timerId = null;
        if (timerBadge) timerBadge.textContent = `⏱️ ${formatElapsed()}`;
    }

    function resetChat() {
        stopSpeaking();
        stopCountdown();
        btnPrint && btnPrint.classList.add('d-none');
        chatContainer.replaceChildren();
        finished = false;
        questionNumber = 0;
        inputGroup.classList.remove('d-none');
        updateCounter();
    }

    function updateCounter() {
        if (!questionCounter) return;
        questionCounter.textContent = finished
            ? 'Entrevista terminada'
            : questionNumber > 0 ? `Pregunta ${questionNumber}` : '';
    }

    /* ---------- Modo presión: cuenta regresiva para responder ---------- */
    let countdownId = null;

    function stopCountdown() {
        if (countdownId) clearInterval(countdownId);
        countdownId = null;
        if (countdownBadge) countdownBadge.classList.add('d-none');
    }

    function startCountdown() {
        stopCountdown();
        const settings = lastSettings || {};
        if (!settings.presion || finished || busy) return;
        let left = PRESSURE_SECONDS[settings.dificultad] || 20;
        const render = () => {
            if (!countdownBadge) return;
            countdownBadge.textContent = `⏳ ${left}s para responder`;
            countdownBadge.className = `badge ${left <= 5 ? 'text-bg-danger countdown-urgent' : 'text-bg-warning'}`;
        };
        render();
        countdownId = setInterval(() => {
            left -= 1;
            if (left > 0) { render(); return; }
            stopCountdown();
            if (listening || busy || finished) return;
            if (userInput.value.trim()) {
                sendMessage();
            } else {
                appendMessage('user', '(Silencio: no respondiste a tiempo)');
                askConsul('(Silencio: el solicitante no respondió a tiempo)');
            }
        }, 1000);
    }

    async function sendMessage() {
        const text = userInput.value.trim();
        if (!text || busy || finished) return;
        stopCountdown();
        stopSpeaking();
        appendMessage('user', text);
        userInput.value = '';
        await askConsul(text);
    }

    async function askConsul(message) {
        stopCountdown();
        busy = true;
        setControls(false);
        const typing = showTyping();
        try {
            const { data } = await postJson('api/chat.php', { message });
            typing.remove();

            if (data.reply) {
                finished = Boolean(data.finished);
                if (finished) {
                    appendDecision(data.reply, data.approved);
                } else {
                    questionNumber += 1;
                    appendMessage('assistant', data.reply);
                }
                updateCounter();
                // La cuenta regresiva empieza cuando el cónsul termina de hablar
                speak(data.reply, finished ? null : startCountdown);
            } else {
                appendMessage('error', data.error || 'Error desconocido.');
                if (data.finished) finished = true;
                if (data.expired) appendMessage('error', 'Pulsa "Repetir entrevista" para empezar de nuevo con tu perfil.');
            }
        } catch (err) {
            typing.remove();
            appendMessage('error', 'Error de red al conectar con el cónsul. Revisa tu conexión e intenta de nuevo.');
        } finally {
            busy = false;
            if (finished) {
                inputGroup.classList.add('d-none');
                stopTimer();
                setStepper(3);
                btnPrint && btnPrint.classList.remove('d-none');
            } else {
                setControls(true);
            }
        }
    }

    btnSend && btnSend.addEventListener('click', sendMessage);
    userInput && userInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.isComposing) {
            e.preventDefault();
            sendMessage();
        }
    });

    btnRestart && btnRestart.addEventListener('click', () => {
        if (busy) return;
        if (!lastProfile) { showForm(); return; }
        startInterview(lastProfile, lastSettings || collectSettings());
    });

    btnEdit && btnEdit.addEventListener('click', () => {
        if (busy) return;
        showForm();
    });

    btnPrint && btnPrint.addEventListener('click', () => {
        const meta = $('print-meta');
        if (meta) {
            const who = lastProfile && lastProfile.nombre ? lastProfile.nombre : 'Solicitante';
            const date = new Date().toLocaleString('es-DO', { dateStyle: 'long', timeStyle: 'short' });
            meta.textContent = `${who} · ${date} · Duración: ${timerBadge ? timerBadge.textContent.replace('⏱️', '').trim() : ''} · ${settingsBadge ? settingsBadge.textContent : ''}`;
        }
        window.print();
    });

    function showForm() {
        stopSpeaking();
        stopCountdown();
        stopTimer();
        setStepper(1);
        stepChat.classList.add('d-none');
        stepUpload.classList.remove('d-none');
        stepUpload.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    /* =========================================================
     * 3. RENDERIZADO DEL CHAT
     * ======================================================= */

    function escapeHtml(text) {
        return String(text).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    }

    // Markdown mínimo y seguro (se escapa primero): **negrita**, viñetas y saltos de línea.
    function renderMarkdown(text) {
        const lines = escapeHtml(text).split(/\r?\n/);
        let html = '';
        let inList = false;
        for (const line of lines) {
            const item = line.match(/^\s*[-*•]\s+(.*)$/);
            if (item) {
                if (!inList) { html += '<ul>'; inList = true; }
                html += `<li>${item[1]}</li>`;
                continue;
            }
            if (inList) { html += '</ul>'; inList = false; }
            if (line.trim()) html += `<p>${line}</p>`;
        }
        if (inList) html += '</ul>';
        return html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
    }

    function appendMessage(role, text) {
        const div = document.createElement('div');
        div.className = { user: 'msg-user', assistant: 'msg-assistant', error: 'msg-error' }[role];
        if (role === 'assistant') {
            div.innerHTML = '<span class="msg-author">Cónsul</span>' + renderMarkdown(text);
        } else {
            div.textContent = text;
        }
        chatContainer.appendChild(div);
        scrollChat();
        return div;
    }

    function appendDecision(text, approved) {
        const body = text.replace(DECISION_MARKER, '').trim();
        const card = document.createElement('div');
        const state = approved === true ? 'approved' : approved === false ? 'rejected' : 'neutral';
        card.className = `decision-card decision-${state}`;
        const title = approved === true ? '✅ Visa aprobada' : approved === false ? '❌ Visa rechazada' : '📋 Decisión final';
        const score = body.match(/(\d{1,2})\s*\/\s*10/);
        card.innerHTML = `
            <div class="decision-header">
                <span>${title}</span>
                ${score ? `<span class="decision-score">${escapeHtml(score[1])}/10</span>` : ''}
            </div>
            <div class="decision-body">${renderMarkdown(body.replace(/^.*?(APROBADA|RECHAZADA)\s*/i, ''))}</div>
            <div class="decision-footer">Resultado simulado con fines de práctica. No predice la decisión real del consulado. Requisitos oficiales: <a href="/fuentes.html">fuentes oficiales</a>.</div>`;
        chatContainer.appendChild(card);
        scrollChat();
    }

    function showTyping() {
        const div = document.createElement('div');
        div.className = 'msg-assistant typing';
        div.setAttribute('aria-label', 'El cónsul está escribiendo');
        div.innerHTML = '<span></span><span></span><span></span>';
        chatContainer.appendChild(div);
        scrollChat();
        return div;
    }

    function scrollChat() {
        chatContainer.scrollTo({ top: chatContainer.scrollHeight, behavior: 'smooth' });
    }

    function setControls(enable) {
        userInput.disabled = !enable;
        btnSend.disabled = !enable;
        btnMic.disabled = !enable;
        if (enable) userInput.focus();
    }

    /* =========================================================
     * 4. VOZ: reconocimiento (micrófono) y síntesis (cónsul)
     * ======================================================= */

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    let listening = false;

    if (SpeechRecognition && btnMic) {
        const recognition = new SpeechRecognition();
        recognition.lang = 'es-DO';
        recognition.interimResults = true;
        recognition.maxAlternatives = 1;

        const setListening = (on) => {
            listening = on;
            btnMic.classList.toggle('btn-danger', on);
            btnMic.classList.toggle('btn-outline-danger', !on);
            btnMic.classList.toggle('mic-listening', on);
            btnMic.setAttribute('aria-pressed', String(on));
            userInput.placeholder = on ? 'Escuchando... habla ahora' : 'Escribe tu respuesta o usa el micrófono...';
        };

        btnMic.addEventListener('click', () => {
            if (listening) { recognition.stop(); return; }
            stopSpeaking();
            stopCountdown();
            try {
                recognition.start();
                setListening(true);
            } catch (err) {
                console.warn('No se pudo iniciar el micrófono:', err);
            }
        });

        let finalText = '';
        recognition.onstart = () => { finalText = ''; };
        recognition.onresult = (e) => {
            let interim = '';
            for (let i = e.resultIndex; i < e.results.length; i++) {
                const t = e.results[i][0].transcript;
                if (e.results[i].isFinal) finalText += t; else interim += t;
            }
            userInput.value = (finalText + interim).trim();
        };
        recognition.onend = () => {
            setListening(false);
            if (finalText.trim()) {
                userInput.value = finalText.trim();
                sendMessage();
            } else {
                startCountdown(); // no se entendió nada: vuelve a correr el tiempo
            }
        };
        recognition.onerror = (e) => {
            setListening(false);
            if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
                appendMessage('error', 'El navegador bloqueó el micrófono. Permite el acceso o escribe tu respuesta.');
            }
        };
    } else if (btnMic) {
        btnMic.style.display = 'none';
    }

    let spanishVoice = null;
    let speechGen = 0;
    function pickVoice() {
        if (!('speechSynthesis' in window)) return;
        const voices = window.speechSynthesis.getVoices();
        spanishVoice = voices.find((v) => /^es[-_](US|MX|DO|419)/i.test(v.lang))
            || voices.find((v) => /^es/i.test(v.lang))
            || null;
    }
    if ('speechSynthesis' in window) {
        pickVoice();
        window.speechSynthesis.onvoiceschanged = pickVoice;
    }

    function speak(text, onDone) {
        // Diferido: así corre después de que askConsul libere "busy"
        const done = () => { if (typeof onDone === 'function') setTimeout(onDone, 0); };
        if (!('speechSynthesis' in window) || !voiceToggle || !voiceToggle.checked) { done(); return; }
        window.speechSynthesis.cancel();

        let spoken = text;
        if (text.includes(DECISION_MARKER)) {
            // En la decisión, solo se lee el veredicto y lo que diría el cónsul.
            spoken = text.split(/\*\*Puntos fuertes/i)[0];
        }
        spoken = spoken
            .split(DECISION_MARKER).join('')
            .replace(/[*_#>`]/g, '')
            .replace(/^\s*-\s+/gm, '')
            .replace(/\s+/g, ' ')
            .trim();
        if (!spoken) { done(); return; }

        const utterance = new SpeechSynthesisUtterance(spoken);
        const gen = ++speechGen;
        let called = false;
        // Ignora callbacks de una locución cancelada (stopSpeaking)
        const once = () => { if (!called && gen === speechGen) { called = true; done(); } };
        utterance.onend = once;
        utterance.onerror = once;
        // Respaldo por si el navegador nunca dispara onend
        setTimeout(once, Math.min(30000, 1500 + spoken.length * 90));
        utterance.lang = spanishVoice ? spanishVoice.lang : 'es-US';
        if (spanishVoice) utterance.voice = spanishVoice;
        utterance.rate = 0.95;
        window.speechSynthesis.speak(utterance);
    }

    function stopSpeaking() {
        speechGen++;
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    }

    voiceToggle && voiceToggle.addEventListener('change', () => {
        if (!voiceToggle.checked) {
            stopSpeaking();
            if (!busy && !finished && !countdownId && stepChat && !stepChat.classList.contains('d-none')) startCountdown();
        }
    });
});
