<?php

class pluginDownload extends Plugin 
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
                        $path = PATH_PLUGINS.'/'.$zip->getNameIndex($i).'plugin.php';
                       // die($path);
                        if(file_exists($path))
                        {
                            //get plugin.php to activate it
                            $content_plugin_file = file_get_contents ($path);
                            
                            //regex to find class name
                            $re = '/(class) ([a-zA-Z-_])*( extends Plugin)/m';
                            preg_match($re, $content_plugin_file, $matches);

                            // Class name of the plugin
                            $className = explode(" ", $matches[0]); 
                            
                            //activate found class
                            if(!activatePlugin(trim($className[1]))) $error = -3;
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
        
        $base_url = "https://api.github.com/repos/bludit/plugins-repository/contents/items";
        $base_meta_url = "https://raw.githubusercontent.com/bludit/plugins-repository/master/items/<name>/metadata.json";

		$html  = '<div class="alert alert-primary" role="alert">';
		$html .= $this->description();
		$html .= '</div>';

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
   
    //create a row for each plugin from api
    foreach ($plugins_available as $item) 
    {
       
        $meta_file = file_get_contents(str_replace("<name>", $item->name, $base_meta_url), false, $context);
        $metadata = json_decode($meta_file);
        
        //where plugin can be downloaded and wich version
        $download = "";
        if(!empty($metadata->download_url)) $download = $metadata->download_url;
        else $download = $metadata->download_url_v2;
        
        //may it has a demo page
        $demo = "";
        if(!empty($metadata->demo_url)) $demo = '<a href="'.$metadata->demo_url.'">'.$L->g("Demo").'</a>';
        
        //html for table row
$tbl_row .= <<<EOF
    <tr>
        <td class="align-middle pt-3 pb-3">
            <div>{$metadata->name}</div>
            <div class="mt-1">
                <button name="install" class="btn btn-primary my-2" type="submit" value="{$download}">{$L->g('Install')}</button>
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

		return $html.$tbl_head.$tbl_row.$tbl_end;
	}
}