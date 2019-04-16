<?php

class extensionManager extends Plugin 
{    
 	public function init()
	{
        // Disable default Save and Cancel button
		$this->formButtons = false;
	}
    
    public function post()
    {
        if(isset($_POST['install']))
        {
            $error = 0;
            $f = file_put_contents("plugin_to_install.zip", fopen($_POST['install'], 'r'), LOCK_EX);
            
            //file couldn't downloadet
            if($f === false) $error = -1;
               
            //try to open zip archive
            $zip = new ZipArchive;
            $res = $zip->open('plugin_to_install.zip');
            if ($res === TRUE) 
            {
                //try to extract zip to plugins folder
                if($zip->extractTo(PATH_PLUGINS))
                {
                    //check each folder in root of zip if it contains plugins.php
                    for($i = 0; $i < $zip->numFiles; $i++) 
                    {   
                        $path = PATH_PLUGINS.DS.$zip->getNameIndex($i).'plugin.php';
                        if(file_exists($path))
                        { 
                            //get classname then acivate plugin
                            $classname = $this->getClassNameFromFile($path);
                            if(!activatePlugin($classname)) $error = -3;
                            break;
                        } 
                    }
                }else
                {
                    //plugin couldn't extracted
                    $error = -4;
                }
                
                //close Stream from zip archive
                $zip->close();
              

            } else 
            {
                //zip couldn't opend
                $error = -2;
            }
            
            //get error message from code
            switch($error)
            {
                case -1:die("Couldn't download file (-1)");break;
                case -2:die("Couldn't open zip archive (-2)");break;
                case -3:die("Couldn't activate Plugin, please do it manualy (-3)");break;
                case -4:die("Couldn't extract plugin to ".PATH_PLUGINS.' (-4)');break;
                
                default:
                case -9:die("An unexpected error happend (-9)");break;
                    
                case 0:break;
            }
            
        }//end isset($_POST['install'])                               
    }

	// Method called on plugin settings on the admin area
	public function form()
	{
		global $L;
        
        
        $html .= $this->includeJS('script.js');

        $plugins_ui  = $this->getPluginsUI();
        $themes_ui   = $this->getThemesUI();
        $settings_ui = $this->getSettingsUI();
        
        
        $html .= 
            '
            
            <div class="alert alert-primary" role="alert">
                <strong>Info: </strong> This plugin requires JS to be enabled
            </div>
            
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a class="nav-item nav-link active" id="nav-plugins-tab" data-toggle="tab" href="#nav-plugins" role="tab" aria-controls="nav-home" aria-selected="true">'.$L->get('Plugins').'</a>
                        <a class="nav-item nav-link" id="nav-themens-tab" data-toggle="tab" href="#nav-themes" role="tab" aria-controls="nav-profile" aria-selected="false">'.$L->get('Themes').'</a>
                        <a class="nav-item nav-link" id="nav-settings-tab" data-toggle="tab" href="#nav-settings" role="tab" aria-controls="nav-contact" aria-selected="false">'.$L->get('Settings').'</a>
                </div>
            </nav>
            <div class="tab-content" id="nav-tabContent">
                <div class="tab-pane fade show active" id="nav-plugins" role="tabpanel" aria-labelledby="nav-home-tab">'.$plugins_ui.'</div>
                <div class="tab-pane fade" id="nav-themes" role="tabpanel" aria-labelledby="nav-profile-tab">'.$themes_ui.'</div>
                <div class="tab-pane fade" id="nav-settings" role="tabpanel" aria-labelledby="nav-contact-tab">'.$settings_ui.'</div>
            </div>';
        /*

        //agent to download from api
        $opts = ['http' => ['method' => 'GET','header' => ['User-Agent: PHP']]];

        $context = stream_context_create($opts);
        $content = file_get_contents($base_url, false, $context);
        $plugins_available = json_decode($content);
        
//table head
$tbl_head = <<<EOF
    <table class="table  mt-3">
        <thead>
            <tr>
                <th class="border-bottom-0 w-25" scope="col">{$L->g('Name')}</th>
                <th class="border-bottom-0 d-none d-sm-table-cell" scope="col">{$L->g('Description')}</th>
                <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">{$L->g('Version')}</th>
                <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">{$L->g('Author')}</th>
            </tr>
        </thead>
        <tbody>
EOF;
   
    $i = 0;
    //create a row for each plugin from api
    foreach ($plugins_available as $item) 
    {
        $meta_file = file_get_contents(str_replace("<name>", $item->name, $base_meta_url), false, $context);
        $metadata = json_decode($meta_file);
        
            //where plugin can be downloaded and wich version
            $download = "";
            if(!empty($metadata->download_url)) $download = $metadata->download_url;
            else $download = $metadata->download_url_v2;
        
        $file_ex = "zip";
        if($metadata->price_usd >0)
        {
            $install = '<a class="btn btn-primary my-2" href="https://plugin.bludit.com">'.$L->g('To buy plugins please visit bludit.com').'</a>';
            //$install = '<a class="btn btn-primary my-2" href="'.$download.'">'.$L->g('Buy').' $'. $metadata->price_usd.'</a>';
        }else if(substr($download, -strlen($file_ex)) === $file_ex)
        {
            $install = '<button name="install" class="btn btn-primary my-2" type="submit" value="'.$download.'">'.$L->g('Install').'</button>';
        }else
        {
            $install = '<a class="btn btn-primary my-2" href="'.$download.'">'.$L->g('Go to Homepage').'</a>';
        }
        
        //may it has a demo page
        $demo = "";
        if(!empty($metadata->demo_url)) $demo = '<a href="'.$metadata->demo_url.'">'.$L->g("Demo").'</a>';
        
        
        
        
        
        
        //html for table row
$tbl_row .= <<<EOF
    <tr>
        <td class="align-middle pt-3 pb-3">
            <div>{$metadata->name}</div>
            <div class="mt-1">
                {$install}
            </div>
        </td>

        <td class="align-middle d-none d-sm-table-cell">
            <div>{$metadata->description}</div>
            <a href="{$metadata->information_url}" target="_blank">{$L->g('More information')}</a>
            {$demo}
        </td>

       <td class="text-center align-middle d-none d-lg-table-cell">
           <span>{$metadata->version}</span>
        </td>

       <td class="text-center align-middle d-none d-lg-table-cell">
            <a target="_blank">{$metadata->author_username}</a>
        </td>

    </tr>
EOF;
        
    }

        //close table tags, end of table
$tbl_end = <<<EOF
        </tbody>
    </table>
EOF;
*/
		return $html.$tbl_head.$tbl_row.$tbl_end;
	}
    
    // Add a link to this plugin to the adminSidebar
    public function adminSidebar(){
        //global $L;
        return '<li class="nav-item"><a class="nav-link" href="'.HTML_PATH_ADMIN_ROOT.'configure-plugin/extensionManager">'. 'Extension manager' .'</a></li>';
    }
    
    /**
    * get the class name form file path using token
    *
    * @param $filePathName
    *
    * @return  mixed
    */
    public function getClassNameFromFile($filePathName)
    {
        $php_code = file_get_contents($filePathName);

        $classes = array();
        $tokens = token_get_all($php_code);
        
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if ($tokens[$i - 2][0] == T_CLASS
                && $tokens[$i - 1][0] == T_WHITESPACE
                && $tokens[$i][0] == T_STRING
            ) {

                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }

        return $classes[0];
    }
    
    public function getPluginsUI(){
      
        global $L;
        
        $html  = '  <a class="btn btn-primary mt-2 mb-2" data-toggle="collapse" href="#collapsePluginsDownloaded" role="button" aria-expanded="false" aria-controls="collapsePluginsDownloaded">'.$L->get('Plugins already downloaded').'</a>
                   
                    
                    <div class="collapse" id="collapsePluginsDownloaded">
                        <div class="card card-body">
            
                            <input type="text" class="light-table-filter" data-table="order-table" placeholder="'.$L->get('Type to search...').'">
                            <script>"use strict"; var LightTableFilter=function (Arr){var filterInput; function _onInputEvent(e){filterInput=e.target; var tables=document.getElementsByClassName(filterInput.getAttribute("data-table")); Arr.forEach.call(tables, function (table){Arr.forEach.call(table.tBodies, function (tbody){Arr.forEach.call(tbody.rows, _filter);});});}function _filter(row){var text=row.textContent.toLowerCase(), val=filterInput.value.toLowerCase(); row.style.display=text.indexOf(val)===-1 ? "none" : "table-row";}return{init: function init(){var inputs=document.getElementsByClassName("light-table-filter"); Arr.forEach.call(inputs, function (input){input.oninput=_onInputEvent;});}};}(Array.prototype); document.addEventListener("readystatechange", function (){if (document.readyState==="complete"){LightTableFilter.init();}}); </script>
                            <table class="table mt-3 order-table">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0" scope="col">'.$L->get('Name').'</th>
                                        <th class="border-bottom-0" scope="col">'.$L->get('Action').'</th>
                                    </tr>
                                </thead>
                            <tbody>';
        
        $installedPlugins = glob(PATH_PLUGINS . '/*' , GLOB_ONLYDIR);
        foreach($installedPlugins as $plugin){
            $plugin = str_replace(PATH_PLUGINS.'/', '', $plugin);
            $currentPlugins = str_replace(PATH_PLUGINS, '', PLUGIN_DIR);
            if($plugin."\\" != $currentTheme){//don't allow to uninstall current theme
                $html .= '<tr><td>'.$plugin.'</td><td><button name="uninstall" class="btn btn-danger my-2" type="submit" value="'.$plugin.'">'.$L->get('Delete').'</button></td></tr>';
            }
        }
        
        $html .= '</tbody></table></div></div>';
        
        
        
        $html  .= '  <div><a onclick="loadPlugins()" class="btn btn-primary mt-2 mb-2" data-toggle="collapse" href="#collapsePluginsAvailable" role="button" aria-expanded="false" aria-controls="collapsePluginsAvailable">'.$L->get('Plugins available').'</a></div>
                   
                    
                    <div class="collapse" id="collapsePluginsAvailable">
                        <div class="card card-body">
                        
                            
                            <input type="text" class="light-table-filter" data-table="order-table" placeholder="Search for anything..">
                            <script>"use strict"; var LightTableFilter=function (Arr){var filterInput; function _onInputEvent(e){filterInput=e.target; var tables=document.getElementsByClassName(filterInput.getAttribute("data-table")); Arr.forEach.call(tables, function (table){Arr.forEach.call(table.tBodies, function (tbody){Arr.forEach.call(tbody.rows, _filter);});});}function _filter(row){var text=row.textContent.toLowerCase(), val=filterInput.value.toLowerCase(); row.style.display=text.indexOf(val)===-1 ? "none" : "table-row";}return{init: function init(){var inputs=document.getElementsByClassName("light-table-filter"); Arr.forEach.call(inputs, function (input){input.oninput=_onInputEvent;});}};}(Array.prototype); document.addEventListener("readystatechange", function (){if (document.readyState==="complete"){LightTableFilter.init();}}); </script>
                            
                            
                            <table id="plugins-available-table" class="table mt-3 order-table">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0 w-25" scope="col">Name</th>
                                        <th class="border-bottom-0 d-none d-sm-table-cell" scope="col">Description</th>
                                        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">Version</th>
                                        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">Author</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            
                            
                        </div>
                    </div>';
        return $html .'
        

        
        
        ';
    }
    
    public function getThemesUI(){
        return "";
    }
    
    public function getSettingsUI(){
        return "";
    }
}