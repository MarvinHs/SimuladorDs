# 🏛️ Simulador de Entrevista Consular (DS-160)

Practica tu entrevista de visa de turismo/negocios (B1/B2) con un cónsul simulado por IA. Llenas un perfil parecido al formulario DS-160, y el cónsul te entrevista por texto o por voz. Al final recibes una decisión simulada con puntos fuertes, puntos débiles, recomendaciones y una puntuación de preparación.

> Herramienta de práctica. No está afiliada al Departamento de Estado de EE. UU. ni predice el resultado real de una entrevista.

## Funciones

- Perfil DS-160 por secciones, con campos de detalle que aparecen solo cuando hacen falta.
- Buscador de países y selección por etiquetas.
- Entrevista adaptativa: una pregunta a la vez, repreguntas ante contradicciones y un límite de turnos.
- Voz: dictado por micrófono (Chrome/Edge) y voz del cónsul (síntesis del navegador).
- Tarjeta de decisión final con puntuación, y botones para repetir la entrevista o editar el perfil.

## Requisitos

- Una API key gratuita de Groq: https://console.groq.com/keys
- Docker, **o bien** PHP 8.1+ con la extensión cURL (XAMPP, WAMP, Laragon…)

## Configuración

```bash
cp .env.example .env
# edita .env y coloca tu clave:  GROQ_API_KEY=gsk_...
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

| Variable | Obligatoria | Descripción |
|---|---|---|
| `GROQ_API_KEY` | Sí | Clave de la API de Groq |
| `GROQ_MODEL` | No | Modelo (por defecto `openai/gpt-oss-120b`) |
| `CURL_CA_BUNDLE` | No | Ruta a `cacert.pem` si cURL no encuentra certificados |

## Estructura

```
index.php                 Interfaz (formulario + chat)
assets/js/voice_engine.js Lógica del formulario, chat y voz
assets/css/style.css      Estilos
api/start.php             Valida el perfil y prepara la entrevista
api/chat.php              Envía cada respuesta al cónsul (IA)
src/InterviewPrompt.php   Lista blanca de campos y prompt del cónsul
src/AiConsulService.php   Cliente de la API de Groq
config/api_config.php     Configuración, sesión y utilidades
```

## Privacidad

Los datos del perfil y las respuestas se guardan solo en la sesión de PHP mientras dura la entrevista, y se envían a Groq para generar las preguntas. No se almacenan en base de datos.
