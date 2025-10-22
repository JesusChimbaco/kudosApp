<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a KudosApp</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
            font-weight: 600;
        }
        .header .emoji {
            font-size: 48px;
            margin-bottom: 10px;
            display: block;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #667eea;
            margin-top: 0;
            font-size: 24px;
        }
        .content p {
            margin: 15px 0;
            color: #555;
        }
        .highlight {
            background-color: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 500;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .features {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
            margin: 25px 0;
        }
        .feature {
            display: flex;
            align-items: center;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        .feature .icon {
            font-size: 24px;
            margin-right: 12px;
        }
        .feature .text {
            flex: 1;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
            color: #777;
            font-size: 14px;
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
            <span class="emoji">🎉</span>
            <h1>¡Bienvenido a KudosApp!</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <h2>¡Hola, {{ $userName }}!</h2>
            
            <p>
                Nos alegra mucho que te hayas unido a nuestra comunidad. Tu cuenta ha sido creada exitosamente y ya puedes comenzar a crear hábitos saludables que transformarán tu vida.
            </p>

            <div class="highlight">
                <strong>📧 Email:</strong> {{ $userEmail }}
            </div>

            <p>
                Con KudosApp podrás:
            </p>

            <div class="features">
                <div class="feature">
                    <span class="icon">✅</span>
                    <div class="text">
                        <strong>Crear y seguir hábitos</strong> personalizados
                    </div>
                </div>
                <div class="feature">
                    <span class="icon">📊</span>
                    <div class="text">
                        <strong>Registrar tu progreso</strong> diario
                    </div>
                </div>
                <div class="feature">
                    <span class="icon">🔔</span>
                    <div class="text">
                        <strong>Recibir recordatorios</strong> automáticos
                    </div>
                </div>
                <div class="feature">
                    <span class="icon">🏆</span>
                    <div class="text">
                        <strong>Desbloquear logros</strong> según tus avances
                    </div>
                </div>
                <div class="feature">
                    <span class="icon">📈</span>
                    <div class="text">
                        <strong>Ver estadísticas</strong> de tu evolución
                    </div>
                </div>
            </div>

            <p style="text-align: center; margin-top: 30px;">
                <a href="{{ config('app.url') }}/login" class="button">
                    Ir a KudosApp →
                </a>
            </p>

            <p style="margin-top: 30px;">
                ¿Tienes preguntas? No dudes en contactarnos. Estamos aquí para ayudarte a alcanzar tus metas.
            </p>

            <p style="color: #999; font-size: 14px; margin-top: 30px;">
                <em>💡 Consejo: Empieza con hábitos pequeños y fáciles de mantener. La consistencia es clave para el éxito.</em>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>KudosApp</strong></p>
            <p>Tu compañero para construir mejores hábitos</p>
            <p style="margin-top: 15px;">
                <a href="{{ config('app.url') }}">Visitar sitio web</a>
            </p>
            <p style="margin-top: 20px; font-size: 12px; color: #999;">
                Este correo fue enviado porque te registraste en KudosApp.<br>
                Si no creaste esta cuenta, puedes ignorar este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
