<?php

return [
    // General
    'title' => 'Gestión de publicaciones',
    'caption' => 'Descripción',
    'type' => 'Tipo',
    'status' => 'Estado',
    'scheduled_at' => 'Programado para',
    'published_at' => 'Publicado',
    'created_at' => 'Creado',
    'updated_at' => 'Actualizado',
    'media' => 'Medios',
    'instagram_account' => 'Cuenta de Instagram',
    'order' => 'Orden',

    // Post Types
    'type' => [
        'feed' => 'Publicación en el feed',
        'reel' => 'Reel',
        'story' => 'Historia',
        'carousel' => 'Carrusel',
        'feed_description' => 'Una publicación regular que aparece en tu feed',
        'reel_description' => 'Un video corto que aparece en Reels',
        'story_description' => 'Una publicación temporal que desaparece después de 24 horas',
        'carousel_description' => 'Una publicación con múltiples imágenes o videos',
    ],

    // Post Status
    'status' => [
        'draft' => 'Borrador',
        'scheduled' => 'Programado',
        'publishing' => 'Publicando',
        'published' => 'Publicado',
        'failed' => 'Fallido',
    ],

    // Messages
    'created_successfully' => 'Publicación creada exitosamente',
    'updated_successfully' => 'Publicación actualizada exitosamente',
    'deleted_successfully' => 'Publicación eliminada exitosamente',
    'scheduled_successfully' => 'Publicación programada exitosamente',
    'create_failed' => 'Error al crear la publicación',
    'update_failed' => 'Error al actualizar la publicación',
    'delete_failed' => 'Error al eliminar la publicación',
    'schedule_failed' => 'Error al programar la publicación',

    // Validation Messages
    'instagram_account_required' => 'Se requiere cuenta de Instagram',
    'instagram_account_not_found' => 'Cuenta de Instagram no encontrada',
    'instagram_account_access_denied' => 'No tienes acceso a esta cuenta de Instagram',
    'type_required' => 'Se requiere tipo de publicación',
    'invalid_type' => 'Tipo de publicación inválido',
    'caption_too_long' => 'La descripción no debe exceder 2200 caracteres',
    'scheduled_time_must_be_future' => 'El tiempo programado debe ser en el futuro',
    'media_required' => 'Se requiere al menos un archivo multimedia',
    'at_least_one_media' => 'Se requiere al menos un archivo multimedia',
    'too_many_media' => 'Demasiados archivos multimedia. Máximo :max permitidos para publicaciones de tipo :type',
    'media_type_required' => 'Se requiere tipo de medio',
    'invalid_media_type' => 'Tipo de medio inválido',
    'invalid_media_type_for_post' => 'Tipo de medio inválido :media_type para publicaciones de tipo :post_type',
    'file_required' => 'Se requiere archivo',
    'invalid_file_type' => 'Tipo de archivo inválido. Tipos permitidos: JPEG, PNG, GIF, WebP, MP4, MOV, AVI',
    'file_too_large' => 'El archivo es demasiado grande. Tamaño máximo 100MB',
    'unsupported_file_type' => 'Tipo de archivo no soportado',
    'invalid_image_file' => 'Archivo de imagen inválido',
    'invalid_video_file' => 'Archivo de video inválido',

    // Business Rules
    'cannot_edit_published' => 'No se pueden editar publicaciones publicadas',
    'cannot_delete_published' => 'No se pueden eliminar publicaciones publicadas',
    'cannot_schedule_published' => 'No se pueden programar publicaciones publicadas',
    'media_required_for_scheduling' => 'Se requieren medios para programar',

    // Actions
    'create_post' => 'Crear publicación',
    'edit_post' => 'Editar publicación',
    'delete_post' => 'Eliminar publicación',
    'schedule_post' => 'Programar publicación',
    'publish_now' => 'Publicar ahora',
    'save_draft' => 'Guardar borrador',
    'cancel' => 'Cancelar',
    'save' => 'Guardar',
    'delete' => 'Eliminar',
    'view' => 'Ver',
    'edit' => 'Editar',
    'back_to_posts' => 'Volver a publicaciones',
    'creating' => 'Creando...',
    'optional' => 'Opcional',
    'characters' => 'caracteres',
    'all_statuses' => 'Todos los estados',
    'all_types' => 'Todos los tipos',
    'search_posts' => 'Buscar publicaciones...',
    'untitled' => 'Sin título',
    'no_caption' => 'Sin descripción',
    'select_post_type' => 'Seleccionar tipo de publicación',
    'select_post_type_description' => 'Elige el tipo de contenido que quieres crear',
    'select_instagram_account' => 'Seleccionar cuenta de Instagram',
    'select_instagram_account_description' => 'Elige a qué cuenta de Instagram publicar',
    'select_account' => 'Seleccionar cuenta',
    'schedule_for_later' => 'Programar para después',
    'media_upload_description' => 'Sube imágenes o videos para tu publicación',
    'scheduling_description' => 'Elige cuándo publicar tu publicación',

    // Form Labels
    'post_details' => 'Detalles de la publicación',
    'media_upload' => 'Subida de medios',
    'scheduling' => 'Programación',
    'preview' => 'Vista previa',
    'add_media' => 'Agregar medios',
    'remove_media' => 'Eliminar medios',
    'reorder_media' => 'Reordenar medios',
    'select_instagram_account' => 'Seleccionar cuenta de Instagram',
    'enter_title' => 'Ingresa el título de la publicación (opcional)',
    'enter_caption' => 'Ingresa la descripción de la publicación',
    'select_schedule_time' => 'Seleccionar tiempo de programación',
    'publish_immediately' => 'Publicar inmediatamente',

    // Placeholders
    'title_placeholder' => 'Ingresa un título para tu publicación...',
    'caption_placeholder' => 'Escribe una descripción para tu publicación...',

    // Help Text
    'title_help' => 'Un título para tu publicación (opcional)',
    'caption_help' => 'Escribe una descripción para tu publicación. Puedes usar hashtags y menciones.',
    'media_help' => 'Sube imágenes o videos para tu publicación. Arrastra para reordenar.',
    'scheduling_help' => 'Programa tu publicación para ser publicada en un momento específico.',

    // Statistics
    'total_posts' => 'Total de publicaciones',
    'draft_posts' => 'Borradores',
    'scheduled_posts' => 'Programadas',
    'published_posts' => 'Publicadas',
    'failed_posts' => 'Fallidas',

    // Empty States
    'no_posts' => 'Aún no hay publicaciones',
    'no_posts_description' => 'Crea tu primera publicación para comenzar',
    'no_drafts' => 'No hay borradores',
    'no_scheduled' => 'No hay publicaciones programadas',
    'no_published' => 'No hay publicaciones publicadas',

    // Media
    'media' => [
        'image' => 'Imagen',
        'video' => 'Video',
        'upload' => 'Subir',
        'drag_drop' => 'Arrastra y suelta archivos aquí',
        'or_click' => 'o haz clic para seleccionar',
        'or_drag_drop' => 'o arrastra y suelta',
        'file_size' => 'Tamaño del archivo',
        'dimensions' => 'Dimensiones',
        'duration' => 'Duración',
        'remove' => 'Eliminar',
        'reorder' => 'Reordenar',
        'supported_formats' => 'PNG, JPG, GIF, WebP, MP4, MOV, AVI hasta 100MB',
        'add_more' => 'Agregar más archivos',
        'existing' => 'Existente',
        'uploading' => 'Subiendo...',
        'too_many_files' => 'Demasiados archivos. Máximo :max archivos permitidos.',
        'file_too_large' => 'El archivo :name es demasiado grande. Tamaño máximo :max.',
        'invalid_file_type' => 'El archivo :name tiene un tipo inválido :type.',
    ],
];
