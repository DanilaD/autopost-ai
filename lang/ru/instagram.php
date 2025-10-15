<?php

return [
    // Page titles
    'title' => 'Аккаунты Instagram',
    'connect_title' => 'Подключить аккаунт Instagram',

    // Messages
    'no_accounts' => 'Аккаунты Instagram еще не подключены',
    'connect_description' => 'Подключите свой аккаунт Instagram, чтобы начать автоматизацию публикации контента.',
    'connect_button' => 'Подключить аккаунт Instagram',

    // Success messages
    'connected_success' => 'Аккаунт Instagram успешно подключен!',
    'disconnected_success' => 'Аккаунт Instagram успешно отключен.',
    'synced_success' => 'Данные аккаунта Instagram успешно синхронизированы.',

    // Error messages
    'not_configured' => 'Интеграция с Instagram еще не настроена. Пожалуйста, свяжитесь с администратором для настройки учетных данных Instagram API.',
    'no_active_company' => 'Для подключения аккаунтов Instagram необходима активная компания. Пожалуйста, создайте или выберите компанию.',
    'connection_failed' => 'Не удалось подключить аккаунт Instagram. Пожалуйста, попробуйте снова.',
    'token_expired' => 'Срок действия вашего токена Instagram истек. Пожалуйста, переподключите свой аккаунт.',
    'sync_failed' => 'Не удалось синхронизировать данные аккаунта Instagram.',
    'disconnect_failed' => 'Не удалось отключить аккаунт Instagram.',
    'dummy_credentials_warning' => '🔧 Instagram находится в режиме разработки. Чтобы подключить настоящий аккаунт Instagram, добавьте учетные данные вашего Instagram приложения в файл .env. Обратитесь к разработчику или администратору за помощью.',

    // Account info
    'username' => 'Имя пользователя',
    'followers' => 'Подписчики',
    'following' => 'Подписки',
    'posts' => 'Публикации',
    'status' => 'Статус',
    'connected_at' => 'Подключен',
    'last_synced' => 'Последняя синхронизация',

    // Actions
    'sync' => 'Синхронизировать',
    'disconnect' => 'Отключить',
    'refresh' => 'Обновить',
    'disconnect_confirm' => 'Вы уверены, что хотите отключить @:username? Вы можете переподключить его в любое время.',
    'disconnect_confirm_title' => 'Отключить аккаунт Instagram?',
    'disconnect_confirm_message' => 'Вы уверены, что хотите отключить {username}? Вы можете переподключить его в любое время.',
    'disconnect_button' => 'Да, отключить',
    'disconnect_cancel' => 'Отмена',

    // Status
    'status_active' => 'Активен',
    'status_expired' => 'Истек',
    'status_expiring_soon' => 'Скоро истечет',
    'status_error' => 'Ошибка',

    // Account details
    'connected' => 'Подключено',
    'account_type' => 'Тип аккаунта',
    'token_warning' => 'Ваш токен доступа скоро истечет. Пожалуйста, переподключите этот аккаунт для продолжения публикаций.',
];
