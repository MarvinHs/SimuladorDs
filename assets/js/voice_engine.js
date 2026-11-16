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

    let lastProfile = null;
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
            await startInterview(lastProfile);
        });
    }

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

    async function startInterview(profile) {
        btnStart && (btnStart.disabled = true);
        setStatus('<span class="spinner-border spinner-border-sm me-2"></span>Preparando la ventanilla consular...', 'primary');
        try {
            const { data } = await postJson('api/start.php', { perfil: profile });
            if (!data.success) {
                setStatus(escapeHtml(data.error || 'Error desconocido al procesar.'), 'danger');
                if (data.missing && data.missing[0] && $(data.missing[0])) $(data.missing[0]).focus();
                return;
            }
            maxTurns = data.maxTurns || maxTurns;
            setStatus('');
            resetChat();
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

    function resetChat() {
        stopSpeaking();
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

    async function sendMessage() {
        const text = userInput.value.trim();
        if (!text || busy || finished) return;
        stopSpeaking();
        appendMessage('user', text);
        userInput.value = '';
        await askConsul(text);
    }

    async function askConsul(message) {
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
                speak(data.reply);
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
        startInterview(lastProfile);
    });

    btnEdit && btnEdit.addEventListener('click', () => {
        if (busy) return;
        showForm();
    });

    function showForm() {
        stopSpeaking();
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
            <div class="decision-footer">Resultado simulado con fines de práctica. No predice la decisión real del consulado.</div>`;
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

    function speak(text) {
        if (!('speechSynthesis' in window) || !voiceToggle || !voiceToggle.checked) return;
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
        if (!spoken) return;

        const utterance = new SpeechSynthesisUtterance(spoken);
        utterance.lang = spanishVoice ? spanishVoice.lang : 'es-US';
        if (spanishVoice) utterance.voice = spanishVoice;
        utterance.rate = 0.95;
        window.speechSynthesis.speak(utterance);
    }

    function stopSpeaking() {
        if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    }

    voiceToggle && voiceToggle.addEventListener('change', () => {
        if (!voiceToggle.checked) stopSpeaking();
    });
});
