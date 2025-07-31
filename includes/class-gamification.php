<?php
/**
 * Sistema de Gamificação
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
 * Classe principal do sistema de gamificação
 */
class Cupompromo_Gamification {

    /**
     * Níveis de usuário
     */
    const USER_LEVELS = [
        'bronze' => [
            'name' => 'Bronze',
            'min_points' => 0,
            'max_points' => 100,
            'color' => '#cd7f32',
            'icon' => '🥉',
            'benefits' => ['Acesso básico aos cupons']
        ],
        'silver' => [
            'name' => 'Prata',
            'min_points' => 101,
            'max_points' => 500,
            'color' => '#c0c0c0',
            'icon' => '🥈',
            'benefits' => ['Cupons exclusivos', 'Notificações prioritárias']
        ],
        'gold' => [
            'name' => 'Ouro',
            'min_points' => 501,
            'max_points' => 1000,
            'color' => '#ffd700',
            'icon' => '🥇',
            'benefits' => ['Acesso antecipado', 'Cupons premium', 'Suporte VIP']
        ],
        'platinum' => [
            'name' => 'Platina',
            'min_points' => 1001,
            'max_points' => 999999,
            'color' => '#e5e4e2',
            'icon' => '💎',
            'benefits' => ['Todos os benefícios', 'Cupons personalizados', 'Consultoria exclusiva']
        ]
    ];

    /**
     * Ações que geram pontos
     */
    const POINT_ACTIONS = [
        'coupon_click' => 5,
        'coupon_use' => 20,
        'store_follow' => 10,
        'review_submit' => 15,
        'referral_signup' => 50,
        'daily_login' => 5,
        'profile_complete' => 25,
        'achievement_unlock' => 100,
        'streak_7_days' => 50,
        'streak_30_days' => 200
    ];

    /**
     * Conquistas disponíveis
     */
    const ACHIEVEMENTS = [
        'first_coupon' => [
            'name' => 'Primeiro Cupom',
            'description' => 'Usou seu primeiro cupom',
            'points' => 50,
            'icon' => '🎫'
        ],
        'coupon_master' => [
            'name' => 'Mestre dos Cupons',
            'description' => 'Usou 50 cupons',
            'points' => 200,
            'icon' => '👑',
            'requirement' => ['type' => 'coupons_used', 'value' => 50]
        ],
        'savings_expert' => [
            'name' => 'Especialista em Economia',
            'description' => 'Economizou R$ 500 em compras',
            'points' => 300,
            'icon' => '💰',
            'requirement' => ['type' => 'total_savings', 'value' => 500]
        ],
        'loyal_customer' => [
            'name' => 'Cliente Fiel',
            'description' => 'Seguiu 10 lojas',
            'points' => 150,
            'icon' => '❤️',
            'requirement' => ['type' => 'stores_followed', 'value' => 10]
        ],
        'early_bird' => [
            'name' => 'Madrugador',
            'description' => 'Acessou o site por 7 dias consecutivos',
            'points' => 100,
            'icon' => '🌅',
            'requirement' => ['type' => 'login_streak', 'value' => 7]
        ],
        'social_butterfly' => [
            'name' => 'Borboleta Social',
            'description' => 'Compartilhou 20 cupons',
            'points' => 120,
            'icon' => '🦋',
            'requirement' => ['type' => 'coupons_shared', 'value' => 20]
        ]
    ];

    /**
     * Instância singleton
     */
    private static $instance = null;

    /**
     * Construtor privado
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Obtém instância singleton
     */
    public static function get_instance(): Cupompromo_Gamification {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Inicializa hooks
     */
    private function init_hooks(): void {
        // Tracking de ações
        add_action('cupompromo_coupon_clicked', [$this, 'track_coupon_click']);
        add_action('cupompromo_coupon_used', [$this, 'track_coupon_use']);
        add_action('cupompromo_store_followed', [$this, 'track_store_follow']);
        add_action('cupompromo_review_submitted', [$this, 'track_review_submit']);
        add_action('cupompromo_referral_completed', [$this, 'track_referral']);
        add_action('wp_login', [$this, 'track_daily_login']);
        
        // Verificação de conquistas
        add_action('cupompromo_points_awarded', [$this, 'check_achievements']);
        
        // Limpeza de dados antigos
        add_action('cupompromo_cleanup_gamification_data', [$this, 'cleanup_old_data']);
        
        // Agenda limpeza
        if (!wp_next_scheduled('cupompromo_cleanup_gamification_data')) {
            wp_schedule_event(time(), 'daily', 'cupompromo_cleanup_gamification_data');
        }
    }

    /**
     * Obtém dados do usuário
     */
    public function get_user_data(int $user_id): array {
        $points = (int) get_user_meta($user_id, 'cupompromo_points', true);
        $level = $this->get_user_level($points);
        $achievements = get_user_meta($user_id, 'cupompromo_achievements', true);
        $streak = $this->get_login_streak($user_id);
        
        if (!is_array($achievements)) {
            $achievements = [];
        }

        return [
            'user_id' => $user_id,
            'points' => $points,
            'level' => $level,
            'achievements' => $achievements,
            'login_streak' => $streak,
            'next_level' => $this->get_next_level($level),
            'progress_to_next' => $this->get_progress_to_next_level($points, $level),
            'total_achievements' => count($achievements),
            'available_achievements' => count(self::ACHIEVEMENTS)
        ];
    }

    /**
     * Obtém nível do usuário
     */
    public function get_user_level(int $points): string {
        foreach (self::USER_LEVELS as $level => $data) {
            if ($points >= $data['min_points'] && $points <= $data['max_points']) {
                return $level;
            }
        }
        
        return 'bronze';
    }

    /**
     * Obtém próximo nível
     */
    public function get_next_level(string $current_level): ?array {
        $levels = array_keys(self::USER_LEVELS);
        $current_index = array_search($current_level, $levels);
        
        if ($current_index === false || $current_index >= count($levels) - 1) {
            return null;
        }
        
        $next_level = $levels[$current_index + 1];
        return self::USER_LEVELS[$next_level];
    }

    /**
     * Calcula progresso para próximo nível
     */
    public function get_progress_to_next_level(int $points, string $current_level): float {
        $current_level_data = self::USER_LEVELS[$current_level];
        $next_level = $this->get_next_level($current_level);
        
        if (!$next_level) {
            return 100.0;
        }
        
        $current_range = $current_level_data['max_points'] - $current_level_data['min_points'];
        $user_progress = $points - $current_level_data['min_points'];
        
        return round(($user_progress / $current_range) * 100, 2);
    }

    /**
     * Adiciona pontos ao usuário
     */
    public function award_points(int $user_id, string $action, int $custom_points = null): bool {
        if (!isset(self::POINT_ACTIONS[$action])) {
            return false;
        }

        $points = $custom_points ?? self::POINT_ACTIONS[$action];
        $current_points = (int) get_user_meta($user_id, 'cupompromo_points', true);
        $new_points = $current_points + $points;

        $result = update_user_meta($user_id, 'cupompromo_points', $new_points);
        
        if ($result) {
            // Registra ação
            $this->log_action($user_id, $action, $points);
            
            // Dispara hook
            do_action('cupompromo_points_awarded', $user_id, $action, $points, $new_points);
            
            // Verifica se subiu de nível
            $old_level = $this->get_user_level($current_points);
            $new_level = $this->get_user_level($new_points);
            
            if ($old_level !== $new_level) {
                $this->handle_level_up($user_id, $old_level, $new_level);
            }
        }

        return $result;
    }

    /**
     * Registra ação do usuário
     */
    private function log_action(int $user_id, string $action, int $points): void {
        global $wpdb;

        $table_name = $wpdb->prefix . 'cupompromo_gamification_log';
        
        $wpdb->insert($table_name, [
            'user_id' => $user_id,
            'action' => $action,
            'points' => $points,
            'created_at' => current_time('mysql')
        ]);
    }

    /**
     * Trata subida de nível
     */
    private function handle_level_up(int $user_id, string $old_level, string $new_level): void {
        $user = get_user_by('id', $user_id);
        if (!$user) {
            return;
        }

        $new_level_data = self::USER_LEVELS[$new_level];
        
        // Envia notificação
        $notifications = Cupompromo_Notifications::get_instance();
        $notifications->send_notification([
            'user_id' => $user_id,
            'type' => 'achievement',
            'title' => 'Parabéns! Você subiu de nível!',
            'message' => sprintf(
                'Você alcançou o nível %s! %s',
                $new_level_data['name'],
                implode(', ', $new_level_data['benefits'])
            ),
            'channels' => ['email', 'in_app'],
            'data' => [
                'achievement_type' => 'level_up',
                'old_level' => $old_level,
                'new_level' => $new_level,
                'level_data' => $new_level_data
            ]
        ]);

        // Dispara hook
        do_action('cupompromo_user_level_up', $user_id, $old_level, $new_level);
    }

    /**
     * Verifica conquistas
     */
    public function check_achievements(int $user_id): void {
        $user_data = $this->get_user_data($user_id);
        $unlocked_achievements = $user_data['achievements'];
        
        foreach (self::ACHIEVEMENTS as $achievement_id => $achievement) {
            if (in_array($achievement_id, $unlocked_achievements)) {
                continue; // Já desbloqueada
            }
            
            if ($this->has_achievement_requirement($user_id, $achievement)) {
                $this->unlock_achievement($user_id, $achievement_id, $achievement);
            }
        }
    }

    /**
     * Verifica se usuário atende requisito da conquista
     */
    private function has_achievement_requirement(int $user_id, array $achievement): bool {
        if (!isset($achievement['requirement'])) {
            return false;
        }
        
        $requirement = $achievement['requirement'];
        $type = $requirement['type'];
        $value = $requirement['value'];
        
        switch ($type) {
            case 'coupons_used':
                return $this->get_coupons_used_count($user_id) >= $value;
                
            case 'total_savings':
                return $this->get_total_savings($user_id) >= $value;
                
            case 'stores_followed':
                return $this->get_stores_followed_count($user_id) >= $value;
                
            case 'login_streak':
                return $this->get_login_streak($user_id) >= $value;
                
            case 'coupons_shared':
                return $this->get_coupons_shared_count($user_id) >= $value;
                
            default:
                return false;
        }
    }

    /**
     * Desbloqueia conquista
     */
    private function unlock_achievement(int $user_id, string $achievement_id, array $achievement): void {
        $unlocked_achievements = get_user_meta($user_id, 'cupompromo_achievements', true);
        if (!is_array($unlocked_achievements)) {
            $unlocked_achievements = [];
        }
        
        $unlocked_achievements[] = $achievement_id;
        update_user_meta($user_id, 'cupompromo_achievements', $unlocked_achievements);
        
        // Adiciona pontos da conquista
        $this->award_points($user_id, 'achievement_unlock', $achievement['points']);
        
        // Envia notificação
        $notifications = Cupompromo_Notifications::get_instance();
        $notifications->send_notification([
            'user_id' => $user_id,
            'type' => 'achievement',
            'title' => 'Conquista Desbloqueada!',
            'message' => sprintf(
                'Parabéns! Você desbloqueou: %s - %s',
                $achievement['name'],
                $achievement['description']
            ),
            'channels' => ['email', 'in_app'],
            'data' => [
                'achievement_id' => $achievement_id,
                'achievement' => $achievement
            ]
        ]);
        
        // Dispara hook
        do_action('cupompromo_achievement_unlocked', $user_id, $achievement_id, $achievement);
    }

    /**
     * Obtém leaderboard
     */
    public function get_leaderboard(int $limit = 10): array {
        global $wpdb;
        
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, meta_value as points 
             FROM {$wpdb->usermeta} 
             WHERE meta_key = 'cupompromo_points' 
             ORDER BY CAST(meta_value AS UNSIGNED) DESC 
             LIMIT %d",
            $limit
        ));
        
        $leaderboard = [];
        foreach ($users as $user) {
            $user_data = get_userdata($user->user_id);
            if ($user_data) {
                $leaderboard[] = [
                    'user_id' => $user->user_id,
                    'name' => $user_data->display_name,
                    'points' => (int) $user->points,
                    'level' => $this->get_user_level((int) $user->points),
                    'avatar' => get_avatar_url($user->user_id, ['size' => 50])
                ];
            }
        }
        
        return $leaderboard;
    }

    /**
     * Tracking de ações
     */
    public function track_coupon_click(int $user_id, int $coupon_id): void {
        $this->award_points($user_id, 'coupon_click');
    }

    public function track_coupon_use(int $user_id, int $coupon_id): void {
        $this->award_points($user_id, 'coupon_use');
    }

    public function track_store_follow(int $user_id, int $store_id): void {
        $this->award_points($user_id, 'store_follow');
    }

    public function track_review_submit(int $user_id, int $review_id): void {
        $this->award_points($user_id, 'review_submit');
    }

    public function track_referral(int $user_id, int $referred_user_id): void {
        $this->award_points($user_id, 'referral_signup');
    }

    public function track_daily_login(int $user_id): void {
        $today = date('Y-m-d');
        $last_login = get_user_meta($user_id, 'cupompromo_last_daily_login', true);
        
        if ($last_login !== $today) {
            $this->award_points($user_id, 'daily_login');
            update_user_meta($user_id, 'cupompromo_last_daily_login', $today);
            
            // Verifica streak
            $this->check_login_streak($user_id);
        }
    }

    /**
     * Verifica streak de login
     */
    private function check_login_streak(int $user_id): void {
        $streak = $this->get_login_streak($user_id);
        
        if ($streak === 7) {
            $this->award_points($user_id, 'streak_7_days');
        } elseif ($streak === 30) {
            $this->award_points($user_id, 'streak_30_days');
        }
    }

    /**
     * Obtém streak de login
     */
    private function get_login_streak(int $user_id): int {
        $login_dates = get_user_meta($user_id, 'cupompromo_login_dates', true);
        if (!is_array($login_dates)) {
            return 0;
        }
        
        $streak = 0;
        $current_date = date('Y-m-d');
        
        for ($i = 0; $i < 30; $i++) {
            $check_date = date('Y-m-d', strtotime("-{$i} days"));
            if (in_array($check_date, $login_dates)) {
                $streak++;
            } else {
                break;
            }
        }
        
        return $streak;
    }

    /**
     * Métodos auxiliares para estatísticas
     */
    private function get_coupons_used_count(int $user_id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key = 'cupompromo_used_coupons'",
            $user_id
        ));
    }

    private function get_total_savings(int $user_id): float {
        $savings = get_user_meta($user_id, 'cupompromo_total_savings', true);
        return (float) $savings;
    }

    private function get_stores_followed_count(int $user_id): int {
        $followed_stores = get_user_meta($user_id, 'cupompromo_followed_stores', true);
        return is_array($followed_stores) ? count($followed_stores) : 0;
    }

    private function get_coupons_shared_count(int $user_id): int {
        global $wpdb;
        
        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->usermeta} 
             WHERE user_id = %d AND meta_key = 'cupompromo_shared_coupons'",
            $user_id
        ));
    }

    /**
     * Limpa dados antigos
     */
    public function cleanup_old_data(): void {
        global $wpdb;
        
        // Remove logs antigos (mais de 1 ano)
        $table_name = $wpdb->prefix . 'cupompromo_gamification_log';
        $wpdb->query(
            "DELETE FROM {$table_name} 
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
        );
    }

    /**
     * Obtém estatísticas do sistema
     */
    public function get_system_stats(): array {
        global $wpdb;
        
        $total_users = count_users()['total_users'];
        $total_points = $wpdb->get_var(
            "SELECT SUM(CAST(meta_value AS UNSIGNED)) 
             FROM {$wpdb->usermeta} 
             WHERE meta_key = 'cupompromo_points'"
        );
        
        $level_distribution = [];
        foreach (self::USER_LEVELS as $level => $data) {
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} 
                 WHERE meta_key = 'cupompromo_points' 
                 AND CAST(meta_value AS UNSIGNED) BETWEEN %d AND %d",
                $data['min_points'],
                $data['max_points']
            ));
            
            $level_distribution[$level] = (int) $count;
        }
        
        return [
            'total_users' => $total_users,
            'total_points' => (int) $total_points,
            'level_distribution' => $level_distribution,
            'average_points' => $total_users > 0 ? round($total_points / $total_users, 2) : 0
        ];
    }
} 