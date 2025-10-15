<?php

return [
    // Page titles
    'title' => 'Cuentas de Instagram',
    'connect_title' => 'Conectar cuenta de Instagram',

    // Messages
    'no_accounts' => 'Aún no hay cuentas de Instagram conectadas',
    'connect_description' => 'Conecta tu cuenta de Instagram para comenzar a automatizar la publicación de contenido.',
    'connect_button' => 'Conectar cuenta de Instagram',

    // Success messages
    'connected_success' => '¡Cuenta de Instagram conectada exitosamente!',
    'disconnected_success' => 'Cuenta de Instagram desconectada exitosamente.',
    'synced_success' => 'Datos de la cuenta de Instagram sincronizados exitosamente.',

    // Error messages
    'not_configured' => 'La integración con Instagram aún no está configurada. Por favor, contacta con tu administrador para configurar las credenciales de la API de Instagram.',
    'no_active_company' => 'Necesitas tener una empresa activa para conectar cuentas de Instagram. Por favor, crea o selecciona una empresa primero.',
    'connection_failed' => 'No se pudo conectar la cuenta de Instagram. Por favor, inténtalo de nuevo.',
    'token_expired' => 'Tu token de Instagram ha expirado. Por favor, reconecta tu cuenta.',
    'sync_failed' => 'No se pudieron sincronizar los datos de la cuenta de Instagram.',
    'disconnect_failed' => 'No se pudo desconectar la cuenta de Instagram.',
    'dummy_credentials_warning' => '🔧 Instagram está en modo de desarrollo. Para conectar una cuenta real de Instagram, agrega las credenciales de tu aplicación de Instagram al archivo .env. Contacta a tu desarrollador o administrador para obtener ayuda.',

    // Account info
    'username' => 'Nombre de usuario',
    'followers' => 'Seguidores',
    'following' => 'Siguiendo',
    'posts' => 'Publicaciones',
    'status' => 'Estado',
    'connected_at' => 'Conectado',
    'last_synced' => 'Última sincronización',

    // Actions
    'sync' => 'Sincronizar',
    'disconnect' => 'Desconectar',
    'refresh' => 'Actualizar',
    'disconnect_confirm' => '¿Estás seguro de que quieres desconectar @:username? Puedes volver a conectarla en cualquier momento.',
    'disconnect_confirm_title' => '¿Desconectar cuenta de Instagram?',
    'disconnect_confirm_message' => '¿Estás seguro de que quieres desconectar {username}? Puedes reconectarla en cualquier momento.',
    'disconnect_button' => 'Sí, desconectar',
    'disconnect_cancel' => 'Cancelar',

    // Status
    'status_active' => 'Activo',
    'status_expired' => 'Expirado',
    'status_expiring_soon' => 'Por expirar',
    'status_error' => 'Error',

    // Account details
    'connected' => 'Conectado',
    'account_type' => 'Tipo de cuenta',
    'token_warning' => 'Tu token de acceso expirará pronto. Por favor, vuelve a conectar esta cuenta para continuar publicando.',
];
