# 🏛️ Simulador de Entrevista Consular (DS-160)

Practica tu entrevista de visa de turismo/negocios (B1/B2) con un cónsul simulado por IA. Llenas un perfil parecido al formulario DS-160, y el cónsul te entrevista por texto o por voz. Al final recibes una decisión simulada con puntos fuertes, puntos débiles, recomendaciones y una puntuación de preparación.

> Herramienta de práctica. No está afiliada al Departamento de Estado de EE. UU. ni predice el resultado real de una entrevista.

## Funciones

- Perfil DS-160 por secciones, con campos de detalle que aparecen solo cuando hacen falta.
- **Perfil de ejemplo** de un clic para probar, y opción de **recordar tus datos** en el navegador.
- Buscador de países y selección por etiquetas.
- **4 niveles de cónsul:**
  - 😊 *Amable*: paciente, para la primera práctica.
  - 🧑‍💼 *Realista*: seco y con prisa; parte de la presunción de intención migratoria (214(b)).
  - 🧐 *Estricto*: verifica cifras y fechas contra tu perfil, pone preguntas trampa e interrumpe.
  - 🔥 *Implacable*: su postura por defecto es rechazar; presiona y repite preguntas para pillarte.
- **Modo presión:** cuenta regresiva para cada respuesta (30/20/15/10 s según el nivel). Si te quedas callado, el cónsul lo nota.
- El cónsul **interrumpe** si hablas de más (el límite de palabras depende del nivel).
- Informe final con **momento clave**, **respuestas que te perjudicaron** y cómo debiste responder.
- **Idioma:** entrevista en español o en inglés (el informe siempre en español).
- Entrevista adaptativa: una pregunta a la vez, repreguntas ante contradicciones y un límite de turnos.
- Voz: dictado por micrófono (Chrome/Edge) y voz del cónsul (síntesis del navegador).
- Cronómetro, pasos de progreso y tarjeta de decisión con puntuación /10.
- **Informe descargable** en PDF (desde el diálogo de impresión) con toda la conversación.
- **Varias IAs con respaldo automático:** Gemini, Groq, Claude u OpenAI.

## ¿Qué IA usar?

| Proveedor | Costo | Ventaja |
|---|---|---|
| **Google Gemini** (recomendado) | Gratis con límites | Muy buen español, rápido |
| **Groq** | Gratis con límites | El más rápido; buen respaldo |
| **Claude (Anthropic)** | De pago | Conversación más natural |
| **OpenAI** | De pago | Alternativa conocida |

Puedes poner varias claves: la app usa la primera disponible y, si falla, pasa sola a la siguiente. Un proveedor que falló se deja en espera 2 minutos para no hacer esperar al usuario.

## Requisitos

- Al menos una API key: [Gemini](https://aistudio.google.com/apikey) o [Groq](https://console.groq.com/keys) (ambas gratis)
- Docker, **o bien** PHP 8.1+ con la extensión cURL (XAMPP, WAMP, Laragon…)

## Configuración

```bash
cp .env.example .env
# edita .env y coloca al menos una clave, por ejemplo:
#   GEMINI_API_KEY=...
#   GROQ_API_KEY=...
```

⚠️ **Nunca escribas la clave en el código ni subas `.env` a GitHub.** El `.gitignore` ya lo excluye.

### Con Docker

```bash
docker compose up -d --build
```

Abre http://localhost:8080

### Con XAMPP / PHP local

1. Copia la carpeta del proyecto dentro de `htdocs`.
2. Crea el `.env` como se indica arriba (la app lo lee sola).
3. Abre `http://localhost/SimuladorDs/`.

Si aparece un error SSL de cURL en Windows, descarga [cacert.pem](https://curl.se/ca/cacert.pem) y define `CURL_CA_BUNDLE` en `.env` con su ruta.

Para pruebas rápidas sin Apache: `php -S localhost:8000`.

## Variables de entorno

| Variable | Descripción |
|---|---|
| `GEMINI_API_KEY` | Clave de Google Gemini |
| `GROQ_API_KEY` | Clave de Groq |
| `ANTHROPIC_API_KEY` | Clave de Claude (opcional) |
| `OPENAI_API_KEY` | Clave de OpenAI (opcional) |
| `AI_PROVIDER` | Proveedor preferido: `gemini`, `groq`, `anthropic` u `openai` |
| `GEMINI_MODEL`, `GROQ_MODEL`, `ANTHROPIC_MODEL`, `OPENAI_MODEL` | Cambiar el modelo de cada proveedor |
| `CURL_CA_BUNDLE` | Ruta a `cacert.pem` si cURL no encuentra certificados |

Para ver qué IA está activa, abre `/api/status.php` (nunca muestra las claves).

## Estructura

```
index.php                 Interfaz (formulario + chat)
assets/js/voice_engine.js Lógica del formulario, chat y voz
assets/css/style.css      Estilos
api/start.php             Valida el perfil y prepara la entrevista
api/chat.php              Envía cada respuesta al cónsul (IA)
api/status.php            Indica qué proveedores de IA están configurados
src/InterviewPrompt.php   Lista blanca de campos y prompt del cónsul
src/AiConsulService.php   Cliente multi-proveedor con respaldo automático
config/api_config.php     Configuración, sesión y utilidades
```

## Privacidad

Los datos del perfil y las respuestas se guardan solo en la sesión de PHP mientras dura la entrevista, y se envían al proveedor de IA configurado para generar las preguntas. Si activas "Recordar mis datos", el perfil se guarda solo en tu navegador (localStorage) y se borra con el botón "Limpiar". No se almacenan en base de datos.

## App de Android (Google Play)

El simulador también se publica como app de Android mediante una **Trusted Web Activity**: la app abre el sitio a pantalla completa, sin barra de direcciones, y cada cambio publicado en el sitio llega a la app sin pasar otra vez por Google Play.

- Código de la app: carpeta `android/` (paquete `com.marvinhs.simuladorvisa`).
- Vinculación app ↔ sitio: `/.well-known/assetlinks.json`.
- PWA: `manifest.webmanifest`, `sw.js`, `offline.html`, íconos en `assets/icons/`.
- Política de privacidad: `/privacidad.html`.

### Compilación automática

El flujo `.github/workflows/android.yml` compila la app en GitHub Actions cada vez que cambia la carpeta `android/` (o manualmente desde la pestaña **Actions → App Android → Run workflow**). Los archivos aparecen en **Releases**:

| Archivo | Para qué |
|---|---|
| `SimuladorVisa-N-play.aab` | Subir a Google Play Console |
| `SimuladorVisa-N.apk` | Instalar directamente en un teléfono |
| `SimuladorVisa-N-prueba.apk` | Prueba rápida (abre con barra de direcciones) |

Para firmar, el repositorio necesita dos secretos en **Settings → Secrets and variables → Actions**:

- `KEYSTORE_BASE64`: el archivo de la llave de firma codificado en base64.
- `KEYSTORE_PASSWORD`: su contraseña.

⚠️ La llave de firma **nunca** se sube al repositorio. Guárdala en un lugar seguro: sin ella no podrás publicar actualizaciones con la misma llave.

### Después de subir la app a Google Play

Google Play vuelve a firmar la app con su propia llave (*Play App Signing*). Copia la huella **SHA-256** que aparece en Play Console → *Integridad de la app* → *Firma de apps* y agrégala a `sha256_cert_fingerprints` en `/.well-known/assetlinks.json`. Sin ese paso, la app instalada desde Play se abrirá con barra de direcciones.
