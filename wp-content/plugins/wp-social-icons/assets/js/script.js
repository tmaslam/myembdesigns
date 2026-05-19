jQuery(function($) {

    // Loads the color pickers
    $('.mg-color').wpColorPicker();

    jQuery('body').on('click', '.mg-add-new', function() {
        field = '<div class="mg-new-fields"><input type="text" name="mg-wpsi[socials][icon][]" class="mg-icon-picker" value=""><input type="text" name="mg-wpsi[socials][link][]" placeholder="Enter your url here" class="social_link" value=""><input type="button" value="Remove" class="mg-remove-field button"></div>';
        jQuery('.mg-social-fields').append(field);
        createIconpicker();
    });


    jQuery('body').on('click', '.mg-new-fields .button', function() {
        if (confirm('Do you really want to remove this social link?'))
            jQuery(this).parent().remove();
    });


    function createIconpicker() {
        var iconPicker = $('.mg-icon-picker').fontIconPicker({
                theme: 'fip-bootstrap'
            }),
            icomoon_json_icons = [],
            icomoon_json_search = [];
        // Get the JSON file
        $.ajax({
            url: mgwpsi.options_path + '/icons/selection.json',
            type: 'GET',
            dataType: 'json'
        })
            .done(function(response) {
                // Get the class prefix
                var classPrefix = response.preferences.fontPref.prefix;

                $.each(response.icons, function(i, v) {
                    // Set the source
                    icomoon_json_icons.push(classPrefix + v.properties.name);

                    // Create and set the search source
                    if (v.icon && v.icon.tags && v.icon.tags.length) {
                        icomoon_json_search.push(v.properties.name + ' ' + v.icon.tags.join(' '));
                    } else {
                        icomoon_json_search.push(v.properties.name);
                    }
                });

                setTimeout(function() {
                    // Set new fonts
                    iconPicker.setIcons(icomoon_json_icons, icomoon_json_search);

                }, 1000);
            })
            .fail(function() {
                // Show error message and enable
                alert('Failed to load the icons, Please check file permission.');
            });
    }
    createIconpicker();
});