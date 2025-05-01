define(
    [
        'jquery',
        'underscore',
        'mage/template'
    ],
    function ($, _, mageTemplate) {
        'use strict';
        const form_template =
            `<form action="<%= data.action %>" method="GET" hidden enctype="application/x-www-form-urlencoded">
                <% _.each(data.fields, function(val, key){ %>
                    <input value="<%= val %>" name="<%= key %>" type="hidden">
                <% }); %>
            </form>`;
        return function (response) {
            if (!response || !response.action || !response.fields) {
                console.error('Invalid redirect response', response);
                return;
            }

            const form = mageTemplate(form_template, {
                data: {
                    action: response.action,
                    fields: response.fields
                }
            });

            return $(form).appendTo($('body'));
        };
    }
);
