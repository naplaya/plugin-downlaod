<?php
class pluginDownload extends Plugin{
    public function init(){
        $this->formButtons = false;
    }
    
    public function post(){
        if(isset($_POST['install'])){
            include './plugin-install.php';
        }
    }
    
    public function form(){
        global $L;
        ini_set('max_execution_time', 0);
        $html  = '<div class="alert alert-primary" role="alert">'.$this->description().'</div>';
        
        $opts = ['http' => ['method' => 'GET','header' => ['User-Agent: PHP']]];
        $context = stream_context_create($opts);
        $content = file_get_contents("https://api.github.com/repos/bludit/plugins-repository/contents/items", false, $context);
        $available_plugins = json_decode($content);
        
        $table_filter = '
        <input type="text" class="light-table-filter" data-table="order-table" placeholder="Search for names.." title="Type in a name">
        <script>
        "use strict";
        
        var LightTableFilter = function (Arr) {
            var filterInput;
            
            function _onInputEvent(e) {
                filterInput = e.target;
                var tables = document.getElementsByClassName(filterInput.getAttribute("data-table"));
                Arr.forEach.call(tables, function (table) {
                    Arr.forEach.call(table.tBodies, function (tbody) {
                        Arr.forEach.call(tbody.rows, _filter);
                    });
                });
            }
            
            function _filter(row) {
                var text = row.textContent.toLowerCase(),
                val = filterInput.value.toLowerCase();
                row.style.display = text.indexOf(val) === -1 ? "none" : "table-row";
            }
            
            return {
                init: function init() {
                    var inputs = document.getElementsByClassName("light-table-filter");
                    Arr.forEach.call(inputs, function (input) {
                        input.oninput = _onInputEvent;
                    });
                }
            };
        }(Array.prototype);
        
        document.addEventListener("readystatechange", function () {
            if (document.readyState === "complete") {
                LightTableFilter.init();
            }
        });
        </script>
        ';
        
        $table_head = '<table id="plugin-download-extension-table" class="table mt-3 order-table"><thead><tr>
        <th class="border-bottom-0 w-25" scope="col">'.$L->g('Name').'</th>
        <th class="border-bottom-0 d-none d-sm-table-cell" scope="col">'.$L->g('Description').'</th>
        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">'.$L->g('Version').'</th>
        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">'.$L->g('Author').'</th>
        </tr></thead><tbody>';
        
        foreach ($available_plugins as $available_plugin) {
            $meta_file = file_get_contents("https://raw.githubusercontent.com/bludit/plugins-repository/master/items/".$available_plugin->name."/metadata.json", false, $context);
            $metadata = json_decode($meta_file);
            
            $download = $metadata->download_url_v2;
            if(!empty($metadata->download_url)){
                $download = $metadata->download_url;
            }
            
            //if plugin has a demo page
            $demo = "";
            if(!empty($metadata->demo_url)){
                $demo = '<a href="'.$metadata->demo_url.'">'.$L->g("Demo").'</a>';
            }
            
            $table_row .= '
            <tr>
            <td class="align-middle pt-3 pb-3">
            <div>'.$metadata->name.'</div>
            <div class="mt-1">
            <button name="install" class="btn btn-primary my-2" type="submit" value="'.$download.'">'.$L->g('Install').'</button>
            </div>
            </td>
            
            <td class="align-middle d-none d-sm-table-cell">
            <div>'.$metadata->description.'</div>
            <a href="'.$metadata->information_url.'" target="_blank">'.$L->g('More information').'</a>
            '.$demo.'
            </td>
            <td class="text-center align-middle d-none d-lg-table-cell"><span>'.$metadata->version.'</span></td>
            <td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank">'.$metadata->author_username.'</a></td>
            </tr>
            ';
        }
        
        $table_end = "</tbody></table>";
        
        return $html.$table_filter.$table_head.$table_row.$table_end;
    }
    public function adminSidebar(){
        return '<li class="nav-item"><a class="nav-link" href="'.HTML_PATH_ADMIN_ROOT.'configure-plugin/pluginDownload">Plugin Downloader</a></li>';
    }
}