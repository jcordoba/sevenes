<?php
/**
 * Listado y búsqueda de miembros (mobile-first)
 * @package 7es-sabbath-school
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ss-members-wrapper">
    <form id="ss-member-search-form" class="ss-form ss-form-inline" autocomplete="off">
        <input type="text" name="search" id="ss-member-search" placeholder="<?php _e('Buscar miembro...','sabbathschool'); ?>" />
        <button type="submit" class="ss-btn ss-btn-primary"><?php _e('Buscar','sabbathschool'); ?></button>
        <a href="<?php echo esc_url(home_url('/sevenes-dashboard/members/add/')); ?>" class="ss-btn ss-btn-success"><?php _e('Nuevo','sabbathschool'); ?></a>
    </form>
    <div id="ss-member-feedback" class="ss-feedback"></div>
    <div id="ss-members-list" class="ss-members-list">
        <?php
        global $wpdb;
        $table_name = $wpdb->prefix . 'sapp_members';

        // Paging
        $items_per_page = 20; // Members per page
        $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $offset = ($current_page - 1) * $items_per_page;

        // Search
        $search_term = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $sql_where = "WHERE is_active = 1"; // Default to active members
        $query_params = [];

        if (!empty($search_term)) {
            $sql_where .= " AND (first_name LIKE %s OR last_name LIKE %s OR identification LIKE %s OR email LIKE %s)";
            $like_term = '%' . $wpdb->esc_like($search_term) . '%';
            array_push($query_params, $like_term, $like_term, $like_term, $like_term);
        }

        // Get total items for pagination
        $total_items_sql = "SELECT COUNT(id) FROM $table_name $sql_where";
        $total_items = $wpdb->get_var(empty($query_params) ? $total_items_sql : $wpdb->prepare($total_items_sql, $query_params));
        $total_pages = ceil($total_items / $items_per_page);

        // Get members for the current page
        $members_sql = "SELECT id, first_name, last_name, identification, email, role FROM $table_name $sql_where ORDER BY last_name ASC, first_name ASC LIMIT %d OFFSET %d";
        $final_query_params = array_merge($query_params, [$items_per_page, $offset]);
        $members = $wpdb->get_results($wpdb->prepare($members_sql, $final_query_params));

        if ($members) {
            echo '<table class="ss-table">';
            echo '<thead><tr><th>Nombre Completo</th><th>Identificación</th><th>Email</th><th>Rol</th><th>Acciones</th></tr></thead>';
            echo '<tbody>';
            foreach ($members as $member) {
                $edit_url = esc_url(add_query_arg(['sevenes_route' => 'members_edit', 'member_id' => $member->id], home_url('/sevenes-dashboard/members/edit/')));
                // For a direct link structure like /sevenes-dashboard/members/edit/MEMBER_ID/, you'd need a rewrite rule.
                // Using query vars for now is simpler without new rewrite rules.
                // If you have a rewrite for /members/edit/ then use: 
                // $edit_url = esc_url(home_url('/sevenes-dashboard/members/edit/' . $member->id . '/'));
                echo '<tr>';
                echo '<td>' . esc_html($member->first_name . ' ' . $member->last_name) . '</td>';
                echo '<td>' . esc_html($member->identification) . '</td>';
                echo '<td>' . esc_html($member->email) . '</td>';
                echo '<td>' . esc_html(ucfirst($member->role)) . '</td>';
                echo '<td><a href="' . $edit_url . '" class="ss-btn-action ss-btn-edit">Editar</a></td>'; // We'll add deactivate later
                echo '</tr>';
            }
            echo '</tbody>';
            echo '</table>';

            // Pagination links
            if ($total_pages > 1) {
                echo '<div class="ss-pagination">';
                $base_url = remove_query_arg('paged', 현재_URL_가져오기_함수_필요); // Need a function to get current URL without paged param
                // For simplicity, let's use a placeholder or assume current URL structure allows adding &paged=
                // A robust way is to parse $_SERVER['REQUEST_URI'] and rebuild it.
                // Or use WordPress functions if available in this context more easily.
                
                // Simple pagination (numbers)
                for ($i = 1; $i <= $total_pages; $i++) {
                    $page_link = add_query_arg('paged', $i); // Adds to current URL
                    if (!empty($search_term)) {
                         $page_link = add_query_arg('search', $search_term, $page_link);
                    }
                    if ($i == $current_page) {
                        echo '<span class="current">' . $i . '</span>';
                    } else {
                        echo '<a href="' . esc_url($page_link) . '">' . $i . '</a>';
                    }
                }
                echo '</div>';
            }
        } else {
            if (!empty($search_term)) {
                 echo '<p>No se encontraron miembros que coincidan con su búsqueda.</p>';
            } else {
                 echo '<p>No hay miembros registrados todavía.</p>';
            }
        }
        ?>
    </div>
    <div id="ss-member-form-modal" class="ss-modal" style="display:none;"></div>
</div>
