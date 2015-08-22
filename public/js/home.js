jQuery(document).ready(function($) {
    
});


function qsearch() {
    var coption = document.getElementById("searchselect").selectedIndex;
    if (coption == 1) {
        var filter_option = "&type=bolt-extension";
    } else if (coption == 2) {
        var filter_option = "&type=bolt-theme";
    } else{
        var filter_option = "";
    }
    window.location = '/browse?q=' + document.getElementById('txtSearchbar').value + filter_option; return false;
}