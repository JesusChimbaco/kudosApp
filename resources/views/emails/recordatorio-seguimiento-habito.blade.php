<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Recordatorio Urgente!</title>
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .urgent-badge {
            background-color: rgba(255, 255, 255, 0.2);
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 10px;
            font-weight: 600;
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
        .urgency-message {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-weight: 600;
        }
        .habito-name {
            font-size: 24px;
            font-weight: 700;
            color: #f5576c;
            margin: 20px 0;
            text-align: center;
        }
        .objetivo-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            margin: 30px 0;
            border-radius: 8px;
            text-align: center;
        }
        .objetivo-emoji {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .objetivo-title {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .objetivo-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .objetivo-description {
            font-size: 16px;
            opacity: 0.95;
            line-height: 1.5;
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
            width: 100%;
            max-width: 300px;
            margin: 30px auto;
            padding: 18px 30px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 4px 15px rgba(245, 87, 108, 0.3);
            transition: transform 0.2s;
        }
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 87, 108, 0.4);
        }
        .motivational {
            background-color: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 30px 0;
            border-radius: 4px;
            color: #155724;
            text-align: center;
            font-size: 16px;
            line-height: 1.6;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
        .footer p {
            margin: 5px 0;
        }
        .time-warning {
            background-color: #f8d7da;
            border: 2px solid #f5c6cb;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            color: #721c24;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 ¡Último Recordatorio!</h1>
            <div class="urgent-badge">⏰ Recordatorio de Seguimiento</div>
        </div>

        <div class="content">
            <div class="greeting">
                ¡Hola {{ $userName }}! 👋
            </div>

            <div class="urgency-message">
                ⚠️ Notamos que aún no has marcado tu hábito como completado. ¡Todavía estás a tiempo!
            </div>

            <div class="emoji">{{ $habitoEmoji }}</div>

            <div class="habito-name">
                {{ $habitoNombre }}
            </div>

            @if($habitoDescripcion)
                <div class="description">
                    {{ $habitoDescripcion }}
                </div>
            @endif

            @if($objetivoNombre)
                <div class="objetivo-section">
                    <div class="objetivo-emoji">{{ $objetivoEmoji }}</div>
                    <div class="objetivo-title">Recuerda tu objetivo</div>
                    <div class="objetivo-name">{{ $objetivoNombre }}</div>
                    @if($objetivoDescripcion)
                        <div class="objetivo-description">{{ $objetivoDescripcion }}</div>
                    @endif
                </div>
            @endif

            <div class="time-warning">
                ⏰ Ya pasaron 5 minutos desde el recordatorio anterior
            </div>

            @if($mensajePersonalizado)
                <div class="mensaje-personalizado">
                    💭 {{ $mensajePersonalizado }}
                </div>
            @endif

            @if($rachaActual > 0)
                <div class="stats">
                    <div class="stat">
                        <div class="stat-value">🔥 {{ $rachaActual }}</div>
                        <div class="stat-label">Racha Actual</div>
                    </div>
                    @if($rachaMaxima > $rachaActual)
                        <div class="stat">
                            <div class="stat-value">⭐ {{ $rachaMaxima }}</div>
                            <div class="stat-label">Racha Máxima</div>
                        </div>
                    @endif
                </div>
            @endif

            <div class="motivational">
                <strong>💪 ¡No pierdas tu racha!</strong><br>
                Cada pequeño paso cuenta para alcanzar tu objetivo. 
                @if($objetivoNombre)
                    Recuerda que esto te acerca a: <strong>{{ $objetivoNombre }}</strong>
                @endif
            </div>

            <a href="{{ config('app.url') }}/dashboard" class="cta-button">
                ✅ Marcar como Completado
            </a>
        </div>

        <div class="footer">
            <p><strong>Kudos - Tu compañero de hábitos</strong></p>
            <p>Este es un recordatorio de seguimiento automático.</p>
            <p>Si ya completaste tu hábito, puedes ignorar este mensaje.</p>
        </div>
    </div>
</body>
</html>
