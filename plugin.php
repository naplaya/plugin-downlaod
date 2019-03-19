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
             $f = file_put_contents("my-zip.zip", fopen($_POST['install'], 'r'), LOCK_EX);
            if($f === false)
                die("Couldn't write to file.");

            $zip = new ZipArchive;
            $res = $zip->open('my-zip.zip');
            if ($res === TRUE) {
              $zip->extractTo(PATH_PLUGINS);
              $zip->close();
              //
            } else {
              //
            }
        }
        
        
        
        
        
        
        
        
        
        
      
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

        
        $opts = ['http' => ['method' => 'GET','header' => ['User-Agent: PHP']]];

        $context = stream_context_create($opts);
        $content = file_get_contents($base_url, false, $context);
        $plugins_available = json_decode($content);
        

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
        
    foreach($plugins_available as $item)
    {
        //print_r($item);
        break;

        //echo $item['name'];
    }

    foreach ($plugins_available as $item) 
    {
       
        $meta_file = file_get_contents(str_replace("<name>", $item->name, $base_meta_url), false, $context);
        $metadata = json_decode($meta_file);
        //print_r($metadata);
       // break;
        
        
        $download = "";
        if(!empty($metadata->download_url)) $download = $metadata->download_url;
        else $download = $metadata->download_url_v2;
        
        $demo = "";
        if(!empty($metadata->demo_url)) $demo = '<a href="'.$metadata->demo_url.'">'.$L->g("Demo").'</a>';
        
$line .= <<<EOF
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

$tbl_end = <<<EOF
        </tbody>
    </table>
EOF;

		return $html.$tbl_head.$line.$tbl_end;
	}
}