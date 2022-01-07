jQuery(window).on('load', function ()
{
    jQuery('#demo-htmlselect-basic').ddslick(
            {
                onSelected: function (data)
                {
                    displaySelectedData("Callback Function on Dropdown Selection", data);
                }
            });
    jQuery('#make-it-custom').on('click', function ()
    {
        jQuery('#demo-htmlselect').ddslick(
                {
                    onSelected: function (data)
                    {
                        displaySelectedData("Callback Function on Dropdown Selection", data);
                    }
                });
    });
    jQuery('#restore').on('click', function ()
    {
        jQuery('#demo-htmlselect').ddslick('destroy');
        jQuery('#dd-display-data').empty();
    });
    function displaySelectedData(demoIndex, ddData)
    {
        jQuery('#dd-display-data').html("<h3>Data from Demo " + demoIndex + "</h3>");
        jQuery('#dd-display-data').append('<strong>selectedIndex:</strong> ' + ddData.selectedIndex + '<br/><strong>selectedItem:</strong> Check your browser console for selected "li" html element');
        if (ddData.selectedData)
        {
            jQuery('#dd-display-data').append
                    (
                            '<br/><strong>Value:</strong>  ' + ddData.selectedData.value +
                            '<br/><strong>Description:</strong>  ' + ddData.selectedData.description +
                            '<br/><strong>ImageSrc:</strong>  ' + ddData.selectedData.imageSrc
                            );
        }
        jQuery('#country_id').val(ddData.selectedData.value);
    }
});