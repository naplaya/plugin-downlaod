$( document ).ready(function() {

    $("#search-plugins").on("keyup paste", function() {
        $("#search-themes").val($(this).val());
    });

    $("#search-themes").on("keyup paste", function() {
        $("#search-plugins").val($(this).val());
    });

});

var themesAlreadyLoaded = false;

function loadThemes(reload)
{
     reload = typeof reload !== 'undefined' ? reload : false;
    
    if(themesAlreadyLoaded && reload == false )return;
    else $('tr.theme-available').remove();
    
    $.get("https://api.github.com/repos/bludit/themes-repository/contents/items", function(data) {
        for (var i = 0; i < data.length; i++) {
            $.get("https://raw.githubusercontent.com/bludit/themes-repository/master/items/"+data[i].name+"/metadata.json", function(data) {
                var data = JSON.parse(data);
                var theme_name = data.name;
                var theme_version = data.version;
                var theme_download = data.download_url;
                if(data.download_url_v2 != ""){
                    var theme_download = data.download_url_v2;
                }
                var theme_information_url = data.information_url;
                var theme_description = data.description;
                var theme_author_username = data.author_username;

                var new_table_row = '<tr class="theme-available">'+
                '<td class="align-middle pt-3 pb-3"><div>'+theme_name+'</div><div class="mt-1"><button name="install" class="btn btn-primary my-2" type="submit" value="'+theme_download+'">Install</button></div></td>'+
                '<td class="align-middle d-none d-sm-table-cell"><div>'+theme_description+'</div><a href="'+theme_information_url+'" target="_blank">More information</a></td>'+
                '<td class="text-center align-middle d-none d-lg-table-cell"><span>'+theme_version+'</span></td>'+
                '<td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank">'+theme_author_username+'</a></td>'+
                '</tr>';

                $("#themes-table > tbody").append(new_table_row);
            });
        }
    });
      
    themesAlreadyLoaded = true;
}

var pluginsAlreadyLoaded = false;

function loadPlugins(reload)
{
    reload = typeof reload !== 'undefined' ? reload : false;
    
    if(pluginsAlreadyLoaded && reload == false )return;
    else $('tr.plugin-available').remove();

    var base_url = "https://api.github.com/repos/bludit/plugins-repository/contents/items";
    var base_meta_url = "https://raw.githubusercontent.com/bludit/plugins-repository/master/items/";

    var installed = new Map();
    $("#plugins-table .pla-name").each(function(index){
        installed.set($( this ).html(), true);    
        console.log($( this ).html())
    });
    
    $.get(base_url, function(data) 
    {
        for (var i = 0; i < data.length; i++) {
            $.get(base_meta_url+data[i].name+"/metadata.json", function(data) {
                var data = JSON.parse(data);
                var plugin_name = data.name;
                
                //console.log(data);
                
                //removes double entries between exixsting and available
                if(installed.has(plugin_name)) return;
                
                var plugin_version = data.version;
                var plugin_download = data.download_url;
                
                var plugin_download_v2 = data.download_url_v2;
                
                console.log(plugin_download);
                console.log(plugin_download_v2);
                
                var plugin_information_url = data.information_url;
                var plugin_description = data.description;
                var plugin_author_username = data.author_username;
                
            
                var actions ="";
                
                var file_ex = "zip";
                if(parseInt((data.price_usd)) > 0)
                {
                   actions += '<a class="btn btn-primary my-2" href="https://plugin.bludit.com">To buy plugins please visit bludit.com</a>';
                    //$install = '<a class="btn btn-primary my-2" href="'.$download.'">'.$L->g('Buy').' $'. $metadata->price_usd.'</a>';
                }else 
                {
                    if(typeof data.download_url !== 'undefined') actions += '<button name="p_install" class="btn btn-primary my-2 mr-2" type="submit" value="'+data.download_url+'">Install</button>';
                    if(typeof data.download_url_v2 !== 'undefined') actions += '<button name="p_install" class="btn btn-primary my-2" type="submit" value="'+data.download_url_v2+'">Install V2</button>';
                }
                
                //else
                //{
                    //actions = '<a class="btn btn-primary my-2" href="'+plugin_download+'">Go to Homepage</a>';
                //}

                //may it has a demo page
                $demo = "";
                if(data.demo_url != '') actions += '<a class="btn btn-primary my-2 ml-2" href="'+data.demo_url+'">Demo</a>';


                var new_table_row = '<tr class="plugin-available">'+
                '<td class="align-middle pt-3 pb-3"><div>'+plugin_name+'</div><div class="mt-1">'+actions+'</div></td>'+
                '<td class="align-middle d-none d-sm-table-cell"><div>'+plugin_description+'</div><a href="'+plugin_information_url+'" target="_blank">More information</a></td>'+
                '<td class="text-center align-middle d-none d-lg-table-cell"><span>'+plugin_version+'</span></td>'+
                '<td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank">'+plugin_author_username+'</a></td>'+
                '</tr>';

                $("#plugins-table > tbody").append(new_table_row);
            });
        }

    });
    pluginsAlreadyLoaded = true;
}