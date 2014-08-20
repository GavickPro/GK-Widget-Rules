/**
 *
 * -------------------------------------------
 * Script for the Widget Rules
 * -------------------------------------------
 *
 **/
 
"use strict";

// added event to open the widget rules wrapper (uses id from the back-end wihout the random ID at end).
jQuery(document).click(function (e) {
    if (jQuery(e.target).hasClass('gk_widget_rules_btn') && jQuery(e.target).hasClass('button')) {
        var wrap = jQuery(e.target).next('.gk_widget_rules_wrapper');

        if (wrap.hasClass('active')) {
            wrap.removeClass('active');
            wrap.find('.gk-widget-rules-visibility').val('0');
        } else {
            gk_widget_control_init(wrap);
            wrap.addClass('active');
            wrap.find('.gk-widget-rules-visibility').val('1');
        }
    }
});

// function to init form event
function gk_widget_control_init(selected_form) {
    var form = jQuery(selected_form);    

    if (form && form.attr('data-state') !== 'initialized') {
        form.attr('data-state', 'initialized');
        var firstSelect = form.find('select[name="gk_widget_rules_type"]');
        var select = form.find('.gk_widget_rules_form_select');
        var btn = form.find('.gk_widget_rules_form .gk_widget_rules_btn');
        var form_inputs = {};
        var form_inputs_names = ['page', 'post', 'category', 'category_descendant', 'tag', 'template', 'taxonomy', 'taxonomy_term', 'posttype', 'author'];
        jQuery.each(form_inputs_names, function (i, el) {
            form_inputs[el] = form.find('.gk_widget_rules_form_input_' + el).parent();
        });
        // hide unnecesary form
        if (firstSelect.children('option:selected').val() === 'all') {
            form.find('.gk_widget_rules_form').css('display', 'none');
        }
        // change event
        firstSelect.change(function () {
            var value = firstSelect.children('option:selected').val();
            var fieldset = form.find('.gk_widget_rules_form');

            if (value === 'all') {
                fieldset.css('display', 'none');
            } else {
                fieldset.css('display', 'block');
            }
        });
        // refresh the list
        gk_widget_control_refresh(form);
        // add onChange event to the selectbox
        select.change(function () {
            var value = select.children('option:selected').val();

            if (value === 'homepage' || value === 'page404' || value === 'search' || value === 'archive') {
                jQuery.each(form_inputs, function (i, el) {
                    el.css('display', 'none');
                });
            } else {
                jQuery.each(form_inputs_names, function (i, el) {
                	if(el !== 'taxonomy_term') {
	                    if (value.replace(':', '') !== el) {
	                        form_inputs[el].css('display', 'none');

	                        if(value.replace(':', '') !== 'taxonomy') {
	                        	form_inputs['taxonomy_term'].css('display', 'none');
	                        }
	                    } else {
	                        form_inputs[el].css('display', 'block');

	                        if(value.replace(':', '') === 'taxonomy') {
	                        	form_inputs['taxonomy_term'].css('display', 'block');
	                        }
	                    }
                    }
                });
            }
        });
        // add the onClick event to the button
        btn.click(function (event) {
            event.preventDefault();

            var output = form.find('.gk_widget_rules_output');
            var value = select.children('option:selected').val();

            if (
                value === 'homepage' ||
                value === 'search' ||
                value === 'archive' ||
                value === 'page404'
            ) {
                output.val(output.val() + ',' + value);
            } else if (
                value === 'page:' ||
                value === 'post:' ||
                value === 'format:' ||
                value === 'template:' ||
                value === 'category:' ||
                value === 'category_descendant:' ||
                value === 'tag:' ||
                value === 'author:'
            ) {
                output.val(output.val() + ',' + value + form.find('.gk_widget_rules_form_input_' + value.replace(':', '')).val());
            } else if (value === 'taxonomy:') {
                var tax = form.find('.gk_widget_rules_form_input_taxonomy').val();
                var term = form.find('.gk_widget_rules_form_input_taxonomy_term').val();
                output.val(output.val() + ',taxonomy:' + tax + ((term !== '') ? ';' + term : ''));
            } else if (value === 'posttype:') {
                var type = form.find('.gk_widget_rules_form_input_posttype').val();
                //
                if (type !== '') {
                    output.val(output.val() + ',posttype:' + type);
                }
            }

            gk_widget_control_refresh(form);
        });
        // event to remove the page tags
        form.find('.gk_widget_rules_pages div').click(function (event) {
            if (event.target.nodeName.toLowerCase() === 'strong') {
                var output = form.find('.gk_widget_rules_output');
                var parent = jQuery(event.target).parent();
                parent.find('strong').remove();
                var text = parent.text();
                //
                if (text === 'All pages') {
                    text = 'page:';
                } else if (text === 'All posts pages') {
                    text = 'post:';
                } else if (text === 'All category pages') {
                    text = 'category:';
                } else if (text === 'All tag pages') {
                    text = 'tag:';
                } else if (text === 'All author pages') {
                    text = 'author:';
                } else if (text === 'All taxonomy pages') {
                    text = 'taxonomy:';
                } else if (text === 'All post format pages') {
                    text = 'format:';
                } else if (text === 'All page template pages') {
                    text = 'template:';
                }
                //
                if (text.indexOf(':') === text.length - 1) {
                    var startlen = output.val().length;
                    output.val(output.val().replace("," + text, ""));
                    // if previous regexp didn't changed the value
                    if (startlen === output.val().length) {
                        var regex = new RegExp(',' + text + '$', 'gmi');
                        output.val(output.val().replace(regex, ""));
                    }
                } else {
                    output.val(output.val().replace("," + text, ""));
                }
                //
                gk_widget_control_refresh(form);
            }
        });
    }
}

// function to refresh the list of pages

function gk_widget_control_refresh(form) {
    var output = form.find('.gk_widget_rules_output');
    if (output.length > 0) {
        var list = form.find('.gk_widget_rules_pages div');
        list.html('');
        var pages = output.val().split(',');
        var pages_exist = false;

        for (var i = 0; i < pages.length; i++) {
            if (pages[i] !== '') {
                pages_exist = true;
                var type = 'homepage';
                var types = ['page:', 'post:', 'category:', 'category_descendant:', 'tag:', 'archive', 'author:', 'template:', 'taxonomy:', 'posttype:', 'page404', 'search'];

                jQuery.each(types, function (j, el) {
                    if (pages[i].substr(0, el.length) === el) {
                        type = el.replace(':', '');
                    }
                });

                var out = pages[i];

                if (out === 'page:') {
                    out = 'All pages';
                } else if (out === 'post:') {
                    out = 'All posts pages';
                } else if (out === 'category:') {
                    out = 'All category pages';
                } else if (out === 'tag:') {
                    out = 'All tag pages';
                } else if (out === 'author:') {
                    out = 'All author pages';
                } else if (out === 'taxonomy:') {
                    out = 'All taxonomy pages';
                } else if (out === 'format:') {
                    out = 'All post format pages';
                } else if (out === 'template:') {
                    out = 'All page template pages';
                }

                list.html(list.html() + "<span class=" + type + ">" + out + "<strong>&times;</strong></span>");
            }
        }

        form.find('.gk_widget_rules_nopages').css('display', pages_exist ? 'none' : 'block');
    }
}

// EOF