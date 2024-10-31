<?php
if (!class_exists('Nusvg_Admin_Page_Documentation')) {

    class Nusvg_Admin_Page_Documentation {

        static function _display() {
            global $wpdb;
            ?>
            <h1><?php _e("Documentation") ?></h1>
            <?php
            if (Nusvg::slug_exists('_nusvg_example') == false) {
                ?><div class="error"><?php _e("Example SVG is missing, sorry.", 'nusvg'); ?></div><?php
                return;
            }
            ?>

            <div id="nusvg-doc">

                <h2><?php _e("Display SVG", 'nusvg') ?></h2>

                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example');</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example') ?></div>
                </div>                

                <h2><?php _e("Parameters", 'nusvg') ?></h2>

                <p><?php _e("You can declare <b>parameters</b> in your SVG file in order to dynamically customize your SVG.",'nusvg') ?></p>

                <h3><?php _e("Syntax", 'nusvg') ?></h3>

                <code>[[parameter_name:default_value]]</code>

                <h4><?php _e("Example",'nusvg') ?></h4>

                <code>&lt;svg xmlns="http://www.w3.org/2000/svg" width="<span class="nusvg-highlight">[[width:18px]]</span>" height="<span class="nusvg-highlight">[[height:18px]]</span>" viewBox="0 0 63 63"> &hellip; &lt;polygon points="0 0, 63 0, 63 63, 0 63, 0 9, 7 9, 7 54, 14 54, 14 33, 21 54, 28 54, 28 27, 35 27, 35 54, 56 54, 56 27, 49 27, 49 45, 42 45, 42 27, 28 27, 28 9, 35 9, 35 18, 42 18, 42 9, 49 9, 49 18, 56 18, 56 9, 21 9, 21 30, 14 9, 0 9, 0 0" fill="[[fill:#000]]">&lt;/polygon>&lt;/svg></code>

                <h3><?php _e("Use of parameters", 'nusvg') ?></h3>

                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example',['writing'=>'red']);</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example', ['writing' => 'red']) ?></div>
                </div>                

                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example',['bg'=>'blue,'width'=>40,'height'=>40]);</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example', ['bg' => 'blue','width'=>40,'height'=>40]) ?></div>
                </div>                

                <h2><?php _e("Retrieve values", 'nusvg') ?></h2>

                <p><?php printf(__("Method %s (or %s for echo) is useful.",'nusvg'),'<b>sformat()</b>','<b>format()</b>') ?></p>

                <div class="nusvg-table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th></th>
                                <th>Variable</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php _e("Any custom parameter", 'nusvg') ?></td>
                                <th>${parameter_name}</th>
                            </tr>
                            <tr>
                                <td>SVG id</td>
                                <th>$slug</th>
                            </tr>
                            <tr>
                                <td>URL</td>
                                <th>$url</th>
                            </tr>                           
                            <tr>
                                <td><?php _e("SVG in base64", 'nusvg') ?></td>
                                <th>$base64</th>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3><?php _e("Get URL", 'nusvg') ?></h3>    

                <div class="block">
                    <code>Nusvg::get('_nusvg_example')-><u>format</u>('$url'); // echo Nusvg::get('_nusvg_example')-><u>sformat</u>('$url');</code>
                    <div class="result"><?php Nusvg::get('_nusvg_example')->format('$url') ?></div>
                </div>                

                <h3><?php _e("Get SVG in base64", 'nusvg') ?></h3>

                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example')->sformat('$base64');</code>
                    <div class="result"><?php echo substr(Nusvg::get('_nusvg_example')->sformat('$base64'), 0, 80) . " &hellip;" ?></div>
                </div>    


                <h2><?php _e("Respect image ratio", 'nusvg') ?></h2>

                <p><?php printf(__("When changing default %s, you should give both width and height parameter, otherwise ratio won't be kept :",'nusvg'),'width/height') ?></p>
                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example','width'=>200]);</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example', ['width' => 200]) ?></div>
                </div>

                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example',['width'=>200,'height'=>200]);</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example', ['width' => 200, 'height' => 200]) ?></div>
                </div>                

                <p><?php printf(__("Use %s as value for automatic calculus :",'nusvg'),"<b>true</b>") ?></p>

                <div class="block">
                    <code>echo Nusvg::get('_nusvg_example',['width'=>200,'height'=>true]);</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example', ['width' => 200, 'height' => true]) ?></div>
                </div>


                <h2><?php _e("ID disambiguation",'nusvg') ?></h2>

                <p><?php printf(__("When using ID inside SVG, we should make them unique. This can be done by prefixing them with %s.",'nusvg'),"<b>UNIQID_</b>") ?></p>

                <div class="block">
                    <code>&lt;pattern id="UNIQID_losanges"></code>
                    <div class="result">&lt;pattern id="f651ze65f_losanges"></div>
                </div>

                <h2><?php _e("Dump") ?></h2>

                <p><?php printf(__("Need a summary of all parameters ? Use method %s !",'nusvg'),"<b>dump_parameters()</b>") ?></p>

                <div class="block">
                    <code>$svg = Nusvg::get('_nusvg_example', [
                        'bg' => '#000',
                        'writing' => 'url(#UNIQID_losanges)',
                        'width' => '100px',
                        'height' => '100px',
                        'pattern_width' => '10px',
                        'pattern_height' => '10px',
                        'losange_2' => 'gray',
                        'losange_3' => 'white',
                        'losange_4' => 'lightgreen'
                        ]);
                        echo $svg->dump_parameters();
                        echo $svg;</code>
                    <div class="result"><?php
                        $svg = Nusvg::get('_nusvg_example', [
                                    'bg' => 'url(#UNIQID_losanges)',
                                    'writing' => '#000',
                                    'width' => '100px',
                                    'height' => '100px',
                                    'pattern_width' => '10px',
                                    'pattern_height' => '10px',
                                    'losange_2' => 'gray',
                                    'losange_3' => 'white',
                                    'losange_4' => 'lightgreen'
                        ]);
                        echo $svg->dump_parameters();
                        echo $svg;
                        ?></div>
                </div>

                <h2><?php _e("inline SVG background", 'nusvg') ?></h2>             

                <div class="block">
                    <code> 
                        
                        $parameters=[
                        'writing'=>'<span class="nusvg-highlight">url(#UNIQID_circle)</span>',
                            'bg'=>'url(#UNIQID_losanges)',                            
                            'width' => 100,
                            'height' => true,
                            'pattern_width' => 7,
                            'pattern_height' => 7,
                            ];
                            <br />
                            <br />
                            $patterns=['patterns'=>['&lt;pattern id="<span class="nusvg-highlight">UNIQID_circle</span>" width="6px" height="6px" viewBox="0,0,10,10" patternUnits="userSpaceOnUse">&lt;rect cx="0px" cy="0px" width="10px" height="10px" fill="black" />&lt;circle cx="5px" cy="5px" r="4px" fill="#333"/>&lt;/pattern>']];
                        <br />
                        <br />
                        $svg = Nusvg::get('_nusvg_example', $parameters,$patterns);
                 <br />    
                 <br />    
                 echo esc_html($svg->sformat(
                                '&lt;div style="color:#fff;font-size:200%;width:' . ($svg->sformat('$width') * 3) . 'px;'
                                . 'height:' . ($svg->sformat('$height') * 3) . 'px;'
                                . 'background:url(data:image/svg+xml;base64,$base64) left top repeat;'
                                . '">&lt;lorem ipsum/div>')));
                     <br />   echo $svg->dump_parameters();</code>
                    <div class="result">
                        <?php
                        $svg = Nusvg::get('_nusvg_example', [
                            'writing'=>'url(#UNIQID_circle)',
                            'bg'=>'url(#UNIQID_losanges)',                            
                            'width' => 100,
                            'height' => true,
                            'pattern_width' => 7,
                            'pattern_height' => 7,
                        ],['patterns'=>['<pattern id="UNIQID_circle" width="6px" height="6px" viewBox="0,0,10,10" patternUnits="userSpaceOnUse"><rect cx="0px" cy="0px" width="10px" height="10px" fill="black" /><circle cx="5px" cy="5px" r="4px" fill="#333"/></pattern>']]);
                        echo esc_html($svg->sformat(
                                '<div style="color:#fff;font-size:200%;width:' . ($svg->sformat('$width') * 3) . 'px;'
                                . 'height:' . ($svg->sformat('$height') * 3) . 'px;'
                                . 'background:url(data:image/svg+xml;base64,$base64) left top repeat;'
                                . '">lorem ipsum</div>'));
                        echo $svg->dump_parameters();
                        ?></div>
                </div>
                

                <h2><?php _e("Using a shortcode instead of PHP", 'nusvg') ?></h2>
                
                <div class="block">
                    <code>[nusvg _nusvg_example]</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example') ?></div>
                </div>               
                
                <p><?php _e("Parameters in JSON format :", 'nusvg') ?></p>
                    
                <div class="block">
                    <code>[nusvg _nusvg_example {"width":100,"height":true,"bg":"#060"}]</code>
                    <div class="result"><?php echo Nusvg::get('_nusvg_example',array("width"=>100,"height"=>true,"bg"=>"#060")) ?></div>
                </div>                
                                
                
                <h2><?php _e("Image license list",'nusvg') ?></h2>
                
                <p><?php printf(__("If you specified credits for your images, you can easily get a credit list to display on your site.%s Thanks to a shortcode : %s Or thanks to a PHP method : %s",'nusvg'),"</p><p>","</p><code>[nusvg_credits]</code><p>","</p><code>Nusvg::get_concat_credits();</code>") ?></p>
                
            </div>
            <?php
        }

    }

}