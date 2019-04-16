/*$(function() 
  {
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

                var new_table_row = `<tr>
                <td class="align-middle pt-3 pb-3"><div>`+theme_name+`</div><div class="mt-1"><button name="install" class="btn btn-primary my-2" type="submit" value="`+theme_download+`">Install</button></div></td>
                <td class="align-middle d-none d-sm-table-cell"><div>`+theme_description+`</div><a href="`+theme_information_url+`" target="_blank">More information</a></td>
                <td class="text-center align-middle d-none d-lg-table-cell"><span>`+theme_version+`</span></td>
                <td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank">`+theme_author_username+`</a></td>
                </tr>`;

                $("#theme-download-extension-table-body").append(new_table_row);
            });
        }
    });
});*/

var pluginsAlreadyLoaded = false;

function loadPlugins(reload)
{
    reload = typeof reload !== 'undefined' ? reload : false;
    
    if(pluginsAlreadyLoaded && reload == false )return;
    else $('tr.plugin-available').remove();

    var base_url = "https://api.github.com/repos/bludit/plugins-repository/contents/items";
    var base_meta_url = "https://raw.githubusercontent.com/bludit/plugins-repository/master/items/";

    $.get(base_url, function(data) 
    {
        for (var i = 0; i < data.length; i++) {
            $.get(base_meta_url+data[i].name+"/metadata.json", function(data) {
                var data = JSON.parse(data);
                var plugin_name = data.name;
                var plugin_version = data.version;
                var plugin_download = data.download_url;
                if(data.download_url_v2 != ""){
                    var plugin_download = data.download_url_v2;
                }
                var plugin_information_url = data.information_url;
                var plugin_description = data.description;
                var plugin_author_username = data.author_username;

                var new_table_row = `<tr class="plugin-available">
                <td class="align-middle pt-3 pb-3"><div>`+plugin_name+`</div><div class="mt-1"><button name="install" class="btn btn-primary my-2" type="submit" value="`+plugin_download+`">Install</button></div></td>
                <td class="align-middle d-none d-sm-table-cell"><div>`+plugin_description+`</div><a href="`+plugin_information_url+`" target="_blank">More information</a></td>
                <td class="text-center align-middle d-none d-lg-table-cell"><span>`+plugin_version+`</span></td>
                <td class="text-center align-middle d-none d-lg-table-cell"><a target="_blank">`+plugin_author_username+`</a></td>
                </tr>`;

                $("#plugins-available-table > tbody").append(new_table_row);
            });
        }

    });
    pluginsAlreadyLoaded = true;
}