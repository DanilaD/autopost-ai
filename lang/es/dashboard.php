<?php

return [
    'title' => 'Panel de control',
    'greeting' => [
        'morning' => 'Buenos días',
        'afternoon' => 'Buenas tardes',
        'evening' => 'Buenas noches',
    ],
    // Mensajes de bienvenida rotativos - se muestran aleatoriamente en el panel
    'welcome_messages' => [
        'Bienvenido a '.config('app.name').'. Hagamos algo increíble hoy.',
        '¿Listo para automatizar tus redes sociales? ¡Comencemos!',
        'Tu contenido merece brillar. Hagámoslo realidad.',
        'Es hora de convertir tus ideas en publicaciones atractivas.',
        'El gran contenido comienza aquí. ¿Qué crearás hoy?',
        'Crezcamos juntos tu presencia en Instagram.',
        '¡Bienvenido de nuevo! Tu audiencia espera tu próxima publicación.',
    ],
    'stats' => [
        'instagram_accounts' => 'Cuentas de Instagram',
        'scheduled_posts' => 'Publicaciones programadas',
        'wallet_balance' => 'Saldo de la cartera',
    ],
    'actions' => [
        'connect_instagram' => 'Conectar Instagram',
        'connect_instagram_desc' => 'Vincula tu cuenta de Instagram para comenzar a automatizar tu contenido.',
        'connect_now' => 'Conectar ahora',
        'create_post' => 'Crear publicación',
        'create_post_desc' => 'Programa y publica contenido en tus cuentas de Instagram.',
        'coming_soon' => 'Próximamente',
    ],
    'empty_state' => [
        'no_posts' => 'Aún no hay publicaciones',
        'get_started' => 'Comienza conectando tu cuenta de Instagram y creando tu primera publicación.',
    ],
    'recent_posts' => 'Publicaciones Recientes',
    'view_all_posts' => 'Ver todas las publicaciones',
];
