<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Hábito</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .emoji {
            font-size: 60px;
            margin: 20px 0;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .habito-name {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin: 20px 0;
            text-align: center;
        }
        .description {
            font-size: 16px;
            color: #666;
            line-height: 1.6;
            margin: 20px 0;
            text-align: center;
        }
        .mensaje-personalizado {
            background-color: #f8f9ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
            font-style: italic;
            color: #555;
        }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            padding: 20px;
            background-color: #f8f9ff;
            border-radius: 8px;
        }
        .stat {
            text-align: center;
        }
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .cta-button {
            display: block;
            width: 80%;
            max-width: 300px;
            margin: 30px auto;
            padding: 16px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            text-align: center;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: transform 0.3s ease;
        }
        .cta-button:hover {
            transform: translateY(-2px);
        }
        .motivational {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background-color: #fff9e6;
            border-radius: 8px;
        }
        .motivational p {
            font-size: 18px;
            color: #ff6b6b;
            font-weight: 600;
            margin: 0;
        }
        .footer {
            background-color: #f8f9ff;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
            font-size: 14px;
            color: #888;
        }
        .footer a {
            color: #667eea;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>⏰ ¡Es hora de tu hábito!</h1>
            <div class="emoji">{{ $habitoEmoji }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            <p class="greeting">¡Hola, <strong>{{ $userName }}</strong>!</p>

            <div class="habito-name">{{ $habitoNombre }}</div>

            @if($habitoDescripcion)
                <p class="description">{{ $habitoDescripcion }}</p>
            @endif

            @if($mensajePersonalizado)
                <div class="mensaje-personalizado">
                    <strong>💬 Mensaje personalizado:</strong><br>
                    {{ $mensajePersonalizado }}
                </div>
            @endif

            <!-- Estadísticas -->
            @if($rachaActual > 0 || $rachaMaxima > 0)
                <div class="stats">
                    @if($rachaActual > 0)
                        <div class="stat">
                            <div class="stat-value">🔥 {{ $rachaActual }}</div>
                            <div class="stat-label">Racha Actual</div>
                        </div>
                    @endif
                    @if($rachaMaxima > 0)
                        <div class="stat">
                            <div class="stat-value">🏆 {{ $rachaMaxima }}</div>
                            <div class="stat-label">Racha Máxima</div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Mensaje motivacional -->
            <div class="motivational">
                @if($rachaActual > 0)
                    <p>¡Llevas {{ $rachaActual }} {{ $rachaActual === 1 ? 'día' : 'días' }} seguido! 💪 ¡No rompas la racha!</p>
                @else
                    <p>¡Hoy es un gran día para comenzar! 💪</p>
                @endif
            </div>

            <!-- Call to Action -->
            <a href="{{ config('app.url') }}/dashboard" class="cta-button">
                Marcar como Completado ✓
            </a>

            <p style="text-align: center; color: #888; font-size: 14px; margin-top: 30px;">
                Recuerda: la constancia es la clave del éxito. ¡Tú puedes! 🚀
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>KudosApp</strong> - Tu compañero de hábitos</p>
            <p>
                Si no quieres recibir estos recordatorios, 
                <a href="{{ config('app.url') }}/settings">actualiza tus preferencias</a>
            </p>
            <p style="margin-top: 20px; font-size: 12px;">
                © {{ date('Y') }} KudosApp. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
