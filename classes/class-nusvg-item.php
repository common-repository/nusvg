<?php
if (!class_exists('Nusvg_Item')) {

    class Nusvg_Item {

        const pattern_parameter = '#\[\[([a-zA-Z0-9_]+):([^\]]+)\]\]#';

        public $slug = "";
        public $preproc = "";
        public $svg = null;
        public $vars = array();
        public $default_width = 0;
        public $default_height = 0;
        public $preview_width = 0;
        public $preview_height = 0;
        public $preview_background = "";
        public $preview_stripes = false;
        public $patterns = [];
        public $credits = "";

        public function __toString() {
            return $this->svg;
        }
 
        static function inject_parameter($html, $parameter, $value) {
            return preg_replace('#\[\[' . $parameter . ':([^\]]+)\]\]#', $value, $html);
        }

        static function inject_parameter_default_value($html) {
            return preg_replace_callback(self::pattern_parameter, function($matches) {
                return $matches[2];
            }, $html);
        }

        public function load() {
            $path = Nusvg::get_basedir('preproc') . '/' . $this->slug . '.svg';
            if (!file_exists($path)) {
                return false;
            }
            $this->preproc = file_get_contents($path);
        }

 
        function format($s) {
            echo $this->sformat($s);
        }
        function sformat($s) {
            return preg_replace_callback('#\$([a-zA-Z0-9_]+)#', function($matches) /* use ($vars) */ {
                if (!isset($this->vars[$matches[1]])) {
                    switch ($matches[1]) {
                        case 'slug' :
                            $this->vars['slug'] = $this->slug;
                            break;
                        case 'base64' :
                            $this->vars['base64'] = $this->get_base64();
                            break;
                        case 'url' :
                            $this->vars['url'] = Nusvg::get_url($this->slug);
                            break;
                        default :
                            // unknown var...
                            return $matches[1];
                    }
                }
                return $this->vars[$matches[1]];
            }, $s);
        }

        public function hydrate_vars() {
            if (preg_match_all(self::pattern_parameter, $this->preproc, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $this->vars[$match[1]] = $match[2];
                }
            }
        }

        public function process($user_vars = [], $args = []) {

            $this->hydrate_vars();

            $this->svg = $this->preproc;

            if (!empty($args)) {
                if (isset($args['patterns'])) {
                    $this->patterns = array_merge($this->patterns, $args['patterns']);
                }
            }
            if ($this->patterns) {
                if (strpos($this->svg, '</defs>') === false) {
                    $this->svg = str_replace('</svg>', '<defs></defs></svg>', $this->svg);
                }
                $patterns = implode('', $this->patterns);
                $this->svg = str_replace('</defs>', $patterns . '</defs>', $this->svg);
            }

            if (isset($user_vars['width']) && isset($user_vars['height']) && ($user_vars['width'] === true XOR $user_vars['height'] === true)) {
                // ratio needed
                $default_width = false;
                preg_match('#\[\[width:([^\]]+)\]\]#', $this->svg, $matches);
                if (is_array($matches) && isset($matches[1])) {
                    $default_width = floatval($matches[1]); // '20.5px' => 20.5
                }
                $default_height = false;
                preg_match('#\[\[height:([^\]]+)\]\]#', $this->svg, $matches);
                if (is_array($matches) && isset($matches[1])) {
                    $default_height = floatval($matches[1]); // '20.5px' => 20.5
                }
                if ($user_vars['width'] === true && $default_height) {
                    $user_vars['width'] = round(floatval($user_vars['height']) * $default_width / $default_height,2);
                }
                if ($user_vars['height'] === true && $default_width) {
                    $user_vars['height'] = round(floatval($user_vars['width']) * $default_height / $default_width,2);
                }
            }
            foreach ($user_vars as $key => $value) {
                // this is a parameter for replacing a value (ie "[[width:15px]]")
                $this->svg = preg_replace('#\[\[' . $key . ':([^\]]+)\]\]#', $value, $this->svg);
            }

            $this->svg = preg_replace(self::pattern_parameter, "$2", $this->svg); // keep default values for vars that were not in $user_vars
           $this->vars = array_merge($this->vars, $user_vars);
           
           // UNIQID_
           
           preg_match_all('#UNIQID_([a-zA-Z0-9_]+)#',$this->svg,$matches);
           if (!empty($matches[1])) {
               $matches[1]=array_unique($matches[1]);
               foreach ($matches[1] as $id) {
                $uniqid=uniqid();
                $this->svg=str_replace('UNIQID_'.$id,$uniqid.'_'.$id,$this->svg);
               }
           }
            }

        public function get_base64() {
            return trim(base64_encode(trim($this->svg)));
        }

        public function write() {
            file_put_contents($this->get_filename_preproc(), $this->preproc);
            file_put_contents($this->get_filename_svg(), $this->svg);
            file_put_contents($this->get_filename_credits(), $this->credits_html());
            Nusvg_Admin::update_concat_credits();
        }

        public function get_filename_preproc() {
            return Nusvg::get_basedir('preproc') . '/' . $this->slug . '.svg';
        }

        public function get_filename_svg() {
            return Nusvg::get_basedir('svg') . '/' . $this->slug . '.svg';
        }

        public function get_filename_credits() {
            return Nusvg::get_basedir('credits') . '/' . $this->slug . '.html';
        }

        public function credits_html() {
            if (trim($this->credits) == "") {
                return "";
            }
            return $this->preview() . $this->credits;
        }

        public function preview() {

            $width = 100;
            $height = 100;
            if (preg_match('#width=[\'"]([0-9]+)[\'"p]#', $this->preproc, $matches)) {
                $width = $matches[1];
            }
            if (preg_match('#height=[\'"]([0-9]+)[\'"p]#', $this->preproc, $matches)) {
                $height = $matches[1];
            }

            $stripe_size = ceil(($width + $height) / 50);
            if ($this->preview_stripes) {
                $style_bg = 'background: repeating-linear-gradient(-45deg, ' . $this->preview_background . ',' . $this->preview_background . ' ' . $stripe_size . 'px,#999 ' . ($stripe_size + 1) . 'px,#999 0px)';
            } else {
                $style_bg = 'background: ' . $this->preview_background;
            }

            $html = $this->preproc;

            $html = self::inject_parameter($html, 'width', $this->preview_width . 'px');
            $html = self::inject_parameter($html, 'height', $this->preview_height . 'px');
            $html = self::inject_parameter_default_value($html);

            return '<div style="box-sizing:content-box;width:' . $this->preview_width . 'px;height:' . $this->preview_height . 'px;' . $style_bg . ';">' . $html . '</div>';
        }

        public function get_parameters() {
            $parameters = array();
            if (preg_match_all(self::pattern_parameter, $this->preproc, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $parameters[$match[1]] = $match[2];
                }
            }
            return $parameters;
        }

        public function dump_parameters() {
            $parameters = $this->get_parameters();
            if ($parameters == false) {
                return;
            }
            ob_start();
            ?>
            <p><?php _e("Custom parameters") ?> : </p>
            <div class="nusvg-table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Parameter</th>
                            <th>Default value</th>
                            <th>Overwritten value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($parameters as $parameter => $value) {
  $default_value=$value;
                        $overwritten='';
                        if (isset($this->vars[$parameter]) && $this->vars[$parameter]!=$default_value) {
                            $overwritten='<b>'.$this->vars[$parameter].'</b>'; 
                        } else {
                            $default_value='<b>'.$default_value.'</b>';
                        }
                                                    
                            ?><tr>
                                <th><?php echo $parameter ?></th>
                                <td><?php echo $default_value ?></td>
                                <td><?php echo $overwritten ?></td>
                            
                            </tr><?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php
            return ob_get_clean();
        }
        
        function row2hydrate($row) {
            $this->slug = $row->slug;
            $this->preproc = $row->preproc;
            $this->default_width = $row->default_width;
            $this->default_height = $row->default_height;
            $this->preview_width = $row->preview_width;
            $this->preview_height = $row->preview_height;
            $this->preview_background = $row->preview_background;
            $this->preview_stripes = $row->preview_stripes;
            $this->credits = $row->credits;
        }

        function select() {
            global $wpdb;
            $row = $wpdb->get_row("SELECT * FROM `" . $wpdb->prefix . "nusvg_items` WHERE slug='" . addslashes($this->slug) . "';");
            if ($row == false) {
                return false;
            }
            $this->row2hydrate($row);
        }

        function insert() {
            global $wpdb;
            $wpdb->query("INSERT IGNORE INTO `" . $wpdb->prefix . "nusvg_items` SET slug='" . addslashes($this->slug) . "';");
            $this->write();
        }

        function delete() {
            global $wpdb;
            $wpdb->query("DELETE FROM `" . $wpdb->prefix . "nusvg_items` WHERE slug='" . addslashes($this->slug) . "';");
            $file_preproc = $this->get_filename_preproc();
            $file_svg = $this->get_filename_svg();
            $file_credits = $this->get_filename_credits();
            if (file_exists($file_preproc)) {
                unlink($file_preproc);
            }
            if (file_exists($file_svg)) {
                unlink($file_svg);
            }
            if (file_exists($file_credits)) {
                unlink($file_credits);
            }
            Nusvg_Admin::update_concat_credits();
        }

        function edition() {

            $this->post();
            ?>
            <form method="post" id="nusvg_item_editor">
                <button class="button button-primary button-large" type="submit" name="nusvg_save"><?php _e("Save") ?></button>

                <div class="table-wrapper">
                    <table>
                        <tbody>
                            <tr>
                                <th>slug</th>
                                <td><input type="hidden" name="slug" value="<?php echo $this->slug ?>" /><?php echo $this->slug ?></td>
                            </tr>
                            <tr>
                                <th>preproc</th>
                                <td><textarea style="width:100%;height:200px" name="preproc" placeholder="<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' width='[[width:20]]' height='[[height:20]]' fill='[[fill:#000]]'>...</svg>"><?php echo esc_html($this->preproc) ?></textarea></td>
                            </tr>
                            <tr>
                                <th>preview</th>
                                <td><?php
                                    $this->process();

                                    echo $this->dump_parameters();
                                    ?><h3><?php _e("Preview HTML", 'nusvg') . " CSS base64" ?></h3><?php
                                    echo $this->preview();

                                    $base64 = $this->get_base64();
                                    ?>
                                    <h3><?php _e("Preview base64 (background)", 'nusvg') . " CSS base64" ?></h3>
                                    <p style="border:1px dashed #999;width:<?php echo $this->preview_width /* ($width ? $width : 100) */ . 'px' ?>;height:<?php echo $this->preview_height /* ($height ? $height : 100) */ . 'px' ?>;background:<?php echo $this->preview_background ?> url(data:image/svg+xml;base64,<?php echo $base64 ?>) left top"></p>
                                    <p style="width:600px;overflow-wrap: break-word;">background:<?php echo $this->preview_background ?> url(data:image/svg+xml;base64,<b><?php echo $base64 ?></b>) left top <span style="color:#080">[length:<?php echo strlen($base64) ?>]</span></p>

                                </td>
                            </tr>
                            <tr>
                                <th>preview_width</th>
                                <td><input type="input" name="preview_width" value="<?php echo htmlspecialchars($this->preview_width, ENT_QUOTES) ?>" pattern="[0-9]+" /> (min. 18)</td>
                            </tr>
                            <tr>
                                <th>preview_height</th>
                                <td><input type="input" name="preview_height" value="<?php echo htmlspecialchars($this->preview_height, ENT_QUOTES) ?>"  pattern="[0-9]+" /> (min. 18)</td>
                            </tr>
                            <tr>
                                <th>preview_background</th>
                                <td><input type="input" name="preview_background" value="<?php echo htmlspecialchars($this->preview_background, ENT_QUOTES) ?>" /><input type="color" value="<?php echo htmlspecialchars($this->preview_background, ENT_QUOTES) ?>"> (6 digit format please : #ff8800)</td>
                            </tr>
                            <tr>
                                <th>preview_stripes</th>
                                <td><input type="checkbox" name="preview_stripes" <?php echo $this->preview_stripes ? ' checked ' : '' ?> /></td>
                            </tr>
                            <tr>
                                <th>credits</th>
                                <td><textarea style="width:100%" name="credits"><?php echo esc_html($this->credits) ?></textarea>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <button class="button button-primary button-large" type="submit" name="nusvg_save"><?php _e("Save") ?></button>
            </form>
            <?php
        }

        function post() {
            if (isset($_POST) && is_array($_POST)) {
                $_POST = array_map('stripslashes_deep', $_POST);
                $_POST = array_map('trim', $_POST);
            }
            if (
                    !isset($_POST['nusvg_save']) || !isset($_POST['slug']) || sanitize_key($_POST['slug']) != $this->slug || !isset($_POST['preproc']) || !isset($_POST['preview_width']) || !isset($_POST['preview_height']) || !isset($_POST['preview_background']) || !isset($_POST['credits'])
            ) {
                return;
            }

            
            $preview_width=(int)$_POST['preview_width'];
            $preview_height=(int)$_POST['preview_height'];

            if ($preview_width < 18) {
               $preview_width = 18;
            }
            if ($preview_height < 18) {
               $preview_height= 18;
            }
            
            $this->preproc = sanitize_textarea_field($_POST['preproc']);
            $this->preview_width =$preview_width;
            $this->preview_height = $preview_height;
            $this->preview_background =  sanitize_text_field($_POST['preview_background']);
            $this->credits =  sanitize_textarea_field($_POST['credits']);
            $this->preview_stripes = (int) isset($_POST['preview_stripes']);

            $parameters = $this->get_parameters();
            if (isset($parameters['width'])) {
                $this->default_width=floatval($parameters['width']);
            }
            if (isset($parameters['height'])) {
                $this->default_height=floatval($parameters['height']);
            }


            $this->update();
        }

        function update() {
            global $wpdb;
            $sql = "UPDATE `" . $wpdb->prefix . "nusvg_items` SET
            preproc ='" . addslashes($this->preproc) . "',
            default_width ='" . addslashes($this->default_width) . "',
            default_height ='" . addslashes($this->default_height) . "',
            preview_width ='" . addslashes($this->preview_width) . "',
            preview_height ='" . addslashes($this->preview_height) . "',
            preview_background ='" . addslashes($this->preview_background) . "',
            preview_stripes ='" . addslashes($this->preview_stripes) . "',
            credits ='" . addslashes($this->credits) . "'
WHERE slug='" . addslashes($this->slug) . "';";
            $wpdb->query($sql);
            $this->write();
        }

        function list_tr() {
            ?>
            <tr>
                <td><a href="<?php echo '?page=nusvg-item&slug=' . $this->slug ?>"><?php echo $this->slug ?></a></td>
                <td><?php echo $this->preview() ?></td>
                <td><?php echo $this->default_width . ' x ' . $this->default_height ?></td>
                <td><?php
                    if ($this->slug == '_nusvg_example') {
                        _e("Not deletable (used in doc)");
                    } else {
                        ?>
                        <form method="post" onsubmit="return confirm('<?php echo htmlspecialchars(__("Delete this item ?", 'nusvg'), ENT_QUOTES) ?>')">
                            <input type="hidden" name="slug" value="<?php echo htmlspecialchars($this->slug, ENT_QUOTES) ?>" />
                            <button type="submit" name="nusvg_item_delete"><?php _e("Delete") ?></button>
                        </form>
                        <?php
                    }
                    ?>
                </td>
            </tr>
            <?php
        }

    }

}