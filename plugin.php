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
        if(isset($_POST['p_install']))
        {
            $error = 0;
            $f = file_put_contents("plugin_to_install.zip", fopen($_POST['p_install'], 'r'), LOCK_EX);
            
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
                            
                            global $plugins;
                            
                            include($path);
                            $Plugin = new $classname;
                            $plugins['all'][$pluginClass] = $Plugin;
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
                //case -3:die("Couldn't activate Plugin, please do it manualy (-3)");break;
                case -4:die("Couldn't extract plugin to ".PATH_PLUGINS.' (-4)');break;
                
                default:
                case -9:die("An unexpected error happend (-9)");break;
                    
                case -3;
                case 0:break;
            }
            
        }//end isset($_POST['install'])    
        
        else if(isset($_POST['p_delete'])){
            $path = PATH_PLUGINS.$_POST['p_delete'];
            Filesystem::deleteRecursive($path);
        }
        
        else if(isset($_POST['t_install']))
        {
            $error = 0;
            $f = file_put_contents("theme_to_install.zip", fopen($_POST['t_install'], 'r'), LOCK_EX);
            
            //file couldn't downloadet
            if($f === false) $error = -1;
               
            //try to open zip archive
            $zip = new ZipArchive;
            $res = $zip->open('theme_to_install.zip');
            if ($res === TRUE) 
            {
                //try to extract zip to plugins folder
                if($zip->extractTo(PATH_PLUGINS))
                {
                    if(!activateTheme($zip->getNameIndex($i)))$error = -3;
                   
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
                case -3:die("Couldn't activate theme, please do it manualy (-3)");break;
                case -4:die("Couldn't extract theme to ".PATH_THEMES.' (-4)');break;
                
                default:
                case -9:die("An unexpected error happend (-9)");break;
                    
                case 0:break;
            }
            
        }//end isset($_POST['t_install'])   
        else if(isset($_POST['t_delete'])){
            $path = PATH_THEMES.$_POST['t_delete'];
            Filesystem::deleteRecursive($path);
        }
    }

	// Method called on plugin settings on the admin area
	public function form()
	{
		global $L;
        
        $html .= $this->includeJS('script.js');
        
        $html .= '
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
                <div class="tab-pane fade show active" id="nav-plugins" role="tabpanel" aria-labelledby="nav-home-tab">'.$this->getPluginsUI().'</div>
                <div class="tab-pane fade" id="nav-themes" role="tabpanel" aria-labelledby="nav-profile-tab">'.$this->getThemesUI().'</div>
                <div class="tab-pane fade" id="nav-settings" role="tabpanel" aria-labelledby="nav-contact-tab">'.$this->getSettingsUI().'</div>
            </div>';
        
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
      
        global $L, $plugins;
        
        foreach($plugins['all'] as $plugin){

           
            //$pl_installed .= '<tr><td>'.$plugin.'</td><td><button name="uninstall" class="btn btn-danger my-2" type="submit" value="'.$plugin.'">'.$L->get('Delete').'</button></td></tr>';
            
            $buttons = "";
            //$buttons = '<button name="p_delete" class="btn btn-danger mb-2 mr-2" type="submit" value="'.$plugin->className().'">'.$L->get('Delete').'</button>';
            
            
            if ($plugin->installed()) 
            {
                if (method_exists($plugin, 'form')) 
                {
                    $buttons .= '<a class="btn btn-primary mb-2 mr-2 " href="'.HTML_PATH_ADMIN_ROOT.'configure-plugin/'.$plugin->className().'"><span class="oi oi-cog" style="font-size: 1em;top:2px;"></span></a>';
                }
                $buttons .=  '<a class="btn btn-primary mb-2" href="'.HTML_PATH_ADMIN_ROOT.'uninstall-plugin/'.$plugin->className().'">'.$L->g('Deactivate').'</a>';
            } else {
                $buttons .=  '<a class="btn btn-primary mb-2" href="'.HTML_PATH_ADMIN_ROOT.'install-plugin/'.$plugin->className().'">'.$L->g('Activate').'</a>';
            }
            
            
            
            
            $pl_installed .= '<tr id="'.$plugin->className().'" '.($plugin->installed()?'class="bg-light"':'').'>
                <td class="align-middle pt-3 pb-3"><div class="pla-name">'.$plugin->name().'</div>
                    <div class="mt-1">
                        '.$buttons.'
                    </div>
                </td>
                <td class="align-middle d-none d-sm-table-cell">
                    <div>'.$plugin->description().'</div>
                </td>
                <td class="text-center align-middle d-none d-lg-table-cell"><span>'.$plugin->version().'</span></td>
                <td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank" href="'.$plugin->website().'">'.$plugin->author().'</a></td>
                </tr>';
            
            
            
    
        }
        
        $html  .= '  
                   
                    
                            <div class="d-flex">
                                <input id="search-plugins" style="flex-grow:1;width:unset;" type="text" class="light-table-filter mt-2 mr-2" data-table="order-table" placeholder="Search for anything..">
                                <a style="color:white;" onclick="loadPlugins(true);$(\'#lpbb\').remove()" class="btn btn-primary mt-2">'.$L->get('Load plugins available').'</a>
                            </div>
                            <script>"use strict"; var LightTableFilter=function (Arr){var filterInput; function _onInputEvent(e){filterInput=e.target; var tables=document.getElementsByClassName(filterInput.getAttribute("data-table")); Arr.forEach.call(tables, function (table){Arr.forEach.call(table.tBodies, function (tbody){Arr.forEach.call(tbody.rows, _filter);});});}function _filter(row){var text=row.textContent.toLowerCase(), val=filterInput.value.toLowerCase(); row.style.display=text.indexOf(val)===-1 ? "none" : "table-row";}return{init: function init(){var inputs=document.getElementsByClassName("light-table-filter"); Arr.forEach.call(inputs, function (input){input.oninput=_onInputEvent;});}};}(Array.prototype); document.addEventListener("readystatechange", function (){if (document.readyState==="complete"){LightTableFilter.init();}}); </script>
                            
                            
                            <table id="plugins-table" class="table mt-1 order-table">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0 w-25" scope="col">Name</th>
                                        <th class="border-bottom-0 d-none d-sm-table-cell" scope="col">Description</th>
                                        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">Version</th>
                                        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">Author</th>
                                    </tr>
                                </thead>
                                <tbody>'.$pl_installed.'
                                </tbody>
                            </table>
                            <a id="lpbb" style="color:white;" onclick="loadPlugins();$(\'#lpbb\').remove()" class="btn btn-primary my-2">'.$L->get('Load plugins available').'</a>
                            
                       ';
        return $html .'
        

        
        
        ';
    }
    
    public function getThemesUI(){
         global $L, $themes;
      
       
        foreach($themes as $theme)
        {   
            //$pl_installed .= '<tr><td>'.$plugin.'</td><td><button name="uninstall" class="btn btn-danger my-2" type="submit" value="'.$plugin.'">'.$L->get('Delete').'</button></td></tr>';
            
            $buttons = "";
            //$buttons = '<button name="p_delete" class="btn btn-danger mb-2 mr-2" type="submit" value="'.$plugin->className().'">'.$L->get('Delete').'</button>';
            
            
            if (!$theme['dirname']==$site->theme()) 
            {
                $buttons .=  '<a class="btn btn-primary mb-2" href="'.HTML_PATH_ADMIN_ROOT.'install-theme/'.$theme['dirname'].'">'.$L->g('Activate').'</a>';
            }
            
            
            
            
            $th_installed .= '<tr id="'.$theme['dirname'].'" '.($theme['dirname']==$site->theme()?'class="bg-light"':'').'>
                <td class="align-middle pt-3 pb-3"><div class="pla-name">'.$theme->name().'</div>
                    <div class="mt-1">
                        '.$buttons.'
                    </div>
                </td>
                <td class="align-middle d-none d-sm-table-cell">
                    <div>'.$theme->description().'</div>
                </td>
                <td class="text-center align-middle d-none d-lg-table-cell"><span>'.$theme->version().'</span></td>
                <td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank" href="'.$theme->website().'">'.$theme->author().'</a></td>
                </tr>';
            
            
            
    
        }
        
        $html  .= '  
                   
                    
                            <div class="d-flex">
                                <input id="search-themes" style="flex-grow:1;width:unset;" type="text" class="light-table-filter mt-2 mr-2" data-table="order-table" placeholder="Search for anything..">
                                <a style="color:white;" onclick="loadThemes(true);$(\'#ltbb\').remove()" class="btn btn-primary mt-2">'.$L->get('Load plugins available').'</a>
                            </div>
                            <script>"use strict"; var LightTableFilter=function (Arr){var filterInput; function _onInputEvent(e){filterInput=e.target; var tables=document.getElementsByClassName(filterInput.getAttribute("data-table")); Arr.forEach.call(tables, function (table){Arr.forEach.call(table.tBodies, function (tbody){Arr.forEach.call(tbody.rows, _filter);});});}function _filter(row){var text=row.textContent.toLowerCase(), val=filterInput.value.toLowerCase(); row.style.display=text.indexOf(val)===-1 ? "none" : "table-row";}return{init: function init(){var inputs=document.getElementsByClassName("light-table-filter"); Arr.forEach.call(inputs, function (input){input.oninput=_onInputEvent;});}};}(Array.prototype); document.addEventListener("readystatechange", function (){if (document.readyState==="complete"){LightTableFilter.init();}}); </script>
                            
                            
                            <table id="themes-table" class="table mt-1 order-table">
                                <thead>
                                    <tr>
                                        <th class="border-bottom-0 w-25" scope="col">Name</th>
                                        <th class="border-bottom-0 d-none d-sm-table-cell" scope="col">Description</th>
                                        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">Version</th>
                                        <th class="text-center border-bottom-0 d-none d-lg-table-cell" scope="col">Author</th>
                                    </tr>
                                </thead>
                                <tbody>'.$th_installed.'
                                </tbody>
                            </table>
                            <a id="ltbb" style="color:white;" onclick="loadThemes();$(\'#ltbb\').remove()" class="btn btn-primary my-2">'.$L->get('Load plugins available').'</a>
                            
                       ';
        return $html .'
        

        
        
        ';
    }
    
    public function getSettingsUI(){
        return "";
    }
}