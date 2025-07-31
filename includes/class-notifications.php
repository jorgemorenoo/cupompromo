<?php
/**
 * Sistema de Notificações
 *
 * @package Cupompromo
 * @version 1.0.0
 */

declare(strict_types=1);

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principal do sistema de notificações
 */
class Cupompromo_Notifications {

    /**
     * Tipos de notificação
     */
    const NOTIFICATION_TYPES = [
        'new_coupon' => 'Novo cupom disponível',
        'coupon_expiring' => 'Cupom expirando em breve',
        'store_update' => 'Atualização da loja',
        'price_drop' => 'Queda de preço detectada',
        'welcome' => 'Bem-vindo ao Cupompromo',
        'achievement' => 'Conquista desbloqueada',
        'system' => 'Notificação do sistema'
    ];

    /**
     * Canais de notificação
     */
    const CHANNELS = [
        'email' => 'Email',
        'push' => 'Push Notification',
        'in_app' => 'Notificação no site',
        'sms' => 'SMS'
    ];

    /**
     * Instância singleton
     */
    private static $instance = null;

    /**
     * Configurações de notificação
     */
    private $settings;

    /**
     * Construtor privado
     */
    private function __construct() {
        $this->settings = get_option('cupompromo_notification_settings', []);
        $this->init_hooks();
    }

    /**
     * Obtém instância singleton
     */
    public static function get_instance(): Cupompromo_Notifications {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa hooks
     */
    private function init_hooks(): void {
        // Notificações automáticas
        add_action('save_post_cupompromo_coupon', [$this, 'notify_new_coupon'], 10, 3);
        add_action('cupompromo_coupon_expiring_soon', [$this, 'notify_coupon_expiring']);
        add_action('cupompromo_price_drop_detected', [$this, 'notify_price_drop']);
        
        // Notificações de usuário
        add_action('user_register', [$this, 'send_welcome_notification']);
        add_action('cupompromo_user_achievement', [$this, 'notify_achievement']);
        
        // Agendamento de notificações
        add_action('cupompromo_send_scheduled_notifications', [$this, 'process_scheduled_notifications']);
        
        // Agenda processamento de notificações
        if (!wp_next_scheduled('cupompromo_send_scheduled_notifications')) {
            wp_schedule_event(time(), 'hourly', 'cupompromo_send_scheduled_notifications');
        }
    }

    /**
     * Envia notificação
     */
    public function send_notification(array $data): bool {
        $defaults = [
            'user_id' => 0,
            'type' => 'system',
            'title' => '',
            'message' => '',
            'channels' => ['email'],
            'data' => [],
            'scheduled_at' => null,
            'priority' => 'normal'
        ];

        $notification = wp_parse_args($data, $defaults);

        // Valida dados
        if (empty($notification['title']) || empty($notification['message'])) {
            return false;
        }

        // Salva notificação
        $notification_id = $this->save_notification($notification);

        if (!$notification_id) {
            return false;
        }

        // Envia imediatamente se não agendada
        if (!$notification['scheduled_at']) {
            return $this->process_notification($notification_id);
        }

        return true;
    }

    /**
     * Salva notificação no banco
     */
    private function save_notification(array $notification): int {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_notifications';

        $data = [
            'user_id' => $notification['user_id'],
            'type' => $notification['type'],
            'title' => $notification['title'],
            'message' => $notification['message'],
            'channels' => json_encode($notification['channels']),
            'data' => json_encode($notification['data']),
            'scheduled_at' => $notification['scheduled_at'],
            'priority' => $notification['priority'],
            'status' => 'pending',
            'created_at' => current_time('mysql'),
            'sent_at' => null
        ];

        $result = $wpdb->insert($table_name, $data);

        return $result ? $wpdb->insert_id : 0;
    }

    /**
     * Processa notificação
     */
    private function process_notification(int $notification_id): bool {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_notifications';
        $notification = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $notification_id
        ));

        if (!$notification) {
            return false;
        }

        $channels = json_decode($notification->channels, true);
        $success = true;

        foreach ($channels as $channel) {
            $method = 'send_' . $channel . '_notification';
            if (method_exists($this, $method)) {
                $result = $this->$method($notification);
                if (!$result) {
                    $success = false;
                }
            }
        }

        // Atualiza status
        $wpdb->update(
            $table_name,
            [
                'status' => $success ? 'sent' : 'failed',
                'sent_at' => current_time('mysql')
            ],
            ['id' => $notification_id]
        );

        return $success;
    }

    /**
     * Envia notificação por email
     */
    private function send_email_notification(object $notification): bool {
        $user = get_user_by('id', $notification->user_id);
        if (!$user) {
            return false;
        }

        $to = $user->user_email;
        $subject = $this->get_email_subject($notification);
        $message = $this->get_email_message($notification);
        $headers = $this->get_email_headers();

        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Envia notificação push
     */
    private function send_push_notification(object $notification): bool {
        // Implementação básica - pode ser expandida com Firebase, OneSignal, etc.
        $user_id = $notification->user_id;
        $subscription = $this->get_user_push_subscription($user_id);

        if (!$subscription) {
            return false;
        }

        // Aqui você implementaria a lógica específica do serviço de push
        // Por exemplo, Firebase Cloud Messaging ou OneSignal
        
        return true;
    }

    /**
     * Envia notificação no site
     */
    private function send_in_app_notification(object $notification): bool {
        // Salva notificação para exibição no frontend
        $user_notifications = get_user_meta($notification->user_id, 'cupompromo_notifications', true);
        if (!is_array($user_notifications)) {
            $user_notifications = [];
        }

        $user_notifications[] = [
            'id' => $notification->id,
            'type' => $notification->type,
            'title' => $notification->title,
            'message' => $notification->message,
            'data' => json_decode($notification->data, true),
            'created_at' => $notification->created_at,
            'read' => false
        ];

        // Mantém apenas as últimas 50 notificações
        if (count($user_notifications) > 50) {
            $user_notifications = array_slice($user_notifications, -50);
        }

        return update_user_meta($notification->user_id, 'cupompromo_notifications', $user_notifications);
    }

    /**
     * Envia notificação SMS
     */
    private function send_sms_notification(object $notification): bool {
        $user = get_user_by('id', $notification->user_id);
        if (!$user) {
            return false;
        }

        $phone = get_user_meta($user->ID, 'phone_number', true);
        if (!$phone) {
            return false;
        }

        // Implementação básica - pode ser expandida com Twilio, etc.
        $message = $this->get_sms_message($notification);
        
        // Aqui você implementaria a lógica específica do serviço SMS
        
        return true;
    }

    /**
     * Notifica novo cupom
     */
    public function notify_new_coupon(int $post_id, WP_Post $post, bool $update): void {
        if ($update || $post->post_status !== 'publish') {
            return;
        }

        $store_id = get_post_meta($post_id, '_store_id', true);
        $store = get_post($store_id);
        
        if (!$store) {
            return;
        }

        // Busca usuários que seguem esta loja
        $followers = $this->get_store_followers($store_id);
        
        foreach ($followers as $user_id) {
            $this->send_notification([
                'user_id' => $user_id,
                'type' => 'new_coupon',
                'title' => sprintf('Novo cupom da %s!', $store->post_title),
                'message' => sprintf(
                    'Um novo cupom foi adicionado para %s: %s',
                    $store->post_title,
                    $post->post_title
                ),
                'channels' => $this->get_user_channels($user_id),
                'data' => [
                    'coupon_id' => $post_id,
                    'store_id' => $store_id,
                    'coupon_title' => $post->post_title,
                    'store_name' => $store->post_title
                ]
            ]);
        }
    }

    /**
     * Notifica cupom expirando
     */
    public function notify_coupon_expiring(int $coupon_id): void {
        $coupon = get_post($coupon_id);
        $store_id = get_post_meta($coupon_id, '_store_id', true);
        $store = get_post($store_id);
        
        if (!$coupon || !$store) {
            return;
        }

        // Busca usuários que salvaram este cupom
        $saved_users = $this->get_coupon_saved_users($coupon_id);
        
        foreach ($saved_users as $user_id) {
            $this->send_notification([
                'user_id' => $user_id,
                'type' => 'coupon_expiring',
                'title' => 'Cupom expirando em breve!',
                'message' => sprintf(
                    'O cupom "%s" da %s expira em breve. Use-o agora!',
                    $coupon->post_title,
                    $store->post_title
                ),
                'channels' => $this->get_user_channels($user_id),
                'data' => [
                    'coupon_id' => $coupon_id,
                    'store_id' => $store_id,
                    'coupon_title' => $coupon->post_title,
                    'store_name' => $store->post_title
                ]
            ]);
        }
    }

    /**
     * Notifica queda de preço
     */
    public function notify_price_drop(array $data): void {
        $product_id = $data['product_id'];
        $old_price = $data['old_price'];
        $new_price = $data['new_price'];
        $store_id = $data['store_id'];

        // Busca usuários que monitoram este produto
        $monitoring_users = $this->get_product_monitoring_users($product_id);
        
        foreach ($monitoring_users as $user_id) {
            $this->send_notification([
                'user_id' => $user_id,
                'type' => 'price_drop',
                'title' => 'Queda de preço detectada!',
                'message' => sprintf(
                    'O preço do produto caiu de R$ %.2f para R$ %.2f',
                    $old_price,
                    $new_price
                ),
                'channels' => $this->get_user_channels($user_id),
                'data' => [
                    'product_id' => $product_id,
                    'old_price' => $old_price,
                    'new_price' => $new_price,
                    'store_id' => $store_id
                ]
            ]);
        }
    }

    /**
     * Envia notificação de boas-vindas
     */
    public function send_welcome_notification(int $user_id): void {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $this->send_notification([
            'user_id' => $user_id,
            'type' => 'welcome',
            'title' => 'Bem-vindo ao Cupompromo!',
            'message' => sprintf(
                'Olá %s! Bem-vindo ao Cupompromo. Aqui você encontra os melhores cupons de desconto.',
                $user->display_name
            ),
            'channels' => ['email'],
            'data' => [
                'user_name' => $user->display_name
            ]
        ]);
    }

    /**
     * Notifica conquista
     */
    public function notify_achievement(array $data): void {
        $user_id = $data['user_id'];
        $achievement = $data['achievement'];

        $this->send_notification([
            'user_id' => $user_id,
            'type' => 'achievement',
            'title' => 'Conquista desbloqueada!',
            'message' => sprintf('Parabéns! Você desbloqueou: %s', $achievement['name']),
            'channels' => $this->get_user_channels($user_id),
            'data' => [
                'achievement' => $achievement
            ]
        ]);
    }

    /**
     * Processa notificações agendadas
     */
    public function process_scheduled_notifications(): void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_notifications';
        
        $scheduled_notifications = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} 
             WHERE status = 'pending' 
             AND scheduled_at IS NOT NULL 
             AND scheduled_at <= %s",
            current_time('mysql')
        ));

        foreach ($scheduled_notifications as $notification) {
            $this->process_notification($notification->id);
        }
    }

    /**
     * Obtém seguidores de uma loja
     */
    private function get_store_followers(int $store_id): array {
        global $wpdb;

        $followers = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
             WHERE meta_key = 'cupompromo_followed_stores' 
             AND meta_value LIKE %s",
            '%' . $store_id . '%'
        ));

        return array_map('intval', $followers);
    }

    /**
     * Obtém usuários que salvaram um cupom
     */
    private function get_coupon_saved_users(int $coupon_id): array {
        global $wpdb;

        $users = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
             WHERE meta_key = 'cupompromo_saved_coupons' 
             AND meta_value LIKE %s",
            '%' . $coupon_id . '%'
        ));

        return array_map('intval', $users);
    }

    /**
     * Obtém usuários que monitoram um produto
     */
    private function get_product_monitoring_users(int $product_id): array {
        global $wpdb;

        $users = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} 
             WHERE meta_key = 'cupompromo_monitored_products' 
             AND meta_value LIKE %s",
            '%' . $product_id . '%'
        ));

        return array_map('intval', $users);
    }

    /**
     * Obtém canais de notificação do usuário
     */
    private function get_user_channels(int $user_id): array {
        $user_channels = get_user_meta($user_id, 'cupompromo_notification_channels', true);
        
        if (!is_array($user_channels)) {
            $user_channels = ['email']; // Padrão
        }

        return $user_channels;
    }

    /**
     * Obtém assinatura push do usuário
     */
    private function get_user_push_subscription(int $user_id): ?array {
        $subscription = get_user_meta($user_id, 'cupompromo_push_subscription', true);
        return is_array($subscription) ? $subscription : null;
    }

    /**
     * Gera assunto do email
     */
    private function get_email_subject(object $notification): string {
        $subject = $notification->title;
        
        // Adiciona prefixo do site
        $site_name = get_bloginfo('name');
        if ($site_name) {
            $subject = sprintf('[%s] %s', $site_name, $subject);
        }

        return $subject;
    }

    /**
     * Gera mensagem do email
     */
    private function get_email_message(object $notification): string {
        $template = $this->get_email_template($notification->type);
        $data = json_decode($notification->data, true);
        
        // Substitui placeholders
        $message = str_replace(
            ['{title}', '{message}', '{user_name}', '{site_name}'],
            [
                $notification->title,
                $notification->message,
                $this->get_user_name($notification->user_id),
                get_bloginfo('name')
            ],
            $template
        );

        return $message;
    }

    /**
     * Gera cabeçalhos do email
     */
    private function get_email_headers(): array {
        return [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        ];
    }

    /**
     * Gera mensagem SMS
     */
    private function get_sms_message(object $notification): string {
        return $notification->title . ': ' . $notification->message;
    }

    /**
     * Obtém template de email
     */
    private function get_email_template(string $type): string {
        $templates = [
            'welcome' => $this->get_welcome_email_template(),
            'new_coupon' => $this->get_coupon_email_template(),
            'coupon_expiring' => $this->get_expiring_email_template(),
            'price_drop' => $this->get_price_drop_email_template(),
            'achievement' => $this->get_achievement_email_template(),
            'default' => $this->get_default_email_template()
        ];

        return $templates[$type] ?? $templates['default'];
    }

    /**
     * Obtém nome do usuário
     */
    private function get_user_name(int $user_id): string {
        $user = get_user_by('id', $user_id);
        return $user ? $user->display_name : 'Usuário';
    }

    /**
     * Templates de email
     */
    private function get_welcome_email_template(): string {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h2>{title}</h2>
            <p>Olá {user_name}!</p>
            <p>{message}</p>
            <p>Explore nossos cupons e economize em suas compras!</p>
            <p>Atenciosamente,<br>Equipe {site_name}</p>
        </div>';
    }

    private function get_coupon_email_template(): string {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h2>{title}</h2>
            <p>{message}</p>
            <p>Não perca essa oportunidade!</p>
            <p>Atenciosamente,<br>Equipe {site_name}</p>
        </div>';
    }

    private function get_expiring_email_template(): string {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h2 style="color: #e74c3c;">{title}</h2>
            <p>{message}</p>
            <p>Use seu cupom antes que expire!</p>
            <p>Atenciosamente,<br>Equipe {site_name}</p>
        </div>';
    }

    private function get_price_drop_email_template(): string {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h2 style="color: #27ae60;">{title}</h2>
            <p>{message}</p>
            <p>Corra para aproveitar o melhor preço!</p>
            <p>Atenciosamente,<br>Equipe {site_name}</p>
        </div>';
    }

    private function get_achievement_email_template(): string {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h2 style="color: #f39c12;">{title}</h2>
            <p>{message}</p>
            <p>Continue assim para desbloquear mais conquistas!</p>
            <p>Atenciosamente,<br>Equipe {site_name}</p>
        </div>';
    }

    private function get_default_email_template(): string {
        return '
        <div style="max-width: 600px; margin: 0 auto; font-family: Arial, sans-serif;">
            <h2>{title}</h2>
            <p>{message}</p>
            <p>Atenciosamente,<br>Equipe {site_name}</p>
        </div>';
    }
} 