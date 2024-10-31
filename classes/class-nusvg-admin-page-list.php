<?php
if (!class_exists('Nusvg_Admin_Page_List')) {

    class Nusvg_Admin_Page_List {

        static function _display() {
            global $wpdb;

            $placeholder="";

            if (isset($_POST['nusvg_item_delete']) && isset($_POST['slug'])) {
                $slug = sanitize_key($_POST['slug']);
                if (Nusvg::slug_exists($slug)) {
                    $Item = new Nusvg_Item;
                    $Item->slug = $slug;
                    $Item->delete();
                }
            }

            if (isset($_POST['nusvg_item_add']) && isset($_POST['nusvg_item_slug'])) {
                $slug = sanitize_key($_POST['nusvg_item_slug']);
                $placeholder=htmlspecialchars($slug, ENT_QUOTES);
                $msg_error = "";
                if (preg_match('#^[a-zA-Z0-9_\-]{2,50}$#', $slug) == false) {
                    $msg_error = __("Bad syntax", 'nusvg');
                } elseif (Nusvg::slug_exists($slug)) {
                    $msg_error = __("Slug already used");
                }
                if ($msg_error) {
                    echo '<div class="error">' . esc_html($msg_error) . '</div>';
                } else {
                    $Item = new Nusvg_Item;
                    $Item->slug = $slug;
                    $Item->insert();
                    ?><script>window.location.href = "<?php echo '?page=nusvg-item&slug=' . $Item->slug ?>";</script><?php
                }
            }
            
            
            
            ?>
            <h1><?php _e("List", 'nusvg') ?></h1>
            
            <form method="post">
                <?php _e("New item", 'nusvg') ?> : <input input="text" name="nusvg_item_slug" value="<?php echo $placeholder ?>" pattern="[a-zA-Z0-9_\-]{2,50}" srequired />
                <button class="button button-primary button-large" type="submit" name="nusvg_item_add"><?php _e('Add') ?></button> (<?php
                $min = 2;
                $max = 50;
                printf(__('Size between %d and %d characters ; alphanumeric + underscores + hyphens', 'nusvg'), $min, $max);
                ?>)
            </form>

            <?php
            $rows = $wpdb->get_results("SELECT * FROM `" . $wpdb->prefix . "nusvg_items` ORDER BY slug ASC;");
            ?>
            <div class="nucore table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>slug</th>
                            <th><?php _e("preview", 'nusvg') ?></th>
                            <th><?php _e("default size", 'nusvg') ?></th>
                            <th><?php _e("delete", 'nusvg') ?></th>                       
                        </tr>
                    </thead>
                    <tbody><?php
                        foreach ($rows as $row) {
                            $Item = new Nusvg_Item;
                            $Item->row2hydrate($row);
                            $Item->list_tr();
                        }
                        ?></tbody>
                </table>
            </div>
            <?php
        }

    }

}